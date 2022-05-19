<?php
/*
 * This function can be used to exchange an authorization code for an access token.
 * Make this call by passing in the code present when the account owner is redirected back to you.
 * The response will contain an 'access_token' and 'refresh_token'
 * https://v3.developer.constantcontact.com/api_guide/server_flow.html
 */

/***
 * @param $redirectURI - URL Encoded Redirect URI
 * @param $clientId - API Key
 * @param $clientSecret - API Secret
 * @param $code - Authorization Code
 * @return string - JSON String of results
 */

$uri_setting = $modx->getObject('modSystemSetting', 'cc_redirect_uri');
$redirectURI = $uri_setting->get('value'); 

$auth_setting = $modx->getObject('modSystemSetting', 'cc_auth_code');
$code = $auth_setting->get('value');   // 60 second life time

$id_setting = $modx->getObject('modSystemSetting', 'cc_client_id');
$clientId = $id_setting->get('value'); 

$sec_setting = $modx->getObject('modSystemSetting', 'cc_client_secret');
$clientSecret = $sec_setting->get('value'); 
 
function getAccessToken($redirectURI, $clientId, $clientSecret, $code) {
    // Use cURL to get access token and refresh token
    $ch = curl_init();

    // Define base URL
    $base = 'https://authz.constantcontact.com/oauth2/default/v1/token';

    // Create full request URL
    $url = $base . '?code=' . $code . '&redirect_uri=' . $redirectURI . '&grant_type=authorization_code&scope=contact_data';
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set authorization header
    // Make string of "API_KEY:SECRET"
    $auth = $clientId . ':' . $clientSecret;
    // Base64 encode it
    $credentials = base64_encode($auth);
    // Create and set the Authorization header to use the encoded credentials
    $authorization = 'Authorization: Basic ' . $credentials;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization, 'Content-Type: application/x-www-form-urlencoded'));

    // Set method and to expect response
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Make the call
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
}

// Step 1
return getAccessToken($redirectURI, $clientId, $clientSecret, $code);

//Access tokens automatically expire two hours (7,200 seconds) after their last use. Access tokens have a maximum lifetime of 24 hours (86,400 seconds).
