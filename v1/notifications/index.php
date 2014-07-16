<?php

/**
 * Push notifications endpoint
 * /v1/notifications
 *
 * @author 
 */

/**
 * Load configuration
 */
require_once('../../config.php');

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../models/Notifications.php';				// notification model
require_once '../../admin/includes/CommonFunctions.php';    // commonfunction

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
 * Put user Badge count update
 * PUT /v1/notifications/BadgeCount
 */
$app->put('/badgecount',function () use ($app) {
    try {
        // Create a http request
		$request = $app->request();
    	$body = stripslashes($request->getBody());
    	$input = json_decode($body); 
        /**
         * Update Bade Count
         * @var Notification 
         */
		 $device = new Notifications();
		/**
         * Updating badge count was made success
         */
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'badge';
		$token = '';
		if(isset($input->DeviceToken))	$token	=  $input->DeviceToken;
		$success = $device->updateBadgeForToken($token,2);
	    $response->addNotification('Badge Count has been updated successfully');
        echo $response;
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