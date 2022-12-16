<?php


namespace App\Lib;

use App\Exceptions\ShopifyProductCreatorException;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;


class InstagramAuthBusiness
{
    public $client_id     = '573228566766473';
    public $client_secret = 'df0b3924507de6952c4f31bb8d34536e';
    public $redirect_url  = 'https://devmontdigital.co/instagram_auth/';

    public function __construct(array $config = array())
    {
        $this->initialize($config);
    }

    public function initialize(array $config = array())
    {
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $this->$key = $val;
            }
        }
        return $this;
    }

    public static function getAuthURL()
    {
        // $authURL = "https://www.facebook.com/v15.0/dialog/oauth?client_id=573228566766473&redirect_uri=https://devmontdigital.co/instagram_auth&state=&response_type=code&scope=instagram_basic,instagram_content_publish,pages_show_list,pages_read_engagement";
        $authURL = "https://api.instagram.com/oauth/authorize/?client_id=875749180517574&redirect_uri=https://my-instagram-app.myshopify.com/admin/apps/auth-insta-app/instagramauth&response_type=code&scope=user_profile,user_media";
        return $authURL;
    }
    
    public static function getAccessToken($code) {  
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.instagram.com/oauth/access_token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "client_id=875749180517574&client_secret=24d0e496e2fdc7aa1a8fadeec57be398&grant_type=authorization_code&redirect_uri=https://my-instagram-app.myshopify.com/admin/apps/auth-insta-app/instagramauth&code=".$code."",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
            "postman-token: 55f055ab-e9ad-2d82-b2d1-d3fad1e360d3"
        ),
        ));
        $data = json_decode(curl_exec($curl));
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);  
        curl_close($curl);
        if($http_code != '200'){     
            print_r($data);
            throw new Exception('Error : Failed to receive access token...'.$code);
        } 
        return $data;     
    }

    public function getUserAccountInfo($access_token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v15.0/me/accounts?fields=instagram_business_account{id,name,username,profile_picture_url}&access_token=' . $access_token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response['data'][0]['instagram_business_account'];
    }

    public static function getUserProfileInfo($access_token, $user_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.instagram.com/' . $user_id . '?fields=account_type,media_count,id,username&access_token=' . $access_token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: ig_did=CE81D54B-1824-401B-BBE2-502243525D08; ig_nrcb=1'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public static function getUserAllPost($access_token, $user_id) {  
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://graph.instagram.com/v15.0/'.$user_id.'/media?fields=id,media_url,media_type,caption,children{media_url},permalink&access_token='.$access_token,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: ig_did=CE81D54B-1824-401B-BBE2-502243525D08; ig_nrcb=1'
        ),
        ));

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response['data']; 

    }
}
