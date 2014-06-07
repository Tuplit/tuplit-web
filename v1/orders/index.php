<?php

/**
 * Categories endpoint
 * /v1/categories
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
require_once '../../lib/Model_Orders.php';              // order  model

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
 * get new order list
 * GET /v1/orders/{MERCHANT ID}/newordersCount
 */
$app->get('/:merchantId/newordersCount', tuplitApi::checkToken(), function ($merchantId) use ($app) {

    try {
		  /**
         * Retreiving new order count
         */
		 $request 			= $app->request();
		 $requestedById 	= tuplitApi::$resourceServer->getOwnerId();
		 $orders 			= R::dispense('orders');		
		 $newOrderDetails	= $orders->getNewOrderDetails($merchantId);    
		 if($newOrderDetails){
			$response 	   = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'newOrderDetails';
			$response->returnedObject = $newOrderDetails;
			echo $response;
		}
		else{
			 /**
	         * throwing error when static data
	         */
			  throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
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
 * get new order list
 * GET /v1/orders/
 */
$app->get('/', tuplitApi::checkToken(), function () use ($app) {

    try {
		  /**
         * Retreiving order list
         */
		 $request 			= $app->request();
		 $requestedById 	= tuplitApi::$resourceServer->getOwnerId();
		 
		if($request->params('Type'))
			$type			= $request->params('Type');
		else
			$type			= '0';
		 
		 $orders 			= R::dispense('orders');		
		 $OrderList		= $orders->getOrderList($requestedById,$type);    
		 if($OrderList){
			$response 	   = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'OrderList';
			$response->returnedObject = $OrderList;
			echo $response;
		}
		else{
			 /**
	         * throwing error when static data
	         */
			  throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
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
 * Update order details
 * PUT /v1/orders
 */
$app->put('/', function () use ($app) {

    try {

        // Create a http request
        $request = $app->request();
    	$body = $request->getBody();
		
    	$input = json_decode($body);
		//echo "<pre>"; echo print_r($input); echo "</pre>";
        /**       
         * @var Model_Products $orders
         */
		
        $orders 	= R::dispense('orders');		
		
		if(isset($input->OrderId)) 			
			$orders->OrderId 		= $input->OrderId;
		if(isset($input->OrderStatus)) 			
			$orders->OrderStatus 	= $input->OrderStatus;
		
				
		/**
         * update the orders details
         */
	    $orders->modify();
				
		 /**
		 * product update was made success
		 */
		$response = new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName = 'orders';
		/**
		* returning upon response of Product update
		*/
		$response->addNotification('Order has been updated successfully');
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