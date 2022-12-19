<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Lib\InstagramAuth;
use App\Lib\InstagramAuthBusiness;
use Illuminate\Support\Facades\DB;
use App\Models\Session;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session as AuthSession;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Exception\InvalidWebhookException;
use Shopify\Utils;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use App\Models\AuthUser;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class AuthUserController extends Controller
{
    public function ponka(Request $request){
        return "ponka";
    }
    public function index(Request $request){
        $payload = json_decode($request->getContent(), true);
        $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active
        $success = $code = $error = null;
        try {
            $getCode  = $payload['code'];
            $hostName = $payload['shop'];
            // $hostName = request()->getHost();
            $data = InstagramAuthBusiness::getAccessToken($getCode);
            $access_token = $data->access_token; 
            $user_id = $data->user_id; 
            $userData = InstagramAuthBusiness::getUserProfileInfo($access_token, $user_id);
            $rawdata  = InstagramAuthBusiness::getUserAllPost($access_token, $user_id);
            // $deleted = DB::table('auth_users')->where('store_name', $hostName)->delete();
            if (AuthUser::where('store_name', $hostName)->exists()) {
                $consumer = AuthUser::find($hostName);
                $consumer->access_token = $access_token; 
                $consumer->code         = $getCode; 
                $consumer->app_id       = $user_id; 
                $consumer->oauth_uid    = $userData['id']; 
                $consumer->username     = $userData['username']; 
                $consumer->link         = 'https://www.instagram.com/'.$userData['username']; 
                $consumer->account_type = $userData['account_type']; 
                $consumer->status       = 1; 
                $consumer->save();
            }
            else{
                $consumer = AuthUser::create([
                    'store_name'    => $hostName,
                    'access_token'  => $access_token,
                    'code'          => $getCode,
                    'app_id'        => $user_id,
                    'oauth_uid'     => $userData['id'],
                    'username'      => $userData['username'],
                    'link'          => 'https://www.instagram.com/'.$userData['username'],
                    'account_type'  => $userData['account_type'],
                    'status'  => 1,
                ]);
            }

            $success = true;
            $code = 200;
            $error = null;
            return response()->json(['message' => "Data Send", 'consumer' => $consumer, 'rawdata' => $rawdata]);
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
    }

    public function status(Request $request){
        $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active
        $success = $code = $error = null;
        $payload = json_decode($request->getContent(), true);
        try {
            $hostName  = $payload['shop'];
            $consumer  = false;
            if (AuthUser::where('store_name', $hostName)->exists()) {
                $consumer  = true;
            }
            $success = true;
            $code = 200;
            $error = null;
            // return $consumer;
            return response()->json(['status' => $consumer]);
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
    }

    public function instaRawData(Request $request){
        $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active
        $success = $code = $error = null;
        $payload = json_decode($request->getContent(), true);
        try {
            $rawdata ="";
            $hostName  = $payload['shop'];
            if($hostName){
                $consumer = AuthUser::where('store_name', $hostName)->get();
                $access_token = $consumer[0]->access_token;
                $app_id = $consumer[0]->app_id;
                $rawdata = InstagramAuthBusiness::getUserAllPost($access_token, $app_id);
                $code = 200;
                $error = null;
                return response()->json(['message' => $hostName, 'consumer' => $consumer, 'rawdata' => $rawdata]);
            }
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
    }

}