<?php
/**
* cards endpoint
* /v1/cards
*
* @author 
*/

/**
* Load configuration
*/
require_once('../../config.php');
require_once '../../admin/includes/CommonFunctions.php';
require_once '../../admin/includes/phmagick.php';

/**
* Load models
*/
require_once '../../lib/ModelBaseInterface.php';         // base interface class for RedBean models
require_once '../../models/Cards.php';                 	// Cards model
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
 * Create Card
 * POST /v1/cards/
 */
$app->post('/',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req	 					= 	$app->request();
		$userId 					= 	tuplitApi::$resourceServer->getOwnerId();

        $cards 						=  	R::dispense('cards');		
		$cards->UserId				= 	$userId;	
		$cards->Currency 			= 	$req->params('Currency');
		$cards->Amount	 			= 	$req->params('Amount');
		$cards->CardNumber	 		= 	$req->params('CardNumber');
		$cards->CardExpirationDate	= 	$req->params('CardExpirationDate');
		$cards->CVV		 			= 	$req->params('CVV');
		if($req->params('WalletId') != '')		$cards->WalletId		= 	$req->params('WalletId');
		if($req->params('MangoPayId') != '')	$cards->MangoPayId		= 	$req->params('MangoPayId');
		$cardCreation				=	$cards->create();
		$response 					= 	new tuplitApiResponse();
		if(isset($cardCreation->Status) && $cardCreation->Status == 'ERROR'){
			global $mangoPayError;
			$errorCodeArray			=	 explode('=',$cardCreation->RegistrationData);
			$errorCode				=	$errorCodeArray[1];
			// Error occurred while registering card		
			throw new ApiException($mangoPayError[$errorCode] ,  ErrorCodeType::ErrorInCardRegistration);
		}
		else if(isset($cardCreation->CardId) && $cardCreation->CardId != ''){
			
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'Card Registration';
			$response->addNotification('Card has been registered successfully');
			echo $response;
		}		 
		else{
			// Error occurred while registering card		
			throw new ApiException("Error in registering card" ,  ErrorCodeType::ErrorInCardRegistration);
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
 * Get Cards
 * GET /v1/cards/
 */
$app->get('/',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req	 					= 	$app->request();
		$userId 					= 	tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response 					= 	new tuplitApiResponse();
		
        $cards 						=  	R::dispense('cards');		
		$cards->UserId				= 	$userId;	
		$getCardDetails				=	$cards->getCards();		
		if($getCardDetails && count($getCardDetails['result']) > 0){
		
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 		= 'UserCardList';		
			$response->returnedObject 				= $getCardDetails['result'];	
			echo $response;
		}
		else{
			/**
			* throwing error when no transaction found
			*/
			throw new ApiException("No cards found", ErrorCodeType::NoResultFound);
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
 * Topup a wallet
 * POST /v1/cards/
 */
$app->post('/topup',tuplitApi::checkToken(),function () use ($app) {
	
    try {
		// Create a http request
        $req	 					= 	$app->request();
		$userId 					= 	tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response 					= 	new tuplitApiResponse();
		
        $cards 						=  	R::dispense('cards');		
		$cards->UserId				= 	$userId;	
		$cards->Currency 			= 	$req->params('Currency');
		$cards->Amount	 			= 	$req->params('Amount');
		$cards->CardId	 			= 	$req->params('CardId');
		
		$TopupWallet				=	$cards->topup();
		if(isset($TopupWallet->Id) && $TopupWallet->Id != ''){
				$response->setStatus(HttpStatusCode::Created);
		        $response->meta->dataPropertyName 	= 'Topup';		
				$response->addNotification('Topup has been done successfully');
	       		echo $response;
		}
		else{
			// Error occured while reseting password
			throw new ApiException("Error in topup" ,  ErrorCodeType::ErrorInCardRegistration);
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
* Delete Cards
* DELETE /v1/cards
*/
$app->delete('/:CardId',tuplitApi::checkToken(), function ($CardId) use ($app) {

    try {

        // Create a http request
        $req 		= $app->request();
		$userId 	= tuplitApi::$resourceServer->getOwnerId();
		
	
		$cards 		 		= R::dispense('cards');
		$cards->UserId	 	= $userId ;
		$cards->CardId	 	= $CardId ;
		$Cards       		= $cards->cardDelete();	
		if(isset($Cards->Id) && !empty($Cards->Id)){
			$response = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'Cards';
			if(isset($Cards->msg) && !empty($Cards->msg))
				$response->addNotification($Cards->msg);
	        else 
				$response->addNotification('Card has been deleted successfully');
	        echo $response;
		}
		else{
			// Error occured while deleting card
			throw new ApiException("Invalid card id" ,  ErrorCodeType::ErrorInCardDelete);
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