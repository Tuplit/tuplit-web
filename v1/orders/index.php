<?php

/**
 * orders endpoint
 * /v1/orders
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
require_once '../../lib/ModelBaseInterface.php';      // base interface class for RedBean models
require_once '../../models/Orders.php';              // order  model

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
 * Placing New Order
 * POST /v1/orders
 */
$app->post('/', tuplitApi::checkToken(),function () use ($app) {

    try {
			
		// Create a http request
		$req 					= 	$app->request();
		$userType 				= 	tuplitApi::$resourceServer->getOwnerType();
		$requestedById 			= 	tuplitApi::$resourceServer->getOwnerId();
		$userId		=	$MerchantId	=	$type	=	'';
		if($userType == 'user')	{	
			$userId				= 	$requestedById;
			$type				=	'1';
		}
		else if($userType == 'merchant') {
			$MerchantId			=	$requestedById;
			$type				=	'2';
		}
		
		/**
		 * Place a new order
		 */
		$order 					= 	R::dispense('orders');
		if(!empty($userId)) {		
			$order->UserId	 	= 	$userId;
			$order->MerchantId	= 	$req->params('MerchantId');
			$MerchantId			= 	$req->params('MerchantId');
		}
		else if(!empty($MerchantId)) {
			$order->MerchantId 	= 	$MerchantId;
			$order->UserId		= 	$req->params('UserId');
			$userId				= 	$req->params('UserId');
		}
		
		$order->OrderDoneBy		= 	$type;
		$order->TotalItems		= 	$req->params('TotalItems');
		$order->TotalPrice		= 	$req->params('TotalPrice');
		$order->Amount			= 	$req->params('TotalPrice');
		$order->CartDetails		= 	$req->params('CartDetails');
		
		//getting user details
		$userDetails 				=   R::findOne('users', 'id=?', [$userId]);
		$order->userDetails			= 	$userDetails;
		/**
         * Place the new order
         */
		$result						=	$order->create();	
		if($result['orderId']) { 
			$merchantDetails 							=   R::findOne('merchants', 'id=?', [$MerchantId]);	
			$adminDetails 								=   R::findOne('admins', 'id=?', ['1']);
			if($merchantDetails && $userDetails && $adminDetails) {

				$Address = '';
				if(!empty($merchantDetails['Street']))
					$Address	.=	$merchantDetails['Street'];
				if(!empty($merchantDetails['City']))
					$Address	.=	', '.$merchantDetails['City'];
				
				if(!empty($merchantDetails['State']) && !empty($merchantDetails['PostCode']))
					$Address	.=	', '.$merchantDetails['State'].' - '.$merchantDetails['PostCode'];
				else if(!empty($merchantDetails['State']) && empty($merchantDetails['PostCode']))
					$Address	.=	', '.$merchantDetails['State'];
				else if(empty($merchantDetails['State']) && !empty($merchantDetails['PostCode']))
					$Address	.=	', '.$merchantDetails['PostCode'];				
				if(!empty($merchantDetails['Country']))
					$Address	.=	', '.$merchantDetails['Country'];
				
				$merchantName							=	$merchantDetails->CompanyName;
				$merchantAddress						=	$Address;
				$merchantMailId							=	$merchantDetails->Email;
				$userName								=	ucfirst($userDetails->FirstName.' '.$userDetails->LastName);
				$useraddress							= 	$userDetails->Location.' '.$userDetails->Country;
				$userMailId								= 	trim($userDetails->Email);
				$adminMail								=	$adminDetails->EmailAddress;
				$adminMailId							=	$adminMail;
				$orderId								=	$result['CartId'];
				$TransactionId							=	$result['TransactionId'];
				//Product Details
				$CartDetails							=	$result['CartDetails'];
				$mailContentArray['CartDetails']		=   $CartDetails;
				$mailContentArray['orderId']			=	$orderId;
				$mailContentArray['TransactionId']		=	$TransactionId;
				$mailContentArray['TotalPrice']			=	number_format((float)$result['Total'], 2, '.', '');
				$mailContentArray['SubTotal']			=   number_format((float)$result['SubTotal'], 2, '.', '');
				$mailContentArray['VAT']				=   number_format((float)$result['VAT'], 2, '.', '');
				$mailContentArray['fileName']			=	'newordertouser.html';
				if($type == 2) {
					$mailContentArray['byname']			=	'Tuplit Team';
					$mailContentArray['subject']		= 	"New order from merchant";
					$mailContentArray['content']		= 	"Here is the order details.";
					$mailContentArray['from']			=   $adminMailId;			
					$mailContentArray['toemail']		=	$userMailId;
					$mailContentArray['name']			=	$userName;
					$mailContentArray['name1']			=	$merchantName;
					$mailContentArray['address']		=	$merchantAddress;
					
					sendMail($mailContentArray,8); //Send mail from merchant to user
					
					if($merchantDetails->OrderMail == 1) {
						$mailContentArray['content']		= 	"Here is the order details you have done to user ".$userName.".";
						$mailContentArray['toemail']		= 	$mailContentArray['from'];
						$mailContentArray['from']			=	$adminMailId;
						$mailContentArray['toemail']		=	$merchantMailId;
						$mailContentArray['name']			=	$merchantName;
						$mailContentArray['name1']			=	$userName;
						$mailContentArray['address']		=	$useraddress;
						$mailContentArray['byname']			=	'Tuplit Team';
						
						sendMail($mailContentArray,8); //Send mail merchant to merchant
					}					
				} else if($type == 1) {
					if($merchantDetails->OrderMail == 1) {
						$mailContentArray['subject']		= 	"New order place by user";
						$mailContentArray['content']		= 	"Here are the details of order done by ".$userName." for you.";
						$mailContentArray['from']			=	$adminMailId;
						$mailContentArray['toemail']		=	$merchantMailId;
						$mailContentArray['name']			=	$merchantName;
						$mailContentArray['name1']			=	$userName;
						$mailContentArray['address']		=	$useraddress;
						$mailContentArray['byname']			=	'Tuplit Team';
						
						sendMail($mailContentArray,8); //Send mail user to merchant
					}
					$mailContentArray['content']		= 	"Here are the details of your latest order.";
					$mailContentArray['toemail']		= 	$userMailId;
					$mailContentArray['from']			=	$adminMailId;
					$mailContentArray['toemail']		=	$userMailId;
					$mailContentArray['name']			=	$userName;
					$mailContentArray['name1']			=	$merchantName;
					$mailContentArray['address']		=	$merchantAddress;
					$mailContentArray['byname']			=	'Tuplit Team';
					
					sendMail($mailContentArray,8); //Send mail user to user
				}
			} else {
				//throws when MailContent
				throw new ApiException("Unable to send email. Some email contents missing", ErrorCodeType::NotAllowed);
			}
			
			$response = new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'Order';	
			$response->meta->OrderId 			= $result['orderId'];	
			$response->meta->TransactionId		= $result['TransactionId'];	
			$response->addNotification('Order has been placed successfully');
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
 * get new order list
 * GET /v1/orders/new/
 */
$app->get('/new', tuplitApi::checkToken(), function () use ($app) {

    try {
		/**
		* Retreiving new orders
		*/
		$request 		= 	$app->request();
		$requestedById 	= 	tuplitApi::$resourceServer->getOwnerId();
		$type			=	$Start	=	0;
		$End			=	12;
		if($request->params('Type'))	 	$type	= 	$request->params('Type');
		if($request->params('Start'))	 	$Start 	= 	$request->params('Start');
		if($request->params('End'))		 	$End 	= 	$request->params('End');
			
		 $orders 								= 	R::dispense('orders');
		 $orders->MerchantID					=	$requestedById;
		 $orders->Type							=	$type;
		 $orders->Start							=	$Start;
		 $orders->End							=	$End;
		 $newOrderDetails						= 	$orders->getNewOrderDetails();
		 if($newOrderDetails){
			$response 	   						= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 	'newOrderDetails';
			if($type == 0) {				
				$response->meta->TotalCount 	= 	$newOrderDetails['total'];
				$response->meta->TwoHourCount 	= 	$newOrderDetails['twoHour'];
				$response->meta->OtherCount 	= 	$newOrderDetails['otherHour'];
				echo $response;
			} else {				
				$response->meta->totalCount 	= 	$newOrderDetails['totalCount'];
				$response->meta->listedCount 	= 	$newOrderDetails['listedCount'];
				$response->returnedObject 		= 	$newOrderDetails['result'];
				echo $response;
			}
		}
		else{
			 /**
	         * throwing error when no results Found
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
 * get order detail
 * GET /v1/orders/{ORDER ID}
 */
$app->get('/:OrderId', tuplitApi::checkToken(), function ($OrderId) use ($app) {

    try {
		  /**
         * Retrieving order detail
         */
		 $request 							= 	$app->request();
		 $requestedById 					= 	tuplitApi::$resourceServer->getOwnerId();
		 $orders	 						=	R::dispense('orders');
		 if($request->params('Type'))
		 	$orders->Type    				= 	$request->params('Type');
		 $OrderDetails						= 	$orders->getOrderDetails($OrderId);    
		 if($OrderDetails){
			$response 	   					= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'OrderDetails';
			$response->returnedObject 		= 	$OrderDetails[0];
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
		$request 			= 	$app->request();
		$requestedById 	= 	tuplitApi::$resourceServer->getOwnerId();
		$Type				= 	0;
		$orders 			= 	R::dispense('orders');		
		if($request->params('UserId'))				$orders->UserId	 		= 	$request->params('UserId');
		if($request->params('Start'))			 	$orders->Start 			= 	$request->params('Start');
		if($request->params('Limit'))			 	$orders->Limit			= 	$request->params('Limit');
		if($request->params('Type'))			 	$Type			 		= 	$request->params('Type');//Type only for manage orders page otherwise default type=0
		if($request->params('UserName') !='')		$orders->UserName 		= 	$request->params('UserName');
		if($request->params('OrderStatus') !='')	$orders->OrderStatus	=	$request->params('OrderStatus');
		if($request->params('OrderDoneBy') !='')	$orders->OrderDoneBy	=	$request->params('OrderDoneBy');
		if($request->params('Price') !='')			$orders->Price			=	$request->params('Price');
		if($request->params('TransactionId') !='')	$orders->TransactionId 	= 	$request->params('TransactionId');
		if($request->params('FromDate') !='')		$orders->FromDate 		= 	$request->params('FromDate');
		if($request->params('ToDate') !='')			$orders->ToDate 		= 	$request->params('ToDate');
		 
		$OrderList		= 	$orders->getOrderList($requestedById,$Type);    
		if($OrderList){
			$response 	= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'OrderList';
			$response->meta->totalCount 		= 	$OrderList['totalCount'];
			$response->meta->listedCount 		= 	$OrderList['listedCount'];
			$response->returnedObject 			= 	$OrderList['result'];
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
 * get orders transactions list
 * GET /v1/orders/transactions
 */
$app->get('/transactions/', tuplitApi::checkToken(), function () use ($app) {

    try {
		  /**
         * Retreiving order list
         */
		 $request 		= 	$app->request();
		 $merchantID	= 	tuplitApi::$resourceServer->getOwnerId();
		 
		 $orders 		= 	R::dispense('orders');		 
		if($request->params('Start'))				$orders->Start 			= 	$request->params('Start');		
		if($request->params('FromDate') !='')		$orders->FromDate 		= 	$request->params('FromDate');
		if($request->params('ToDate') !='')			$orders->ToDate 		= 	$request->params('ToDate');
		if($request->params('DataType') !='')		$orders->DataType		=  	$request->params('DataType');
		if($request->params('OrderStatus') !='')	$orders->OrderStatus	=  	$request->params('OrderStatus');
		if($request->params('LimitType') !='')		$orders->LimitType		=  	$request->params('LimitType');
		
		//get data
		$OrderList		= 	$orders->getTransactionSummary($merchantID);  
		 
		 if($OrderList){
			$response 	   	= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'OrderList';
			$response->meta->totalCount 		= 	$OrderList['totalCount'];
			$response->meta->listedCount 		= 	$OrderList['listedCount'];
			if(isset($OrderList['summary']) && !empty($OrderList['summary'])) {
				$response->meta->transactions 	= 	$OrderList['summary']['transactions'];
				$response->meta->sales 			= 	$OrderList['summary']['sales'];
				$response->meta->refunds 		= 	$OrderList['summary']['refunds'];
				$response->meta->refunded 		= 	$OrderList['summary']['refunded'];
			}
			$response->returnedObject 			= 	$OrderList['list'];
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
 * Update order details as Approved/Rejected
 * PUT /v1/orders
 */
$app->put('/', tuplitApi::checkToken(), function () use ($app) {

    try {
		$userType 					= tuplitApi::$resourceServer->getOwnerType();
		$requestedById 				= tuplitApi::$resourceServer->getOwnerId();
		
		$userId	=	$MerchantId	=	'';
		if($userType == 'user')		
			$userId					= 	$requestedById;	
		else if($userType == 'merchant') 
			$MerchantId				=	$requestedById;
		
        // Create a http request
        $request 					= $app->request();
    	$body 						= $request->getBody();		
    	$input 						= json_decode($body);
		
        /**       
         * @var Products
         */		
        $orders 					= 	R::dispense('orders');		
		$orders->userId				=	$userId;
		$orders->merchantId			=	$MerchantId;
		$orders->userType			=	$userType;
		
		if(isset($input->OrderId)) 			
			$orders->OrderId 		= $input->OrderId;
		if(isset($input->OrderStatus)) {			
			$orders->OrderStatus 	= $input->OrderStatus;
			if($input->OrderStatus == 1)
				$msg 				= "Order has been Approved successfully";
			else if($input->OrderStatus == 2)
				$msg 				= "Order has been Rejected successfully";
		}
		
		/**
         * update the orders details
         */
		$details	=	$orders->modify();
		
		 /**
		 * product update was made success
		 */
		$response = new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName = 'OrderStatus';		
		$response->addNotification($msg);
		if($details)
			$response->meta->paymentDetails	= 	$details;
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
 * POST Do payment
 * POST /v1/orders/payment
 */
$app->post('/payment',tuplitApi::checkToken(), function () use ($app) {

    try {
		 // Create a http request
        $req 						= 	$app->request();
		$merchantId					= 	tuplitApi::$resourceServer->getOwnerId();
        $orders 					= 	R::dispense('orders');
		$orders->MerchantId			= 	$req->params('MerchantId');
		$orders->UserId 			= 	$req->params('UserId');
		$orders->Amount 			= 	$req->params('Amount');
		$orders->Currency 			= 	$req->params('Currency');
	    $paymentId	 				= 	$orders->doPayment();		
		if( isset($paymentId->Id)  && $paymentId->Id != ''){
			$response 				= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName = 'Payment';		
			if ($paymentId->Status == 'SUCCEEDED')
				$response->addNotification('Payment has been done successfully');
			else
				$response->addNotification($paymentId->ResultMessage);
			echo $response;	
		}
		 else{
			throw new ApiException("Sorry! payment is not successful" ,  ErrorCodeType::PaymentError);
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
 * Refunding the order amount 
 * GET /v1/orders/refund/{ORDER ID}
 */
$app->get('/refund/:OrderId', tuplitApi::checkToken(), function ($OrderId) use ($app) {

    try {
		// Create a http request
        $request					= 	$app->request();
		
		//validate refund user type
		$userType 					= 	tuplitApi::$resourceServer->getOwnerType();
		if($userType == 'user')	{	
			 /**
	         * throwing error when user tries to refund
	         */
			  throw new ApiException("You are not allowed to refund", ErrorCodeType::InvalidRefundUser);
		}
		
		//get merchant id 
		$requestedById 				= 	tuplitApi::$resourceServer->getOwnerId();
		
		//order table dispense
		$orders	 					=	R::dispense('orders');
		$orders->OrderId			=	$OrderId;
		$orders->MerchantId			=	$requestedById;
		
		$type	=	'';     //Type = 1 is for refunding order from manage
		if($request->params('Type') !='') {
			$orders->Type 	= 	$request->params('Type');
			$type			=	$request->params('Type');
		}
		if($request->params('msg') !='')
			$orders->msg 	= 	base64_decode($request->params('msg'));
		if($request->params('ProductId') !='')
			$orders->ProductId 	= 	$request->params('ProductId');
		
		$refundDetails							= 	$orders->getRefundDetails($type);    
		if($refundDetails){
			if(isset($refundDetails->ResultCode) && $refundDetails->ResultCode == '001401') {
				 /**
				 * throwing error when error in refund
				 */
				  throw new ApiException($refundDetails->ResultMessage, ErrorCodeType::AlreadyRefunded);
			} else {
			$response 	   						= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'OrderRefund';
			$response->returnedObject 			= 	$refundDetails;
			$response->addNotification($refundDetails->ResultMessage);
			echo $response;
			}
		}
		else{
			 /**
	         * throwing error when error in refund
	         */
			  throw new ApiException("No refund data's found", ErrorCodeType::NoResultFound);
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