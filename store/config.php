<?php 
@session_start();

@ini_set('session.gc_maxlifetime',12*60*60);
@ini_set('session.cookie_lifetime',12*60*60);
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Karachi');

define('PRE_FIX' , "ticticEcom");

$baseurl= "http://quickies.xoblack.com/mobileapp_api/";
define("status" , "live");
define("API_KEY" , "156c4675-9608-4591-1111-00000");
$imagebaseurl= $baseurl;
$baseurl = $baseurl."api/";
define("imagebaseurl" , $imagebaseurl);

define("noImage" , "assets/img/noimage.jpg");






if(isset($_GET['p']))
{
    $pageTitle = ucWords($_GET['p']);
}

function curl_request($data,$url)
{
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "api-key: ".API_KEY.""
    ];
    $data = $data;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $return = curl_exec($ch);
    $json_data = json_decode($return, true);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $json_data;
}

function curl_request_debug($data,$url)
{
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "api-key: ".API_KEY.""
    ];
    $data = $data;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $return = curl_exec($ch);
    $json_data = json_decode($return, true);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $return;
}

function checkImageUrl($url)
{
    $aws=strpos($url,'http');
    $cdn=strpos($url,'cdn');
    $s3=strpos($url,'s3');
    $cloudfront=strpos($url,'cloudfront');
    if($aws==true || $cdn==true || $cloudfront==true || $s3 == true)
    {
        return $url;
    }
    else
    if(checkImageExist($url))
    {
        return $url;
    }
    else
    {
        return imagebaseurl."/".$url;
    }
}

function checkImageExist($external_link)
{
    if(@getimagesize($external_link))
    {
        return $external_link;
    } 
    else 
    {
        return "assets/img/noimage.jpg";
    }
}


?>
