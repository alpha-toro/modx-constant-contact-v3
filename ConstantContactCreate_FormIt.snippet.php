<?php
/*
 * This function can be used to exchange a refresh token for a new access token and refresh token.
 * Make this call by passing in the refresh token returned with the access token.
 * The response will contain a new 'access_token' and 'refresh_token'
 */

/***
 * @param $refreshToken - The refresh token provided with the previous access token
 * @param $clientId - API Key
 * @param $clientSecret - API Secret
 * @return string - JSON String of results
 */
 
//FORMIT HOOK

$formFields = $hook->getValues();
$first_name = $formFields['first_name'];
$email = $formFields['email'];
$phone = $formFields['phone'];

$membership_lists = ['0ce51e9e-47ee-11ec-add5-fa163e6a92d8'];

$setting = $modx->getObject('modSystemSetting', 'cc_refresh_token');

$id_setting = $modx->getObject('modSystemSetting', 'cc_client_id');
$clientId = $id_setting->get('value'); 

$sec_setting = $modx->getObject('modSystemSetting', 'cc_client_secret');
$clientSecret = $sec_setting->get('value'); 

if ($setting && $email && $clientId && $clientSecret) {
    //echo $setting->get('value');
    
} else {
    //return true; //dont trigger form it to fail
    $hook->addError('error_message','Settings not set.'); 
    $modx->log(modX::LOG_LEVEL_ERROR,'CC Settings not set');
    return false;
}
 
$refreshToken = $setting->get('value');


// Use cURL to get a new access token and refresh token
$ch = curl_init();

// Define base URL
$base = 'https://authz.constantcontact.com/oauth2/default/v1/token';

// Create full request URL
$url = $base . '?refresh_token=' . $refreshToken . '&grant_type=refresh_token';
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
$error = curl_error($ch);

if($error){
    $hook->addError('error_message',serialize($error)); 
    $modx->log(modX::LOG_LEVEL_ERROR,'Refresh CC Curl error: '.serialize($error));
    return false;
}

$getToken = $result;

curl_close($ch);

//step 2

$obj = json_decode($getToken);

if($obj->refresh_token){
    
    //replace old token
    $setting->set('value', $obj->refresh_token);
    $setting->save();
    
    $ch = curl_init();
    
    // Define base URL
    $url = 'https://api.cc.email/v3/contacts';
    curl_setopt($ch, CURLOPT_URL, $url);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$obj->access_token,
        'Content-Type: application/json',
        'accept: application/json',
        'cache-control: no-cache'
    )); 

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $postRequest = '{
      "email_address": {
        "address": "'.$email.'",
        "permission_to_send": "implicit"
      },
      "phone_numbers": [{
        "phone_number": "'.$phone.'",
        "kind": "work"
      }],
      "first_name": "'.$first_name.'",
      "last_name": "",
      "job_title": "",
      "company_name": "",
      "create_source": "Contact",
      "list_memberships": '.json_encode($membership_lists).'
    }';

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);
    
    $result = curl_exec($ch);
    
    $result_json = json_decode($result);
    
    if($result_json->error_message){
        $modx->log(modX::LOG_LEVEL_ERROR,'CC Create Error'.$result_json->error_message);
        $hook->addError('error_message',$result_json->error_message); 
        return false;
    } else {
        //dont trigger form it to fail
        //$modx->log(modX::LOG_LEVEL_ERROR,'CC Finished'.$result);
        return true;
    }
    
    curl_close($ch);
} else {
    $modx->log(modX::LOG_LEVEL_ERROR,'CC Refresh Token Missing: '.serialize($obj));
    $hook->addError('error_message',serialize($obj)); 
    return false;
}
