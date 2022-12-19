<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Lib\InstagramAuthBusiness;
use App\Models\AuthUser;
use App\Models\Session;
use Shopify\Auth\Session as AuthSession;

use App\Lib\EnsureBilling;
use App\Lib\ProductCreator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Shopify\Auth\OAuth;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Exception\InvalidWebhookException;
use Shopify\Utils;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use App\Http\Controllers\AuthUserController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return "Hello API";
});

// Route::get('/instafeed1', function (Request $request, Closure $next) {
//     return $next($request)
//     ->header('Access-Control-Allow-Origin', '*')
//     ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');


//      /** @var AuthSession */
//     // return "Hello API";
//     // $hostName = 'e4df-2407-aa80-15-cf2d-7dbf-27c3-f84-490d.eu.ngrok.io';
//     // $consumer = AuthUser::where('store_name', $hostName)->get();
//     // $access_token = $consumer[0]->access_token;
//     // $app_id = $consumer[0]->app_id;
//     // $rawdata  = InstagramAuthBusiness::getUserAllPost($access_token, $app_id);
//     // return response()->json(['rawdata' => $rawdata]);
//     // $shop = Utils::sanitizeShopDomain($request->query('shop'));
//     // $session = $request->get('shopifySession');
//     // return $session->getShop();
// });


// Route::get('/instafeed', function (Request $request) {
//     return "sss";
// })->middleware('cors');
// Route::middleware(['cors','shopify.auth'])->group(function () {
//     Route::post('/instafeed123', function(){
//         return "dddd";
//     });
// });

// Route::get('/aaa', function () {
//     return response()->json(['Laravel CORS Demo']);
// })->middleware('cors','shopify.auth');

//[AuthUserController::class, 'instaRawData']

Route::get('/demo-url',  function  (Request $request)  {
    return response()->json(['Laravel CORS Demo']);
})->middleware('cors');