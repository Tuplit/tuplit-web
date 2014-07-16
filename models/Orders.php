<?php

/**
 * Description of Orders
 *
 * @author 
 */
use RedBean_Facade as R;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
use Exceptions\ApiException as ApiException;
use Valitron\Validator as Validator;

//Require needed models
require_once	'Notifications.php';
require_once	'Users.php';

require_once '../../admin/includes/mangopay/functions.php';
require_once '../../admin/includes/mangopay/config.php';
require_once '../../admin/includes/mangopay/MangoPaySDK/mangoPayApi.inc';
class Orders extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Constructor
	*/
    public function __construct() {

    }
	
	/**
	* Place an order
	*/
    public function create(){
		/**
		* Get the bean
		*/
        $bean 						= 	$this->bean;
		$type						=	$bean->OrderDoneBy;
		$userDetails				=	$bean->userDetails;
		$transId					=	'';
		unset($bean->userDetails);
		
		// validate the model parameters
        $this->validate();
		
		//validate and get userdetails		
		$userInfo					=	$this->validatePaymentUsers();
		if($userInfo) {
			unset($bean->Amount);
			$user					=	$userInfo['fromuser'];
			$merchant				=	$userInfo['touser'];
			$admin					=	$userInfo['admin'];
			
			//Getting cart details		
			$input					= 	$bean->CartDetails;		
			$temp					= 	json_decode($input,1);
			if(!empty($temp)) {
				if(!is_array($temp))
					$CartDetails[0] = 	$temp;
				else
					$CartDetails	= 	$temp;
			}
			else {
				//throws when json not valid 
				throw new ApiException("Cart details JSON not valid.", ErrorCodeType::NotAllowed);
			}
			
			//Forming cart-id
			$CartId					= 	'TUPLIT-UM-'. time();
			$productIds				=	'';
			$productcount			=	0;
			foreach($CartDetails as $val) {
				$productcount++;
				if(empty($productIds))
					$productIds		.=	$val['ProductId'];
				else
					$productIds		.=	','.$val['ProductId'];
			}
			
			$sql1 		= 	"SELECT * from products where id in (".$productIds.") and Status = 1";
			$result1 	= 	R::getAll($sql1);
			if(count($result1) == $productcount) {
				if($bean->OrderDoneBy == 1) {
					$walletDetails								=	getWalletDetails($user->WalletId);
					if($walletDetails) {
						if(isset($walletDetails->Balance->Amount) && ($walletDetails->Balance->Amount >= $bean->TotalPrice)) {
							$userDetails1['AuthorId']			=	$user->MangoPayUniqueId;
							$userDetails1['CreditedUserId']		=	$merchant->MangoPayUniqueId;
							$userDetails1['Currency']			=	'USD';
							$userDetails1['Amount']				=	$bean->TotalPrice;
							$userDetails1['DebitedWalletId']	=	$user->WalletId;
							$userDetails1['CreditedWalletId']	=	$merchant->WalletId;
							$userDetails1['FeesAmount']			=	$admin->MangoPayFees;
							$result								=	payment($userDetails1);
							if($result) {
								$transId						= 	$result->Id;
								//echo "<pre>"; echo print_r(getWalletDetails($user->WalletId)); echo "</pre>";
								//echo "<pre>"; echo print_r(getWalletDetails($merchant->WalletId)); echo "</pre>";
							}
						} else {
							// low balance
							throw new ApiException("Insufficient amount in account.", ErrorCodeType::CheckBalanceError);
						}
					}
				}
				//Storing carts
				foreach($CartDetails as $val) {	
					$carts						= 	R::dispense('carts');
					$carts->CartId				= 	$CartId;
					$carts->fkUsersId			= 	$bean->UserId;
					$carts->fkMerchantsId		= 	$bean->MerchantId;
					$carts->fkProductsId		= 	$val['ProductId'];
					$carts->ProductsQuantity	= 	$val['ProductsQuantity'];
					$carts->ProductsCost		= 	$val['ProductsCost'];
					$carts->DiscountPrice		= 	$val['DiscountPrice'];
					$carts->TotalPrice			= 	($val['ProductsQuantity'] * $val['DiscountPrice']);
					$carts->PurchasedDate		= 	date('Y-m-d H:i:s');
					
					// save the carts to the database
					if($_SERVER['REMOTE_ADDR'] == '172.21.4.56'){  } else {
						R::store($carts);
					}
				}
				
				//Storing Order
				//$transId						= 	'TUPLIT-TRANS-'. time();
				$bean->fkCartId					= 	$CartId;
				$bean->fkUsersId				= 	$bean->UserId;		
				$bean->fkMerchantsId			= 	$bean->MerchantId;
				$bean->TransactionId			= 	$transId;
				$bean->OrderDate 				= 	date('Y-m-d H:i:s');
				$bean->OrderStatus 				= 	'0';        
				$bean->Status 					= 	'1';
				unset($bean->UserId);
				unset($bean->MerchantId);
				unset($bean->CartDetails);
				
				// save the bean to the database
				if($_SERVER['REMOTE_ADDR'] == '172.21.4.215'){ 
					$orderId					=	"13";
				} else {
					$orderId 					=    R::store($this);					
					if($bean->OrderDoneBy == 2){
						$notification 						= 	R::dispense('notifications');
						$notification->orderId 				= 	$orderId ;
						$notification->userId 				= 	$bean->fkUsersId;
						$notification->merchantId 			= 	$bean->fkMerchantsId;
						$notification->sendNotification(2);
					}
				}
				$resultArray['orderId'] 		= 	$orderId;
				$resultArray['CartDetails'] 	= 	$CartDetails;
				$resultArray['CartId'] 			= 	$CartId;
				$resultArray['TransactionId'] 	= 	$transId;
				return $resultArray;
			} else {
				 /**
				 * throwing error when product data found
				 */
				  throw new ApiException("One of the product in your cart is not in active status ", ErrorCodeType::NoResultFound);
			}
		}
    }
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate()
    {
		$bean 	= 	$this->bean;
		$type	=	$bean->OrderDoneBy;	
		if($type == ''){			
			$rules = [
	            'required' => [
	                 ['UserId'],['MerchantId'],['TotalItems'],['TotalPrice'],['CartDetails']
	            ]
	        ];
		}
		if($type == 1){			
			$rules = [
	            'required' => [
	                ['MerchantId'],['TotalItems'],['TotalPrice'],['CartDetails']
	            ]
	        ];
		}
		if($type == 2){			
			$rules = [
	            'required' => [
	                 ['UserId'],['TotalItems'],['TotalPrice'],['CartDetails']
	            ]
	        ];
		}
				
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the Order properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	
	/**
	* get NewOrders
	*/    
	public function getNewOrderDetails()
    {
		$bean 				= 	$this->bean;
		$merchantId			=	$bean->MerchantID;
		$type				=	$bean->Type;
		$Start				=	$bean->Start;
		$End				=	$bean->End;
		if($type == 0) {
			$fields			=	"ord.id as OrderId,u.FirstName,u.LastName, ord.TotalPrice,OrderDoneBy";
			$joincondition	= 	"left join users u on (ord.fkUsersId = u.id)";
			$condition		= 	"ord.OrderStatus = 0 and u.Status = 1";
			$limit			=	"limit 0,2 ";
		} else {
			$fields			= 	"SQL_CALC_FOUND_ROWS u.UniqueId as UserId,u.FirstName,u.LastName,u.Email,u.Photo,ord.id as OrderId,ord.fkCartId as CartId,ord.TotalItems,ord.TotalPrice,ord.TransactionId,ord.OrderStatus,ord.OrderDate as OrderDate,ord.OrderDoneBy";
			$joincondition	= 	"left join users u on (ord.fkUsersId = u.id)";
			$condition		= 	"ord.OrderStatus = 0 and u.Status = 1";
			$limit			=	"limit ".$Start.",".$End;
		}
	
		/**
		* Query to get NewOrders 
		*/
		$sql 		= 	"SELECT ".$fields." from orders ord ".$joincondition." where ".$condition." and ord.fkMerchantsId = ".$merchantId." order by ord.id desc ".$limit;
   		$result 	= 	R::getAll($sql);
		if($result){
			/**
			* The result were found
			*/
			 if($type == 0)
				return $result;
			else {
				$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
				$total 			= 	(integer)$totalRec[0]['count'];
				$listedCount	= 	count($result);
				/**
				* The result were found
				*/	
				foreach($result as $key=>$value) {
					if(!empty($value['Photo'])){
						$result[$key]['Photo'] 		= 	USER_IMAGE_PATH.$value['Photo'];
						$result[$key]['ThumbPhoto'] = 	USER_THUMB_IMAGE_PATH.$value['Photo'];
					}
					else{
						$result[$key]['Photo']		= 	$result[$key]['Photo'] = ADMIN_IMAGE_PATH."no_user.jpeg";
						$result[$key]['ThumbPhoto']	= 	$result[$key]['ThumbPhoto'] = ADMIN_IMAGE_PATH."no_user.jpeg";
					}
					$fields1			= 	"c.fkProductsId,p.ItemName,c.ProductsQuantity,c.ProductsCost,c.DiscountPrice,c.TotalPrice";
					$joincondition1		= 	"left join products p on (c.fkProductsId = p.id)";
					$sql1 				= 	"SELECT ".$fields1." from carts as c ".$joincondition1." where c.CartId='".$value['CartId']."'";
					$result1 			= 	R::getAll($sql1);
					$newProdRes =array();
					if($result1 ){
						foreach($result1 as $key1=>$value1){
							$value1['ItemName']	=	ucfirst($value1['ItemName']);
							$newProdRes[] 			= $value1;
						}
					}
					$result[$key]['Products'] = $newProdRes ;
				}
				
				$result['result']		= $result;
				$result['totalCount']	= $total;
				$result['listedCount']	= $listedCount;
				return $result;
			}
		}
		else{
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
	* get Order List
	*/
	public function getOrderList($merchantId,$Type)
    {
		/**
		* Query to get OrderList 
		*/
		$userId 		= 	'';
		$bean 			= 	$this->bean;
		if(isset($bean->UserId))
			$userId		=	$bean->UserId;
		if($bean->Start)
			$start 		= 	$bean->Start;
		else
			$start 		= 	0;
		if($bean->Limit)
			$limit 		= 	$bean->Limit;
		else
			$limit 		= 	10;
		if(isset($bean->FromDate))			
			$fromDate	=	$bean->FromDate;
		if(isset($bean->ToDate))			
			$toDate		=	$bean->ToDate;
		if(isset($bean->UserName))			
			$UserName	=	$bean->UserName;
		if(isset($bean->TransactionId))			
			$TransactionId	=	$bean->TransactionId;
		if(isset($bean->OrderStatus))
			$OrderStatus	=	$bean->OrderStatus;
		if(isset($bean->Price))
			$Price	=	(integer)$bean->Price;			
			
		$fields			= 	"u.id as PUserId,u.UniqueId as UserId,u.FirstName,u.LastName,u.Email,u.Photo,ord.id as OrderId,ord.fkCartId as CartId,ord.TotalItems,ord.TotalPrice,ord.TransactionId,ord.OrderStatus,ord.OrderDate as OrderDate,ord.OrderDoneBy";
		$joincondition	= 	"left join users u on (ord.fkUsersId = u.id)";
				
		if($Type == 0) {			
			$condition	= 	"ord.OrderStatus in (0,1,2)";
		} else {
			$condition	= 	"ord.OrderStatus in (1,2) and ord.OrderDate like '".date('Y-m-d')."%' order by ord.OrderDate desc";
		}
		
		if(isset($fromDate) && $fromDate != ''	&&	isset($toDate) && $toDate != ''){
			$condition .= " AND ((date(OrderDate) >=  '".date('Y-m-d',strtotime($fromDate))."' and date(OrderDate) <= '".date('Y-m-d',strtotime($toDate))."') ) ";
		}
		else if(isset($fromDate) && $fromDate != '')
			$condition .= " AND date(OrderDate) >=  '".date('Y-m-d',strtotime($fromDate))."'";
		else if(isset($toDate) && $toDate != '')
			$condition .= " AND date(OrderDate) <=  '".date('Y-m-d',strtotime($toDate))."'";
		
		if(isset($UserName) && $UserName != '')
			$condition .= " AND (u.FirstName like '%".$UserName."%' or u.LastName like '%".$UserName."%')";
			
		if(isset($TransactionId) && $TransactionId != '')
			$condition .= " AND ord.TransactionId ='".$TransactionId."'";
		
		if(isset($OrderStatus) && $OrderStatus != '') {
			$OrderStatus=	$OrderStatus - 1;
			$condition .= " AND ord.OrderStatus ='".$OrderStatus."'";
		}
		if(isset($Price) && $Price != '') {
			$condition .= " AND ord.TotalPrice >=".$Price."";
		}	
		if($userId != '')
			$condition	.= 	" and u.id = ".$userId."  order by ord.id desc limit $start,$limit";
		else if($Type == 0)
			$condition	.= 	" order by ord.id desc limit $start,$limit";
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS ".$fields." from orders as ord  ".$joincondition." where u.Status = 1  and ord.fkMerchantsId = ".$merchantId." and ".$condition." ";
   		//echo $sql;
		$result 		= 	R::getAll($sql);
		$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= 	(integer)$totalRec[0]['count'];
		$listedCount	= 	count($result);
		if($result){
			/**
			* The result were found
			*/
			foreach($result as $key=>$value) {
				if(!empty($value['Photo'])){
					$result[$key]['Photo'] 		= 	USER_IMAGE_PATH.$value['Photo'];
					$result[$key]['ThumbPhoto'] = 	USER_THUMB_IMAGE_PATH.$value['Photo'];
				}
				else{
					$result[$key]['Photo']		= 	$result[$key]['Photo'] = ADMIN_IMAGE_PATH."no_user.jpeg";
					$result[$key]['ThumbPhoto']	= 	$result[$key]['ThumbPhoto'] = ADMIN_IMAGE_PATH."no_user.jpeg";
				}
				$fields1			= 	"c.fkProductsId,p.ItemName,p.Photo,c.ProductsQuantity,c.ProductsCost,c.DiscountPrice,c.TotalPrice";
				$joincondition1		= 	"left join products p on (c.fkProductsId = p.id)";
				$sql1 				= 	"SELECT ".$fields1." from carts as c ".$joincondition1." where c.CartId='".$value['CartId']."'";
				$result1 			= 	R::getAll($sql1);
				$productsRes		=	array();
				if($result1 ){
					foreach($result1 as $key1=>$value1){
						$value1['ItemName']	=	ucfirst($value1['ItemName']);
						$productsRes[] 			= $value1;
					}
				}
				$result[$key]['Products'] = $productsRes ;
				//$result[$key]['Products'] = $productsRes;
			}
			
			$result['result']		= $result;
			$result['totalCount']	= $total;
			$result['listedCount']	= $listedCount;
			return $result;
		}
		else{
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
	* @param Modify the Orders	 
	*/
    public function modify()
	{
		
		/**
		* Get the bean
		* @var $bean Orders
		*/
		$bean 			= 	$this->bean;
		$userid 		= 	$bean->userId;
		$merchnatid 	= 	$bean->MerchantId;
		
		//validate
		$this->validateModifyParams();
		
		//validate Order exists or not
		$orderdetails 	=	$this->validateCreate();
		
		unset($bean->userId);
		unset($bean->MerchantId);
		
		if($orderdetails) {
			$bean->id 			= 	$bean->OrderId;
			unset($bean->OrderId);			
			if(!empty($userid) && $bean->OrderStatus == 1) {
				$userdata 		= 	R::findOne('users', 'id = ? and Status=1', [$userid]);
				if($userdata) {
					
					//Check for user is in store
					$distance	=	(((acos(sin(($bean->Latitude*pi()/180)) * sin(($userdata->Latitude*pi()/180))+cos(($bean->Latitude*pi()/180)) * cos(($userdata->Latitude*pi()/180)) * cos((($bean->Longitude- $userdata->Longitude)*pi()/180))))*180/pi())*60*1.1515);
					$admin	 	= 	R::findOne('admins', 'id = ?', [1]);
					if($distance > $admin->LocationLimit) {
						//throws when user not in store 
						throw new ApiException("You are not in the store to approve/reject this order.", ErrorCodeType::NotAllowed);
					}
					
					//Updating Current balance
					/*if($userdata->CurrentBalance >= $orderdetails->TotalPrice) {
						$newamount		=	$userdata->CurrentBalance - $orderdetails->TotalPrice;
						$sql4			=	'update users set  CurrentBalance ='.$newamount.' where id='.$userid;
						R::exec($sql4);						
					}else {
						throw new ApiException("Low balance. User not having enough balance", ErrorCodeType::CheckBalanceError);
					}	*/
					
				} else {
					//throws when order place by user and user trying to approve/reject order 
					throw new ApiException("Your status was not in active state.", ErrorCodeType::NotAllowed);
				}				
			} else if($bean->OrderStatus == 2 && $orderdetails->OrderDoneBy == 1) {
			
				//Refunding the total amount while rejecting
				$userdata 			= 	R::findOne('users', 'id = ? and Status=1', [$orderdetails->fkUsersId]);
				if($userdata) {					
					$newamount		=	$userdata->CurrentBalance + $orderdetails->TotalPrice;
					$sql4			=	'update users set  CurrentBalance ='.$newamount.' where id='.$orderdetails->fkUsersId;
					R::exec($sql4);
				} else {
					//throws when order place by user and user trying to approve/reject order 
					throw new ApiException("Your status was not in active state.", ErrorCodeType::NotAllowed);
				}
				
			}
			
			// updating the orders
			$sql 		=	"update orders set  OrderStatus =".$bean->OrderStatus.", OrderDate='".date('Y-m-d H:i:s')."' where id=".$bean->id;
			R::exec($sql);
			
			if($_SERVER['REMOTE_ADDR'] == '172.21.4.104'){
				//push notification while approve/reject an order
				if($orderdetails->OrderDoneBy == 1 || ($orderdetails->OrderDoneBy == 2 && $bean->OrderStatus == 2)) {
					$notification 						= 	R::dispense('notifications');
					$notification->orderId 				= 	$bean->id;
					$notification->userId 				= 	$orderdetails->fkUsersId;
					$notification->merchantId 			= 	$orderdetails->fkMerchantsId;
					if($bean->OrderStatus == 1) {						
						$notification->sendNotification(3);// if Approved
					} else if($bean->OrderStatus == 2){
						$notification->sendNotification(4);// if Rejected
					}
				}
			}
		}
    }
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validateModifyParams($type='')
    {
		$bean = $this->bean;
		if($type == ''){			
			$rules = [
	            'required' => [
	                 ['OrderId'],['OrderStatus']
	            ]
	        ];
		}
				
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the Order properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the updating of orders
	*/
	public function validateCreate()
    {
		/**
		* Get the bean
		* @var $bean Orders
		*/
        $bean 			= 	$this->bean;
		$userid 		= 	$bean->userId;
		$merchnatid 	= 	$bean->MerchantId;
		
		/**
		* check for order exist       
		*/
		if(isset($bean->OrderId) && !empty($bean->OrderId)) {	
			$modifiedBy = R::findOne('orders', 'id = ?', [$bean->OrderId]);
			if (!$modifiedBy) {
				// the order was not found
				throw new ApiException("Order Id not found.", ErrorCodeType::NoResultFound);
			} else {				
				//Checking who is approving/rejecting
				if(!empty($userid) && ($modifiedBy->fkUsersId == $userid && $modifiedBy->OrderDoneBy == 1)) {
					//throws when order place by user and user trying to approve/reject order 
					throw new ApiException("You can't approve/reject this order. Because this order can approve/reject only by merchant.", ErrorCodeType::NotAllowed);
				}
				else if(!empty($merchnatid) && ($modifiedBy->fkMerchantsId == $merchnatid && $modifiedBy->OrderDoneBy == 2 && $bean->OrderStatus ==1)) {
					//throws when order place by merchant and merchant trying to approve/reject order
					throw new ApiException("You can't approve/reject this order. Because this order can approve/reject only by user.", ErrorCodeType::NotAllowed);
				}
				
				//checking already approved/rejected
				if($modifiedBy->OrderStatus != 0 && $modifiedBy->OrderStatus == $bean->OrderStatus) {
					if($bean->OrderStatus == 1) {
						//throws when order already accepted
						throw new ApiException("Order is already approved.", ErrorCodeType::OrderAlreadyApproved);
					}
					else if($bean->OrderStatus == 2) {
						//throws when order already rejected
						throw new ApiException("Order is already rejected.", ErrorCodeType::OrderAlreadyRejected);
					}
				}
				return  $modifiedBy;
			}
		}	
    }
	
	/**
	* get OrdersDetails
	*/
	public function getOrderDetails($orderId)
    {
		$sql			=	"SELECT ord.id as OrderId, ord.fkCartId AS CartId, ord.fkUsersId AS UserId, ord.fkMerchantsId AS MerchantId, ord.TotalPrice,ord.TransactionId, m.CompanyName, m.Address, m.Location,u.Photo,u.FirstName,u.LastName,u.Email,ord.OrderDate,ord.OrderDoneBy,u.UniqueId FROM `orders` AS ord
								LEFT JOIN merchants AS m ON ( ord.fkMerchantsId = m.id )
								LEFT JOIN users AS u ON ( u.id = ord.fkUsersId )
								WHERE 1 AND ord.id = '".$orderId."'";
		$result 		= 	R::getAll($sql);
		if($result) {
			if(!empty($result[0]['Photo'])){
				$result[0]['Photo'] = USER_THUMB_IMAGE_PATH.$result[0]['Photo'];
			}
			else{
				$result[0]['Photo']=  ADMIN_IMAGE_PATH."no_user.jpeg";
			}
			$sql1		=	"SELECT  c.fkProductsId as ProductID, p.ItemName,p.Photo, c.ProductsQuantity, c.ProductsCost, c.DiscountPrice, c.TotalPrice  FROM carts c
								left join products p on (c.fkProductsId = p.id)
								where c.CartId='".$result[0]['CartId']."'";
			$result1 	= 	R::getAll($sql1);
			
			if($result1)
				foreach($result1 as $key=>$val){
					 if(!empty($val['Photo'])){
						$imagePath				=	PRODUCT_IMAGE_PATH.$val['Photo'];
						$val['Photo'] 			= 	$imagePath;
						$val['ItemName'] 		= 	ucfirst($val['ItemName']);
						$resultProduct[]		= 	$val;
					}
				}
				$result[0]['Products']	= $resultProduct;
				
			return	$result;
		}
		else {
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No product Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
	* get User Created order Details
	*/
	public function getUserOrderDetails($userId,$Type = '',$details='')
    {
		/**
		* Get the bean
		* @var $bean Orders
		*/			
		$bean 			= 	$this->bean;
		$condition		=	'';
		
		$orderArray 	= 	array();
		$start 			= 	0;
		$limit 			= 	20;
		if($bean->Start)
			$start 		= 	$bean->Start;
		if($bean->Limit)
			$limit 		= 	$bean->Limit;
		
		if($Type == 1)
			$condition	=	' and ord.OrderStatus=1 ';
		$orderSql 		= 	"SELECT SQL_CALC_FOUND_ROWS ord.id as OrderId,ord.OrderStatus,ord.fkMerchantsId as MerchantID,m.CompanyName,m.Location,m.Icon as MerchantIcon,ord.TotalItems,ord.TotalPrice,ord.OrderDate 
							from orders ord 
							LEFT JOIN merchants m ON ord.fkMerchantsId = m.id 
							where ord.Status = 1 ".$condition." and ord.fkUsersId=".$userId." order by ord.OrderDate desc limit $start,$limit";
		$orders 		= 	R::getAll($orderSql);
		$ordersTotal	=	R::getAll('SELECT FOUND_ROWS() as count ');
		$total			=	$ordersTotal[0]['count'];
		$listed			=	count($orders);		
		if($orders){
			
			foreach($orders as $key => $value){
				if(isset($value['MerchantIcon']) && $value['MerchantIcon'] != '')
					$value['MerchantIcon'] 	= 	MERCHANT_ICONS_IMAGE_PATH.$value['MerchantIcon'];	
				else
					$value['MerchantIcon'] 	= 	'';					
				$orderArray[] 				= 	$value;
				unset($value['MerchantIcon']);
			}		
			$orderDetails['OrderDetails'] 		= 	$orderArray;
			$orderDetails['Total'] 				= 	$total;
			$orderDetails['Listed'] 			= 	$listed;
			return $orderDetails;
		}
		else{
			if($details == 1){
				$orderDetails['OrderDetails'] 		= 	array();
				$orderDetails['Total'] 				= 	0;
				$orderDetails['Listed'] 			= 	0;
			}
			else{
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No Transactions Found", ErrorCodeType::NoResultFound);
			}
		}
	}
	
	/**
	* get Already Shopped Friends
	*/
	public function getAlreadyShoppedFriends($merchantId,$userId)
    {
		$totalorderedusers	=	$orderedFriendsCount	=	0;
		$friendsListArray	=	$resultarray			=	array();
		$userSql 			= 	"SELECT SQL_CALC_FOUND_ROWS fkUsersId as userId FROM orders where fkMerchantsId=".$merchantId." and OrderStatus = 1 and Status = 1 group by fkUsersId";
		$userIds			= 	R::getAll($userSql);
		$total				=	R::getAll('SELECT FOUND_ROWS() as count ');
		$totalorderedusers	=	$total[0]['count'];
		if($userIds && !empty($userId)) {
			foreach($userIds as $key=>$val){
				$userShopped[] = $val['userId'];
			}
			$sql 			= 	"SELECT  group_concat(`fkFriendsId`,',', fkUsersId) as friendsId  FROM friends where  (fkUsersId = ".$userId." or fkFriendsId = ".$userId.") and Status = 1 ";
			$friends		= 	R::getAll($sql);
			if($friends){
				$friendsArray 		= 	array_unique(explode(',',$friends[0]['friendsId']));
				$friendsShopped 	= 	array_intersect($userShopped, $friendsArray);				
				if(is_array($friendsShopped) && count($friendsShopped) > 0){
					$friendsIds 				= 	implode(',',$friendsShopped);
					$sql1 						= 	"SELECT SQL_CALC_FOUND_ROWS id,FirstName,LastName,Photo FROM users where id IN (".$friendsIds.") and Status = 1";
					$friendsArray 				= 	R::getAll($sql1);
					$total						=	R::getAll('SELECT FOUND_ROWS() as count ');
					$orderedFriendsCount		=	$total[0]['count'];
					foreach($friendsArray as $key => $value){
						$user_image_path 		= 	'';
						if(isset($value['Photo']) && $value['Photo'] != ''){
							$user_image_path 	= 	USER_THUMB_IMAGE_PATH.$value['Photo'];
						}
						$value['Photo'] 		= 	$user_image_path;
						$friendsListArray[]		= 	$value;
						if($key >=2)
							break;
					}
				}
			}
		}
		$resultarray['OrderCount']				=	$totalorderedusers;	
		$resultarray['OrderedFriendsCount']		=	$orderedFriendsCount;
		$resultarray['OrderedFriendsList']		=	$friendsListArray;
		return	$resultarray;
	}
	
	/**
    * get Transaction List
    */
    public function getTransactionList($merchantId)
    {
		$condition 		= $field = '';
		$bean 			= 	$this->bean;
		$this->validateMerchants($merchantId,1);
		if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
			$time_zone 	= 	getTimeZone();
			$_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
		} else {
			$time_zone 	= 	$_SESSION['tuplit_ses_from_timeZone'];
		}
		$dataType 		= 	$bean->DataType;
		$startDate 		= 	$bean->StartDate;
		$endDate   		= 	$bean->EndDate;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($dataType=='year') {
			$field 		= 	" , MONTH(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS month";
			$condition 	.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by month";
		} else if($dataType=='month') {
			$field 		= 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition .= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by day";
		} else if($dataType=='day') {
			$field 		= 	" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
			$condition .= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
		} else if($dataType=='between') {
			$field 		= 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate !='')
			{
				$condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) between '".date('Y-m-d',strtotime($startDate))."' and '".date('Y-m-d',strtotime($endDate))."'";
			} else if(isset($startDate) && $startDate!='') {
				$condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime($startDate))."'";
			} else if(isset($endDate) && $endDate!='') {
				$condition 	.= 	" and DATE(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($endDate))."'";
			} 
			$condition 		.= 	' group by day';
		} else if($dataType=='7days') {
			$field 			 = 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition 		.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  group by day";
		}
		else if($dataType=='timeofday') {
			$field 			= 	" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
			$condition 		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
		}
		$sql 				= 	"SELECT  count(id) as TotalOrders,SUM(TotalPrice) as TotalPrice ".$field." from orders as o 
								where 1 and  o.OrderStatus IN (0,1,2) and o.fkMerchantsId = ".$merchantId."  ".$condition."";	
		//echo $sql;
		$TransactionList 	=	R::getAll($sql);
		if($TransactionList ){
			foreach($TransactionList as $key=>$value){
				$totalOrders						=	$value["TotalOrders"];
				$totalPrice							=	$value["TotalPrice"];
				$averageTransaction					=	$totalPrice/$totalOrders;
				$value["TotalPrice"]				=	number_format((float)$totalPrice, 2, '.', '');
				$value["Average"]					=	number_format((float)$averageTransaction, 2, '.', '');
				$TransactionListArray[] 			= 	$value;
			}
			
			$TransactionArray['result']            	=  	$TransactionListArray;
			return $TransactionArray;
		}
		else{
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
	* Validate the modification
	*/
	public function validateMerchants($merchantsId,$type='')
    {		
		/**
		* Get the identity of the person requesting the details
		*/
		$sql		 = "select id,FirstName,LastName,Email,Status from merchants where id = '".$merchantsId."' and Status != 3";
		$merchants	 = R::getAll($sql);
        if (!$merchants) {			
			// the Merchants was not found
			throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
		else{
			if($merchants[0]['Status'] == 0){
				// the Merchants was not found
				throw new ApiException("Sorry! you cannot do this process", ErrorCodeType::NotAccessToDoProcess);
			}
			if($merchants[0]['Status'] == 2){
				// the Merchants was not found
            	throw new ApiException("Your status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
			}
			return $merchants;
		}
    }
	
	/**
    * get Product Analysis
    */
    public function getProductAnalysis($merchantId)
    {
		$condition 		= $field = $sort_condition = '';
		$bean 			= 	$this->bean;
		$this->validateMerchants($merchantId,1);
		if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
			$time_zone 	= 	getTimeZone();
			$_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
		} else {
			$time_zone 	= 	$_SESSION['tuplit_ses_from_timeZone'];
		}
		$type 			= 	$bean->Type;
		$dataType 		= 	$bean->DataType;
		$startDate 		= 	$bean->StartDate;
		$endDate   		= 	$bean->EndDate;
		$sortVal   		= 	$bean->Sorting;
		$fieldVal  		= 	$bean->Field;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($type == 2)
		 	 $group_condition	= 'p.fkCategoryId ';
		else 
			$group_condition	= 'p.id';
		if($dataType=='year') {
			$field 		= 	" , MONTH(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS month";
			$condition 	.=	 "  and DATE_FORMAT(PurchasedDate,'%Y') = ".$cur_year." group by  ".$group_condition."";
		} else if($dataType=='month') {
			$field 		= 	" , DATE_FORMAT(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition .= 	"and DATE_FORMAT(PurchasedDate,'%m') = ".$cur_month." and DATE_FORMAT(PurchasedDate,'%Y') = ".$cur_year." group by ".$group_condition."";
		} else if($dataType=='day') {
			$field 		= 	" , HOUR(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) AS hour";
			$condition .= 	" and date(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by ".$group_condition."";
		} else if($dataType=='between') {
			$field 		= 	" , DATE_FORMAT(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			if(isset($startDate) && $startDate!='' && isset($endDate) && $endDate !='')
			{
				$condition 	.= 	" and DATE(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) between '".date('Y-m-d',strtotime($startDate))."' and '".date('Y-m-d',strtotime($endDate))."'";
			} else if(isset($startDate) && $startDate!='') {
				$condition 	.= 	" and DATE(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) >= '".date('Y-m-d',strtotime($startDate))."'";
			} else if(isset($endDate) && $endDate!='') {
				$condition 	.= 	" and DATE(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($endDate))."'";
			} 
			$condition 		.= 	' group by '.$group_condition.'';
		} else if($dataType=='7days') {
			$field 			 = 	" , DATE_FORMAT(DATE_ADD(PurchasedDate,INTERVAL '".$_SESSION['tuplit_ses_from_timeZone']."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition 		.= 	"and (DATE_FORMAT(PurchasedDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(PurchasedDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  group by ".$group_condition."";
		}
		if($sortVal != '' && $fieldVal != '' ){
			$sort_condition = ' order by '.$fieldVal.' '.$sortVal.'';
		}
		else{
			$sort_condition = ' order by Name asc';
		}
		if($type == 2 ){
			$sql 				= 	"SELECT  pc.CategoryName as Name,pc.id as CategoryId,SUM(ProductsQuantity) as TotalQuantity,SUM(c.TotalPrice) as TotalPrice,
									COUNT(o.id) as TotalOrders ".$field." from orders as o
									left join carts as c ON (c.CartId = o.fkCartId)
		                         	left join products as p on(p.id = c.fkProductsId)
									left join productcategories as pc on(pc.id = p.fkCategoryId )
									where 1  and  o.OrderStatus IN (0,1,2) and pc.fkMerchantId = ".$merchantId." and o.fkMerchantsId =   ".$merchantId." and c.fkMerchantsId = ".$merchantId." and p.fkMerchantsId =  ".$merchantId."   ".$condition." ".$sort_condition."";	
		}
		else {
			$sql 				= 	"SELECT  p.ItemName as Name,p.id as ProductId ,SUM(ProductsQuantity) as TotalQuantity,SUM(c.TotalPrice) as TotalPrice,
									COUNT(o.id) as TotalOrders ".$field." from orders as o 
									left join carts as c ON (c.CartId = o.fkCartId)
		                         	left join products as p on(p.id = c.fkProductsId)
									where 1   and  o.OrderStatus IN (0,1,2) and c.fkMerchantsId = ".$merchantId." and p.fkMerchantsId =  ".$merchantId."  ".$condition." ".$sort_condition."";	
		}
	//	echo '-->'.$sql 	 .'<br>';
		$productAnalysis 	=	R::getAll($sql);
		if($productAnalysis ){
			foreach($productAnalysis as $key=>$value){
				$totalOrders						=	$value["TotalQuantity"];
				$totalPrice							=	$value["TotalPrice"];
				$averageTransaction					=	$totalPrice/$totalOrders;
				$value["TotalPrice"]				=	number_format((float)$totalPrice, 2, '.', ',');
				$value["Average"]					=	number_format((float)$averageTransaction, 2, '.', ',');
				$ProductListArray[] 				= 	$value;
			}
			
			$ProductArray['result']            	=  	$ProductListArray;
			return $ProductArray;
		}
		else{
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
    * Payment amount between user and merchant
    */
    public function doPayment(){

		/**
        * Get the bean
        */
		$bean 										= 	$this->bean;
		
		// validate the model
        $this->validatePaymentParams();
		
		//validate and get userdetails		
        $userInfo									=	$this->validatePaymentUsers();
		if($userInfo) {
			$user									=	$userInfo['fromuser'];
			$merchant								=	$userInfo['touser'];
			$admin									=	$userInfo['admin'];
			$walletDetails							=	getWalletDetails($user->WalletId);
			if($walletDetails) {
				if(isset($walletDetails->Balance->Amount) && ($walletDetails->Balance->Amount >= $bean->Amount)) {
					$userDetails['AuthorId']			=	$user->MangoPayUniqueId;
					$userDetails['CreditedUserId']		=	$merchant->MangoPayUniqueId;
					$userDetails['Currency']			=	$bean->Currency;
					$userDetails['Amount']				=	$bean->Amount;
					$userDetails['DebitedWalletId']		=	$user->WalletId;
					$userDetails['CreditedWalletId']	=	$merchant->WalletId;
					$userDetails['FeesAmount']			=	$admin->MangoPayFees;
					$result								=	payment($userDetails);
					//echo "<pre>"; echo print_r($result); echo "</pre>";
					return $result;
				} else {
					// low balance
					throw new ApiException("Insufficient amount in account.", ErrorCodeType::CheckBalanceError);
				}
			}		
		}			
    }
	/**
    * Validate the fields of payment
    */
    public function validatePaymentParams()
    {
		$bean 		= 	$this->bean;
	  	$rules 		= 	[
							'required' => [
								['MerchantId'],['UserId'],['Amount'],['Currency']
							]
						];
		
        $v 			= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check payment amount fields." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
	}
	/**
    * Validate the transferAmount
    */
    public function validatePaymentUsers()
    {
		$bean 		= 	$this->bean;
		
		$admin		= R::findOne('admins');
		$adminFees	= $admin->MangoPayFees;
		//echo "===>".$bean->Amount;
		//echo "====>".$adminFees;
		if($bean->Amount < $adminFees){
			throw new ApiException("The amount should be greater than fees.", ErrorCodeType::PaymentAccountError);
		}		
		
		//User
		$fromuser = R::findOne('users', 'id = ? and Status=1', array($bean->UserId));
		if(!$fromuser) {
			// the User was not found
			throw new ApiException("you are not in active state to make payment.", ErrorCodeType::UserNotInActiveStatus);
		} else {
			if($fromuser->MangoPayUniqueId == '') {
				//Not connected with payment accounts
				throw new ApiException("You are not connected with MangoPay to make payment.", ErrorCodeType::PaymentAccountError);
			}
		}
		
		//Merchant
		$touser = R::findOne('merchants', 'id = ? and Status=1', array($bean->MerchantId));
		if(!$touser) {
			// the User was not found
			throw new ApiException("The merchant is not in active state.", ErrorCodeType::UserNotInActiveStatus);
		} else {
			if($touser->MangoPayUniqueId == '') {
				//Not connected with payment accounts
				throw new ApiException("The merchant is not connected with MangoPay.", ErrorCodeType::PaymentAccountError);
			}
		}	
		
		$out['fromuser']	=	$fromuser;
		$out['touser']		=	$touser;
		$out['admin']		=	$admin;
		return $out;
	}
}