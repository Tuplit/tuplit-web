<?php

/**
 * Transactions endpoint
 * /v1/transactions
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
require_once '../../lib/ModelBaseInterface.php';        // base interface class for RedBean models
require_once '../../lib/Model_Transactions.php';             // order  model
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
 * get transactions list
 * GET /v1/transactions/
 */
$app->get('/', tuplitApi::checkToken(), function () use ($app) {
    try {
		  /**
         * Retrieving transactions list
         */
		 $request 			= $app->request();
		 $userId		 	= tuplitApi::$resourceServer->getOwnerId();
		 $transactions		= new Model_Transactions();
		 $transactionsList	= $transactions->getTransactionsList($userId); 
		 
		 if($transactionsList){
			$response 	   						= new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'TransactionsList';
			$response->returnedObject 			= $transactionsList;
			echo $response;
		}
		else{
			 /**
	         * throwing error when no data found.
	         */
			  throw new ApiException("No Transactions Found", ErrorCodeType::NoResultFound);
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
 * get transaction Details
 * GET /v1/transactions/
 */
$app->get('/:TransactionId', tuplitApi::checkToken(), function ($TransactionId) use ($app) {
    try {
		  /**
         * Retrieving transaction Details
         */
		 $request 				= $app->request();
		 $userId		 		= tuplitApi::$resourceServer->getOwnerId();
		 $transactions			= new Model_Transactions();
		 $transactionsDetails	= $transactions->getTransactionsDetails($userId,$TransactionId);  
		 
		 if($transactionsDetails){
			$response 	   						= new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'TransactionsDetails';
			$response->returnedObject 			= $transactionsDetails;
			echo $response;
		}
		else{
			 /**
	         * throwing error when no data found.
	         */
			  throw new ApiException("No Transactions Found", ErrorCodeType::NoResultFound);
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