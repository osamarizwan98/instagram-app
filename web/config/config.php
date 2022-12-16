<?php 
use App\Lib\InstagramAuth;
/* 
 * Basic Site Settings and API Configuration 
 */ 
 
// Instagram API configuration 
define('INSTAGRAM_CLIENT_ID', '573228566766473');
define('INSTAGRAM_CLIENT_SECRET', 'df0b3924507de6952c4f31bb8d34536e'); 
define('INSTAGRAM_REDIRECT_URI', 'https://devmontdigital.co/instagram_auth/'); 
 
// Start session 
// if(!session_id()){ 
//     session_start(); 
// } 
 
/* 
 * For the internal purposes only  
 * changes not required 
 */ 
 
$instagram = new InstagramAuth(array( 
    'client_id' => INSTAGRAM_CLIENT_ID, 
    'client_secret' => INSTAGRAM_CLIENT_SECRET, 
    'redirect_url' => INSTAGRAM_REDIRECT_URI 
)); 