<?php

use App\Exceptions\ShopifyProductCreatorException;
use App\Lib\AuthRedirection;
use App\Lib\EnsureBilling;
use App\Lib\ProductCreator;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session as AuthSession;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Exception\InvalidWebhookException;
use Shopify\Utils;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;

use App\Lib\InstagramAuth;
use App\Lib\InstagramAuthBusiness;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\AuthUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::fallback(function (Request $request) {
    if (Context::$IS_EMBEDDED_APP &&  $request->query("embedded", false) === "1") {
        if (env('APP_ENV') === 'production') {
            return file_get_contents(public_path('index.html'));
        } else {
            return file_get_contents(base_path('frontend/index.html'));
        }
    } else {
        return redirect(Utils::getEmbeddedAppUrl($request->query("host", null)) . "/" . $request->path());
    }
})->middleware('shopify.installed');

Route::get('/api/auth', function (Request $request) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    // Delete any previously created OAuth sessions that were not completed (don't have an access token)
    Session::where('shop', $shop)->where('access_token', null)->delete();

    return AuthRedirection::redirect($request);
});

Route::get('/api/auth/callback', function (Request $request) {
    $session = OAuth::callback(
        $request->cookie(),
        $request->query(),
        ['App\Lib\CookieHandler', 'saveShopifyCookie'],
    );

    $host = $request->query('host');
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    $response = Registry::register('/api/webhooks', Topics::APP_UNINSTALLED, $shop, $session->getAccessToken());
    if ($response->isSuccess()) {
        Log::debug("Registered APP_UNINSTALLED webhook for shop $shop");
    } else {
        Log::error(
            "Failed to register APP_UNINSTALLED webhook for shop $shop with response body: " .
                print_r($response->getBody(), true)
        );
    }

    $redirectUrl = Utils::getEmbeddedAppUrl($host);
    if (Config::get('shopify.billing.required')) {
        list($hasPayment, $confirmationUrl) = EnsureBilling::check($session, Config::get('shopify.billing'));

        if (!$hasPayment) {
            $redirectUrl = $confirmationUrl;
        }
    }

    return redirect($redirectUrl);
});

Route::get('/api/products/count', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $client = new Rest($session->getShop(), $session->getAccessToken());
    $result = $client->get('products/count');

    return response($result->getDecodedBody());
})->middleware('shopify.auth');

Route::get('/api/products/create', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $success = $code = $error = null;
    try {
        ProductCreator::call($session, 5);
        $success = true;
        $code = 200;
        $error = null;
    } catch (\Exception $e) {
        $success = false;

        if ($e instanceof ShopifyProductCreatorException) {
            $code = $e->response->getStatusCode();
            $error = $e->response->getDecodedBody();
            if (array_key_exists("errors", $error)) {
                $error = $error["errors"];
            }
        } else {
            $code = 500;
            $error = $e->getMessage();
        }

        Log::error("Failed to create products: $error");
    } finally {
        return response()->json(["success" => $success, "error" => $error], $code);
    }
})->middleware('shopify.auth');

Route::post('/api/webhooks', function (Request $request) {
    try {
        $topic = $request->header(HttpHeaders::X_SHOPIFY_TOPIC, '');

        $response = Registry::process($request->header(), $request->getContent());
        if (!$response->isSuccess()) {
            Log::error("Failed to process '$topic' webhook: {$response->getErrorMessage()}");
            return response()->json(['message' => "Failed to process '$topic' webhook"], 500);
        }
    } catch (InvalidWebhookException $e) {
        Log::error("Got invalid webhook request for topic '$topic': {$e->getMessage()}");
        return response()->json(['message' => "Got invalid webhook request for topic '$topic'"], 401);
    } catch (\Exception $e) {
        Log::error("Got an exception when handling '$topic' webhook: {$e->getMessage()}");
        return response()->json(['message' => "Got an exception when handling '$topic' webhook"], 500);
    }
});




Route::get('/api/loginInsta', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $success = $code = $error = null;
    try {
        $url = InstagramAuth::getAuthURL();
        $success = true;
        $code = 200;
        $error = null;
        return response()->json(['message' => "Got an exception when handling webhook", 'url'=>$url]);
    } catch (\Exception $e) {
        $success = false;
        if ($e instanceof ShopifyProductCreatorException) {
            $code = $e->response->getStatusCode();
            $error = $e->response->getDecodedBody();
            if (array_key_exists("errors", $error)) {
                $error = $error["errors"];
            }
        } else {
            $code = 500;
            $error = $e->getMessage();
        }

        Log::error("Failed to create products: $error");

        throw $e;
    }
})->middleware('shopify.auth');

/** Insta Business Auth **/
Route::get('/api/loginInstaBusiness', function (Request $request) {

    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $success = $code = $error = null;

    try {
        $url = InstagramAuthBusiness::getAuthURL();
        $success = true;
        $code = 200;
        $error = null;
        return response()->json(['message' => "Got an exception when handling webhook", 'url'=>$url]);
        return response()->json(["success" => $success, "error" => $error], $code);
    } catch (\Exception $e) {
        $success = false;
        if ($e instanceof ShopifyProductCreatorException) {
            $code = $e->response->getStatusCode();
            $error = $e->response->getDecodedBody();
            if (array_key_exists("errors", $error)) {
                $error = $error["errors"];
            }
        } else {
            $code = 500;
            $error = $e->getMessage();
        }

        Log::error("Failed to create products: $error");

        throw $e;
    }
})->middleware('shopify.auth');


Route::post('/api/instaToken', [AuthUserController::class, 'index'])->middleware('shopify.auth');
Route::post('/api/status', [AuthUserController::class, 'status'])->middleware('shopify.auth');
Route::post('/api/instaRawData', [AuthUserController::class, 'instaRawData'])->middleware('shopify.auth');




// Route::post('/api/instaToken', function (Request $request) {

//     /** @var AuthSession */
//     $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

//     $success = $code = $error = null;

//     try {
//         // $url = InstagramAuthBusiness::getAuthURL();
//         // dd($request);
//         $getCode = $request->input('code');
//         // $data = InstagramAuthBusiness::getAccessToken('AQCEUesk--DYBJ1UFdG2xjAdxald5OrLMYR_kEwv7QQ-M3d8y4BmI3mja69syoDH9JH9bsxRyM0fo4Ld_j0XVdrVLmrH_puIb0g8xNoGDmLDDW-Tc3Kg_HdPaM1U2FWN8MkD1WnQgeVJPFztVNHeDggTcvhZlBPL886R1kIpEqy1JNwrx6dDjm5yY572B0wVDKX9XGvaxVugZqd-l9c1GdbsMOFvQgsZsEAfzYkF5UI0nQ#_');
//         // $access_token = $data->access_token; 
//         $access_token = 'IGQVJYQUtuM2VUVEdmQURHb0ZAMTkx2OUhqWjcwVW1PQjljUFNtN3dOMmNPWkdyMDNqc182ZAUU5NzVqb2k4UUVEUXNocWpNYmtjVEJHdWlPQU5nMHRRb0ZAXX3kta21IeTN3c0FlU1ZAwLUdCaGVfVXo4cl9KaWc0SDUyemc4'; 
//         $userData = InstagramAuthBusiness::getUserProfileInfo($access_token,'17841401127337460');
//         $success = true;
//         $code = 200;
//         $error = null;
//         return response()->json(['message' => "Data Send", 'user_data' => $userData]);
//     } catch (\Exception $e) {
//         $success = false;
//         if ($e instanceof ShopifyProductCreatorException) {
//             $code = $e->response->getStatusCode();
//             $error = $e->response->getDecodedBody();
//             if (array_key_exists("errors", $error)) {
//                 $error = $error["errors"];
//             }
//         } else {
//             $code = 500;
//             $error = $e->getMessage();
//         }

//         Log::error("Failed to create products: $error");

//         throw $e;
//     }
// })->middleware('shopify.auth');