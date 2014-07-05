<?php

/**
* comment details endpoint
* /v1/Comments
*
* @author 
*/

/**
* Load configuration
*/
require_once('../../config.php');
require_once "../../admin/includes/CommonFunctions.php";	// load all common functions

/**
* Load models
*/
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../models/Friends.php';              // comments model

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
* invite friends
*GET/v1/invites/invites
*/
$app->post('/',tuplitApi::checkToken(),function () use ($app) {	
    try {
		// Create a http request		
        $req 				= $app->request();	
		$requestedById 		= tuplitApi::$resourceServer->getOwnerId();		
        $response 			= new tuplitApiResponse();
			
		/**
		* Get a invites table instance
		*/
        $invites 			= R::dispense('friends');
		$invites->UserId	= $requestedById;		
		if($req->params('FbId'))
			$invites->FbId			= $req->params('FbId');
		if($req->params('CellNumber'))
			$invites->CellNumber	= $req->params('CellNumber');
		 $inviteId 					= $invites->inviteFriend();
		if($inviteId){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'invites';
			$response->addNotification('Invitation has been tracked successfully');
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