<?php

/**
 * Content endpoint
 * /v1/version
 *
 * @author eventurers
 */

/**
 * Load configuration
 */
require_once('../../config.php');

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../models/General.php';                 // General model

require_once "../../admin/includes/CommonFunctions.php";

/**
 * Library objects
 */
use RedBean_Facade as R;
use Helpers\RedBeanHelper as RedBeanHelper;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Exceptions\ApiException as ApiException;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
/**
 * Initialize application
 */
tuplitApi::init(true);
$app = new \Slim\Slim();


/**
 * Get check version
 * GET /v1/version/check
 */
$app->get('/check', function () use ($app) {

    try {
       
	    // Create a http request
        $req = $app->request();
		
		$general		= 	R::dispense('general');
		if($req->params('version'))				$general->version 		= 	$req->params('version');
		if($req->params('build'))				$general->build 		= 	$req->params('build');
		if($req->params('device_type'))			$general->device_type 	= 	$req->params('device_type');
		if($req->params('app_type'))			$general->app_type 		= 	$req->params('app_type');
		
		$status 	= 	$general->checkCurrentVersion();
		$response 	= 	new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Ok);
		if($status){
			$response->meta->status_code 	= 	$status['status_code'];
			$response->meta->status 		= 	'Success';
			$response->meta->message 		= 	$status['message'];
	        echo $response;
		}
		else{
			$response->meta->status_code 	= 	4;
			$response->meta->status 		= 	'Failure';
			$response->meta->message 		= 	'No app version found';
	        echo $response;
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});
/**
 * Start the Slim Application
 */

$app->run();