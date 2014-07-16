<?php

/**
 * @author 
 */

/**
 * Load configuration
 */
require_once('../../config.php');

/**
 * Load libraries
 */
require('../../vendor/autoload.php');                   // composer library
require_once '../../lib/tuplitApi.php';               	// application
require_once '../../lib/Helpers/PasswordHelper.php';    // password helper
require_once '../../lib/tuplitApiResponse.php';       	// response
require_once '../../lib/tuplitApiResponseMeta.php';   	// response meta
require_once '../../lib/ModelBaseInterface.php';        // base interface class for RedBean models
require_once '../../models/Users.php';            		// Model: Users
require_once '../../models/Merchants.php';            	// Model: Merchants
require_once "../../admin/includes/CommonFunctions.php";

use Helpers\ResponseHelper as ResponseHelper;
/**
 * Initialize application
 *
 * We set $startAuthServer to true to start the authorization server
 */
tuplitApi::init(true);
$app = new \Slim\Slim();

/**
 * Check the Facebook and linkedIn Id callback function
 */
$checkLoginCallBack = function($email,$password,$facebookId,$googlePlusId,$deviceToken,$token,$userdata,$platform) {
    return Users::checkLogin($email,$password,$facebookId,$googlePlusId,$deviceToken,$token,$userdata,$platform);
};


/**
 * check merchant account
 */
$checkMerchantLoginCallBack =  function($email,$password) {
    return Merchants::checkLogin($email,$password);
};

/**
 *
 * This is how you obtain an authentication token
 * We are doing a post to the following endpoint: /oauth2/password/token
 * When doing so we pass the following parameters:
 
 * - ClientId
 * - ClientSecret
 * - UserName
 * - Password
 *
 * path: /oauth2/password/token
 */
$app->post('/token', function () use ($app, $checkLoginCallBack) {
	try {
		
        $req = $app->request();
		$res = $app->response();
		$res['Content-Type'] = 'application/json';

        // grab the authorization server from the api
        $authServer = tuplitApi::$authServer;
		
        // We are going for this flow in oauth 2.0: Resource Owner Password Credentials Grant
        $grant = new League\OAuth2\Server\Grant\Password($authServer);

        // this is where we check the Facebook and linkedIn Id
		
		$user_id = $grant->setVerifyCredentialsCallback($checkLoginCallBack);
        $authServer->addGrantType($grant);
        // get the response from the server
        $response = $authServer->getGrantType('password')->completeFlow(1);
		/** TO ASSIGN THE RESPONSES TO A FUNCTION **/
		ResponseHelper::setResponse(json_encode($response));
		/** END TO ASSIGN THE RESPONSES TO A FUNCTION **/
		echo json_encode($response);
		//showError

    }
    catch (League\OAuth2\Server\Exception\ClientException $e) {

        // Get the http status code based on the oauth error
        $status = tuplitApi::$errorCodeLookup[$e->getCode()];
		tuplitApi::showError($e, $status);
    }
    catch (Exception $e) {

        // Something went wrong
        tuplitApi::showError($e);

    }
});


$app->post('/token/merchants/', function () use ($app, $checkMerchantLoginCallBack) {
	try {		
        $req = $app->request();
		$res = $app->response();
		$res['Content-Type'] = 'application/json';
        // grab the authorization server from the api
        $authServer = tuplitApi::$authServer;
		
        // We are going for this flow in oauth 2.0: Resource Owner Password Credentials Grant
        $grant = new League\OAuth2\Server\Grant\Password($authServer);

        // this is where we check the Facebook and linkedIn Id
		
		$user_id = $grant->setVerifyCredentialsCallback($checkMerchantLoginCallBack);
        $authServer->addGrantType($grant);
        // get the response from the server
        $response = $authServer->getGrantType('password')->completeFlow(2);
		/** TO ASSIGN THE RESPONSES TO A FUNCTION **/
		ResponseHelper::setResponse(json_encode($response));
		/** END TO ASSIGN THE RESPONSES TO A FUNCTION **/
		echo json_encode($response);
		//showError

    }
    catch (League\OAuth2\Server\Exception\ClientException $e) {

        // Get the http status code based on the oauth error
        $status = tuplitApi::$errorCodeLookup[$e->getCode()];
		tuplitApi::showError($e, $status);
    }
    catch (Exception $e) {

        // Something went wrong
        tuplitApi::showError($e);

    }
});

/**
 * Start the Slim Application
 */
$app->run();