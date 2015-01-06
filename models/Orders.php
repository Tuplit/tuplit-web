<?php

/**
 * Description of Orders
 *
 * @author 
 */
ini_set('default_encoding','utf-8');
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
		$VATPercentage				=	$commision = 0;
		global	$ProductVAT;
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
			$productVAT				=	$merchant->ProductVAT;
			if($productVAT > 0 && isset($ProductVAT[$productVAT])){
				$VatAmount			=	$ProductVAT[$productVAT];
				$VATPercentage			=	$bean->TotalPrice*($VatAmount/100);
			}
			$VAT					=	$VATPercentage;
			$TotalPrice				=	$bean->TotalPrice+$VATPercentage;
			$mangoPayFees			=	$admin->MangoPayFees;
			$commision				=	$TotalPrice*($mangoPayFees/100);
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

					//when user done order
					$logStart				=	microtime(true);
					$walletDetails			=	getWalletDetails($user->WalletId);
					
					//MangoPay Log
					$logArray				=	Array();	
					$logArray['UserId']		=	$bean->UserId;
					$logArray['MerchantId']	=	$bean->MerchantId;
					$logArray['URL']		=	'getWalletDetails';
					$logArray['Content']	=	Array('WalletId'=>$user->WalletId);
					$logArray['Start']		=	$logStart;
					$logArray['End']		=	microtime(true);
					$logArray['Response']	=	$walletDetails;
					
					$log 	=	R::dispense('users');
					$log->storeMangoPayLog($logArray);					
					
					if($walletDetails) {
						if(isset($walletDetails->Balance->Amount) && ($walletDetails->Balance->Amount >= getCents($TotalPrice))) {
							$userDetails1['AuthorId']			=	$user->MangoPayUniqueId;
							$userDetails1['CreditedUserId']		=	$merchant->MangoPayUniqueId;
							$userDetails1['Currency']			=	DEFAULT_CURRENCY;
							$userDetails1['Amount']				=	$TotalPrice;
							$userDetails1['DebitedWalletId']	=	$user->WalletId;
							$userDetails1['CreditedWalletId']	=	$merchant->WalletId;
							$userDetails1['FeesAmount']			=	$admin->MangoPayFees;
							
							$logStart							=	microtime(true);
							$result								=	payment($userDetails1);
							
							//MangoPay Log
							$logArray				=	Array();	
							$logArray['UserId']		=	$bean->UserId;
							$logArray['MerchantId']	=	$bean->MerchantId;
							$logArray['URL']		=	'payment';
							$logArray['Content']	=	$userDetails1;
							$logArray['Start']		=	$logStart;
							$logArray['End']		=	microtime(true);
							$logArray['Response']	=	$result;
							$log 	=	R::dispense('users');
							$log->storeMangoPayLog($logArray);
							
							
							if($result && isset($result->Id) && !empty($result->Id) && isset($result->Status) && $result->Status == 'SUCCEEDED') {
								$transId						= 	$result->Id;								
							} else {
								// Error occurred during payment/transaction
								throw new ApiException("Error occurred during payment/transaction", ErrorCodeType::CheckBalanceError);
							}
							
						} else {
							// low balance
							throw new ApiException("Insufficient amount in account", ErrorCodeType::CheckBalanceError);
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
					if($bean->OrderDoneBy == 1 && !empty($transId))
						$carts->Refund			= 	'1';
					else
						$carts->Refund			= 	'0';
					
					// save the carts to the database
					if($_SERVER['REMOTE_ADDR'] == '172.21.4.56'){  } else {
						R::store($carts);
					}
				}
				//Storing Order
				$bean->fkCartId					= 	$CartId;
				$bean->fkUsersId				= 	$bean->UserId;		
				$bean->fkMerchantsId			= 	$bean->MerchantId;
				$bean->TransactionId			= 	$transId;
				$bean->OrderDate 				= 	date('Y-m-d H:i:s');
				$bean->OrderStatus 				= 	'0';        
				$bean->Status 					= 	'1';
				$bean->SubTotal 				= 	number_format((float)$bean->TotalPrice, 2, '.', '');
				$bean->TotalPrice				= 	number_format((float)$TotalPrice, 2, '.', '');
				$bean->VAT	 					= 	number_format((float)$VAT, 2, '.', '');
				if($bean->OrderDoneBy == 1 && !empty($transId)){
					$bean->RefundStatus			= 	'1';
					$bean->Commision			= 	number_format((float)$commision, 2, '.', '');
				}
				else{
					$bean->RefundStatus			= 	'0';
					$bean->Commision			= 	'0';
				}
				
				
				unset($bean->UserId);
				unset($bean->MerchantId);
				unset($bean->CartDetails);
				
				// save the bean to the database
				if($_SERVER['REMOTE_ADDR'] == '172.21.4.215'){ 
					$orderId					=	"13";
				} else {
					$orderId 								=   R::store($this);					
					if($bean->OrderDoneBy == 2 ){	// && $user->PushNotification == 1 && $user->BuySomething == 1
					$notification 						= 	R::dispense('notifications');
					$notification->orderId 				= 	$orderId ;
					$notification->userId 				= 	$bean->fkUsersId;
					$notification->merchantId 			= 	$bean->fkMerchantsId;
					//$notification->orderDoneBy 			= 	$bean->OrderDoneBy;
					$notification->sendNotification(2);
					}
				}
				$resultArray['orderId'] 		= 	$orderId;
				$resultArray['CartDetails'] 	= 	$CartDetails;
				$resultArray['CartId'] 			= 	$CartId;
				$resultArray['TransactionId'] 	= 	$transId;
				$resultArray['SubTotal'] 		= 	$bean->SubTotal;
				$resultArray['VAT'] 			= 	$VAT;
				$resultArray['Total'] 			= 	$TotalPrice;
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
    * Validate the amount
    */
    public function validateAmount($amount)
    {
		if ($amount <= 0 || $amount >= 80) {           
            throw new ApiException("Sorry you can't process this amount value. Try with less amount below ".utf8_encode('£')."80" , ErrorCodeType::NoResultFound);
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
			$fields			=	"SQL_CALC_FOUND_ROWS ord.id as OrderId,ord.OrderDoneBy,ord.OrderDate as OrderDate";
			$joincondition	= 	"left join users u on (ord.fkUsersId = u.id)";
			$condition		= 	"ord.OrderStatus = 0 and u.Status = 1";
			$limit			=	"";
		} else {
			$fields			= 	"SQL_CALC_FOUND_ROWS u.UniqueId as UserId,u.FirstName,u.LastName,u.Email,u.Photo,ord.id as OrderId,ord.fkCartId as CartId,ord.TotalItems,ord.TotalPrice,ord.VAT,ord.SubTotal,ord.TransactionId,ord.OrderStatus,ord.OrderDate as OrderDate,ord.OrderDoneBy";
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
			$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
			$total 			= 	(integer)$totalRec[0]['count'];
			$listedCount	= 	count($result);
				
			 if($type == 0) {
				/**
				* The result were found
				*/	
				$twoHour	=	$otherHour = 0;
				$TwoHoursAgo 	= 	strtotime("-2 hours");
				foreach($result as $key=>$value) {
					$orderdate 		= 	strtotime($value['OrderDate']);
					if ($orderdate >= $TwoHoursAgo)
						$twoHour++;// less than 2 hours ago
					else
						$otherHour++;// more than 2 hours ago
				}
				$result['total'] 		= count($result);
				$result['twoHour'] 		= $twoHour;
				$result['otherHour'] 	= $otherHour;
				return $result;				
			}
			else {
				
				/**
				* The result were found
				*/	
				foreach($result as $key=>$value) {
					if(!empty($value['Photo'])){
						$result[$key]['Photo'] 		= 	USER_IMAGE_PATH.$value['Photo'];
						$result[$key]['ThumbPhoto'] = 	USER_THUMB_IMAGE_PATH.$value['Photo'];
					}
					else{
						$result[$key]['Photo']		= 	$result[$key]['Photo'] = ADMIN_IMAGE_PATH_OTHER."no_user.jpeg";
						$result[$key]['ThumbPhoto']	= 	$result[$key]['ThumbPhoto'] = ADMIN_IMAGE_PATH_OTHER."no_user.jpeg";
					}
					$fields1			= 	"c.fkProductsId,p.ItemName,c.ProductsQuantity,c.ProductsCost,c.DiscountPrice,c.TotalPrice,c.Refund";
					$joincondition1		= 	"left join products p on (c.fkProductsId = p.id)";
					$sql1 				= 	"SELECT ".$fields1." from carts as c ".$joincondition1." where c.CartId='".$value['CartId']."'";
					$result1 			= 	R::getAll($sql1);
					$newProdRes =	array();
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
		if(isset($bean->OrderDoneBy))
			$OrderDoneBy	=	$bean->OrderDoneBy;
		if(isset($bean->Price))
			$Price	=	(integer)$bean->Price;			
			
		$fields			= 	"u.id as PUserId,u.UniqueId as UserId,u.FirstName,u.LastName,u.Email,u.Photo,ord.id as OrderId,ord.fkCartId as CartId,ord.TotalItems,ord.TotalPrice,ord.SubTotal,ord.VAT,ord.TransactionId,ord.RefundStatus,ord.OrderStatus,ord.OrderDate as OrderDate,ord.OrderDoneBy";
		$joincondition	= 	"left join users u on (ord.fkUsersId = u.id)";
				
		if($Type == 0) {			
			$condition	= 	"ord.OrderStatus in (0,1,2)";
		} else if($Type == 2) {			
			$condition	= 	"ord.OrderStatus in (1) and ord.fkMerchantsId = ".$merchantId;
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
		if(isset($OrderDoneBy) && $OrderDoneBy != '') {
			$condition .= " AND ord.OrderDoneBy ='".$OrderDoneBy."'";
		}
		if(isset($Price) && $Price != '') {
			$condition .= " AND ord.TotalPrice >=".$Price."";
		}	
		if($userId != '')
			$condition	.= 	" and u.id = ".$userId."  order by ord.OrderDate desc limit $start,$limit";
		else if($Type == 0)
			$condition	.= 	" order by ord.id desc limit $start,$limit";
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS ".$fields." from orders as ord  ".$joincondition." where u.Status = 1 and ord.Status=1 and ord.TransactionId != '' and ord.fkMerchantsId = ".$merchantId." and ".$condition." ";
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
					$result[$key]['Photo']		= 	$result[$key]['Photo'] = ADMIN_IMAGE_PATH_OTHER."no_user.jpeg";
					$result[$key]['ThumbPhoto']	= 	$result[$key]['ThumbPhoto'] = ADMIN_IMAGE_PATH_OTHER."no_user.jpeg";
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
	* getTransactionSummary
	*/
	public function getTransactionSummary($merchantId)
    {
		$bean 		= 	$this->bean;
		
		//Parameters
		$transactionSummary		=	$orderList =	Array();		
		$limit 			= 	10;	
		$start			=	0;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');		
		
		if(isset($bean->Start))				$start 			= 	$bean->Start;
		if(isset($bean->FromDate))			$fromDate		=	$bean->FromDate;
		if(isset($bean->ToDate))			$toDate			=	$bean->ToDate;			
		if(isset($bean->DataType))			$dataType 		= 	$bean->DataType;
		if(isset($bean->OrderStatus))		{
			if($bean->OrderStatus !='' && ($bean->OrderStatus =='0' || $bean->OrderStatus =='1'))
				$OrderStatus 	= 	" and ord.OrderStatus in (".$bean->OrderStatus.") ";
			else if($bean->OrderStatus !='' && ($bean->OrderStatus =='2'))
				$OrderStatus 	= 	" and ord.OrderStatus in (".$bean->OrderStatus.") and  ord.RefundStatus != 2 ";
			else if($bean->OrderStatus !='' && $bean->OrderStatus =='3')
				$OrderStatus 	= 	" and ord.RefundStatus = 2 ";
			else
				$OrderStatus	=	"";
		}
			
		//Search condition
		if(isset($OrderStatus) && $OrderStatus !='')
			$condition	= 	" and ord.fkMerchantsId = ".$merchantId." and ord.Status = 1 ".$OrderStatus;
		else
			$condition	= 	" and ord.fkMerchantsId = ".$merchantId." and ord.Status = 1 and ord.OrderStatus in (0,1,2) ";
		
		$filtercondition	=	'';
		if(isset($dataType) && !empty($dataType)) {
			if($dataType=='year') {
				$filtercondition 	.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
			} else if($dataType=='month') {
				$filtercondition 	.= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." ";
			} else if($dataType=='day') {
				$filtercondition 	.= 	" and  DATE_FORMAT( OrderDate, '%Y-%m-%d' ) ='".date('Y-m-d',strtotime($curr_date))."'";
			} else if($dataType=='7days') {
				$filtercondition 	.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
			}
		} else {		
			if(isset($fromDate) && $fromDate != ''	&&	isset($toDate) && $toDate != '')
				$filtercondition 	.= " AND ((OrderDate >=  '".date('Y-m-d H:i:s',$fromDate)."' and OrderDate <= '".date('Y-m-d H:i:s',$toDate)."') ) ";
			else if(isset($fromDate) && $fromDate != '')
				$filtercondition 	.= " AND OrderDate >=  '".date('Y-m-d H:i:s',$fromDate)."'";
			else if(isset($toDate) && $toDate != '')
				$filtercondition 	.= " AND OrderDate <=  '".date('Y-m-d H:i:s',$toDate)."'";		
		}
		
		if(isset($bean->LimitType) && !empty($bean->LimitType) && $bean->LimitType == 1) {
			$LimitType	=	'';
		} else {
			$LimitType	=	" limit $start,$limit ";
		}
		$condition		.=	$filtercondition;
		$sql 			= 	"SELECT SQL_CALC_FOUND_ROWS u.id as UserId,u.FirstName,u.LastName,u.Photo,ord.id as OrderId,ord.TotalPrice,ord.RefundStatus,ord.TransactionId,ord.OrderStatus,ord.OrderDate as OrderDate,ord.OrderDoneBy,ord.Commision from orders as ord
								left join users u on (ord.fkUsersId = u.id)
								where u.Status = 1 and ord.Status = 1  ".$condition." order by ord.OrderDate desc ".$LimitType;
   		//echo $sql;
		$orderList 		= 	R::getAll($sql);
		$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= 	(integer)$totalRec[0]['count'];
		$listedCount	= 	count($orderList);
		if($orderList){			
			foreach($orderList as $key=>$value) {
				if(!empty($value['Photo'])){
					$orderList[$key]['Photo'] 		= 	USER_IMAGE_PATH.$value['Photo'];
					$orderList[$key]['ThumbPhoto'] = 	USER_THUMB_IMAGE_PATH.$value['Photo'];
				}						
			}				
			
			if(isset($OrderStatus) && !empty($OrderStatus))
				$filtercondition	.=	$OrderStatus;
			else
				$filtercondition	.=	"  and OrderStatus in (1,2) ";
			
			$sql 	= 	"SELECT count(ord.id) as TotalTransaction,sum(ord.TotalPrice) as TotalAmount,ord.OrderStatus,ord.RefundStatus FROM orders as ord
						WHERE 1 and ord.fkMerchantsId=".$merchantId." and ord.TransactionId != '' ".$filtercondition." group by OrderStatus";
			//echo $sql;
			$transaction = 	R::getAll($sql);
			if(count($transaction) > 0) {
				$transactionSummary['transactions']	=	0;
				$transactionSummary['sales']		=	0;
				$transactionSummary['refunds']		=	0;
				$transactionSummary['refunded']		=	0;
				foreach($transaction as $value) {
					if($value['OrderStatus'] == 1){
						$transactionSummary['transactions']	= $value['TotalTransaction'];
						$transactionSummary['sales']		= $value['TotalAmount'];
					} else if($value['OrderStatus'] == 2 && $value['RefundStatus'] == 2){
						$transactionSummary['refunds']	= $value['TotalTransaction'];
						$transactionSummary['refunded']		= $value['TotalAmount'];
					}							
				}
			}			
			
			$outArray['list'] 			=	$orderList;
			$outArray['summary'] 		=	$transactionSummary;			
			$outArray['totalCount']		= 	$total;
			$outArray['listedCount']	= 	$listedCount;
			return $outArray;
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
		$trans			=	$transId	=	$re_status	=	$commisions = '';
		$userType		=	$bean->userType;
		
		//validate
		$this->validateModifyParams();
		
		//validate Order exists or not
		$orderdetails 	=	$this->validateCreate();
		
		unset($bean->userType);
		unset($bean->userId);
		unset($bean->MerchantId);
		
		if($orderdetails) {
			$admin		= R::findOne('admins');
			$adminFees	= $admin->MangoPayFees;
			if(!empty($userid) && $bean->OrderStatus == 1) {				
				//doing payment process if user accepts the order done by merchant
				$bean->UserId		=	$orderdetails->fkUsersId;
				$bean->MerchantId	=	$orderdetails->fkMerchantsId;
				$bean->Amount		=	$orderdetails->TotalPrice;
				$bean->Currency		=	DEFAULT_CURRENCY;
				$paymentDetails 	= 	$this->doPayment();
				if($paymentDetails && isset($paymentDetails->Id) && !empty($paymentDetails->Id) && isset($paymentDetails->Status) && $paymentDetails->Status == 'SUCCEEDED') {
					$commision		= 	$bean->Amount*($adminFees/100);
					$transId		= 	$paymentDetails->Id;
					$payment		=	$paymentDetails;
					$re_status		=	'1';
					$sql1 			=	"update carts set  Refund = '1' where CartId='".$orderdetails->fkCartId."'";
					R::exec($sql1);
				} else {
					if(isset($paymentDetails->ResultMessage) && !empty($paymentDetails->ResultMessage)) 
						throw new ApiException($paymentDetails->ResultMessage, ErrorCodeType::CheckBalanceError);
					else
						throw new ApiException("Error occurred during payment", ErrorCodeType::CheckBalanceError);
				}
				
				
			} else if($bean->OrderStatus == 2 && $orderdetails->OrderDoneBy == 1) {	

				//Refunding the amount when merchant rejecting order if order done by user
				$bean->OrderId		=	$bean->OrderId;
				$bean->MerchantId	=	$orderdetails->fkMerchantsId;			
				$refundDetails 		= 	$this->getRefundDetails();
				if($refundDetails && isset($refundDetails->Id) && !empty($refundDetails->Id)  && isset($refundDetails->Status) && $refundDetails->Status == 'SUCCEEDED') {
					$payment		=	$refundDetails;
					$re_status		=	'2';
					$sql1 			=	"update carts set  Refund = '2' where CartId='".$orderdetails->fkCartId."'";
					R::exec($sql1);
				} else {
					if(isset($refundDetails->ResultMessage) && !empty($refundDetails->ResultMessage)) 
						throw new ApiException($refundDetails->ResultMessage, ErrorCodeType::CheckBalanceError);
					else
						throw new ApiException("Error occurred during refunding old order", ErrorCodeType::CheckBalanceError);
				}	
			} 
			if(isset($commision) && !empty($commision)) 
				$commisions	=	", Commision ='".$commision."' ";
			if(isset($transId) && !empty($transId)) 
				$trans	=	", TransactionId ='".$transId."' ";
			if(!empty($re_status)) {
				if($re_status == '1')
					$re_status	=	" , OrderStatus='1', RefundStatus = 1 ";
				//$re_status	=	" , OrderStatus='1' ";
				if($re_status == '2')
					$re_status	=	" , OrderStatus='2', RefundStatus = 2, Message='Order Rejected by Merchant' ";
					//$re_status	=	" , OrderStatus='2', Message='Order Rejected by Merchant' ";
				}
			
			// updating the orders
			$sql 		=	"update orders set  OrderStatus =".$bean->OrderStatus.", OrderDate='".date('Y-m-d H:i:s')."' ".$commisions." ".$trans." ".$re_status." where id=".$bean->OrderId;
			R::exec($sql);
			
			//push notification while approve/reject an order							
			$users 	= 	R::find("users", " id = ? and Status=1",[$orderdetails->fkUsersId]);	
			if($users && $users[$orderdetails->fkUsersId]->PushNotification == 1 && $users[$orderdetails->fkUsersId]->BuySomething == 1) {
				if($orderdetails->OrderDoneBy == 1 || ($orderdetails->OrderDoneBy == 2 && $bean->OrderStatus == 2 && $userType == 'merchant')) {
					$notification 						= 	R::dispense('notifications');
					$notification->orderId 				= 	$bean->OrderId;
					$notification->userId 				= 	$orderdetails->fkUsersId;
					$notification->merchantId 			= 	$orderdetails->fkMerchantsId;
					$notification->orderAmount 			= 	$orderdetails->TotalPrice;
					if($bean->OrderStatus == 1)
						$notification->sendNotification(3);// if Approved
					else if($bean->OrderStatus == 2)
						$notification->sendNotification(4);// if Rejected
				}
			}
			
			if(isset($payment) && !empty($payment))
				return $payment;
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
		/**
		* Get the bean
		* @var $bean Orders
		*/
        $bean 			= 	$this->bean;
		
		if(isset($bean->Type) && $bean->Type == 1)
			$condition	=	" AND ord.TransactionId = '".$orderId."'";
		else
			$condition	=	" AND ord.id = '".$orderId."'";
		
		$sql			=	"SELECT ord.id as OrderId, ord.fkCartId AS CartId, ord.fkUsersId AS UserId,m.Street,m.City,m.PostCode,m.State,m.Country, ord.fkMerchantsId AS MerchantId, ord.TotalPrice,ord.VAT,ord.SubTotal, ord.RefundStatus,ord.TransactionId, m.CompanyName, m.Location,u.Photo,u.FirstName,u.LastName,u.Email,ord.OrderDate,ord.OrderDoneBy,u.UniqueId FROM `orders` AS ord
								LEFT JOIN merchants AS m ON ( ord.fkMerchantsId = m.id )
								LEFT JOIN users AS u ON ( u.id = ord.fkUsersId )
								WHERE 1 ".$condition;
		$result 		= 	R::getAll($sql);
		if($result) {
			if(!empty($result[0]['Photo'])){
				$result[0]['Photo'] = USER_THUMB_IMAGE_PATH.$result[0]['Photo'];
			}
			else{
				$result[0]['Photo']=  ADMIN_IMAGE_PATH_OTHER."no_user.jpeg";
			}			
			
			$result[0]['Address'] = '';
			if(!empty($result[0]['Street']))
				$result[0]['Address']	.=	$result[0]['Street'];
			if(!empty($result[0]['City']))
				$result[0]['Address']	.=	', '.$result[0]['City'];
			
			if(!empty($result[0]['State']) && !empty($result[0]['PostCode']))
				$result[0]['Address']	.=	', '.$result[0]['State'].' - '.$result[0]['PostCode'];
			else if(!empty($result[0]['State']) && empty($result[0]['PostCode']))
				$result[0]['Address']	.=	', '.$result[0]['State'];
			else if(empty($result[0]['State']) && !empty($result[0]['PostCode']))
				$result[0]['Address']	.=	', '.$result[0]['PostCode'];
			
			if(!empty($result[0]['Country']))
				$result[0]['Address']	.=	', '.$result[0]['Country'];
			unset($result[0]['Street']);
			unset($result[0]['City']);
			unset($result[0]['PostCode']);
			unset($result[0]['State']);
			unset($result[0]['Country']);
			
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
		$condition 		= 	$condition2	= 	$field = $time_zone = $check = '';
		$pre_total		=	$cur_total	=	$pre_order		=	$cur_order	=	$difference 	=	$currentPercent 	=	0;		
		$CurrentListArray	=	$OutputArray	=	$TransactionSummary = Array();
		$bean 			= 	$this->bean;
		$this->validateMerchants($merchantId,1);
		if($bean->TimeZone)
			$time_zone 	= 	$bean->TimeZone;
		$dataType 		= 	$bean->DataType;
		
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if(isset($bean->StartDate) && !empty($bean->StartDate)) {
			$field 			= 		" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) AS hour";
			$condition 		.= 		" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($bean->StartDate))."' group by hour";
			$condition2 	.= 		" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime('-1 day',strtotime($bean->StartDate)))."'";
		} else {
			if($dataType=='year') {
				$field 			= 		" , MONTH(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) AS month";
				$condition 		.=	 	"  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by month";
				$condition2		.=	 	"  and DATE_FORMAT(OrderDate,'%Y') = ".($cur_year - 1)."";
			} else if($dataType=='month') {
				$field 			= 		" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE), '%m/%d/%Y') AS day";
				$condition 		.= 		" and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by day";
				$condition2 	.= 		" and DATE_FORMAT(OrderDate,'%m') = ".($cur_month - 1)." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
			} else if($dataType=='day') {
				$field 			= 		" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) AS hour";
				$condition 		.= 		" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
				$condition2 	.= 		" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime('-1 day',strtotime($curr_date)))."'";
			}  else if($dataType=='7days') {
				$field 			 = 		" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE), '%m/%d/%Y') AS day";
				$condition 		.= 		" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  group by day";
				$condition2		.= 		" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime('-7 day',strtotime($curr_date)))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-14 days"))."') ";
			}
		}
		$sql 			= 	"SELECT  count(id) as TotalOrders,SUM(TotalPrice) as TotalPrice,SUM(VAT) as TotalVAT ".$field." from orders as o 
							where 1 and  o.OrderStatus IN (1) and o.Status = 1 and o.TransactionId != '' and o.fkMerchantsId = ".$merchantId."  ".$condition."";	
		//echo $sql;
		$CurrentList 	=	R::getAll($sql);
		if($CurrentList ){
			$totvat	=	0;
			foreach($CurrentList as $key=>$value){
				$totalOrders				=	$value["TotalOrders"];
				$totalPrice					=	$value["TotalPrice"];
				$averageTransaction			=	$totalPrice/$totalOrders;
				$cur_total					=	$cur_total + $totalPrice;
				$cur_order					=	$cur_order + $totalOrders;
				$totvat						=	$totvat + $value["TotalVAT"];
				$value["TotalPrice"]		=	number_format((float)$totalPrice, 2, '.', '');
				$value["Average"]			=	number_format((float)$averageTransaction, 2, '.', '');
				$CurrentListArray[] 		= 	$value;
			}

			$sql 			= 	"SELECT  SUM(c.TotalPrice) as TotalPrice,SUM(c.ProductsCost* c.ProductsQuantity) as ProductPrice  ".$field." from carts as c 
								left join orders as o on(c.CartId = o.fkCartId) 
								where 1 and  o.OrderStatus IN (1) and o.Status = 1  and o.TransactionId != '' and o.fkMerchantsId = ".$merchantId." and c.fkMerchantsId = ".$merchantId." ".$condition."";	
			//echo $sql;
			$Discounts	 	=	R::getAll($sql);
			$ProductAmount = $DiscountedAmount	= $TotalAmount	= $subTotal = 0 ;
			if($Discounts ){
				foreach($Discounts as $d_key=>$d_value){
					$ProductPrice			=	$d_value["ProductPrice"];
					$TotalPrice				=	$d_value["TotalPrice"];
					$ProductAmount			+=  $ProductPrice;
					$TotalAmount			+=  $TotalPrice;
				}
				$DiscountedAmount	=	$ProductAmount - $TotalAmount;
				$TransactionSummary['GrossTotal'] 	= round($ProductAmount,2);
				$TransactionSummary['SubTotal'] 	= round($TotalAmount,2);
				$TransactionSummary['Discount'] 	= round($DiscountedAmount,2);
				$TransactionSummary['Vat'] 			= round($totvat,2);
			}
			
			$sql 			= 	"SELECT  count(id) as TotalOrders,SUM(TotalPrice) as TotalPrice ".$field." from orders as o 
							where 1 and  o.OrderStatus IN (1) and o.Status = 1 and o.TransactionId != '' and o.fkMerchantsId = ".$merchantId."  ".$condition2."";	
			$PreviousList 	=	R::getAll($sql);
			//echo $sql;
			if($PreviousList && isset($PreviousList[0]['TotalOrders']) > 0){
				$pre_total		=	$PreviousList[0]['TotalPrice'];				
				$pre_order		=	$PreviousList[0]['TotalOrders'];
			}
			
			//Summary process - Start			
			
			//AmountProcess
			$TransactionSummary['Amount'] 		= 	round($cur_total,2);
			$difference		=	$currentPercent	=	0;
			$difference		=	$cur_total - $pre_total;
			//echo "====>".$cur_total;
			//echo "====>".$pre_total;
			
			if($cur_total > $pre_total && $cur_total > 0)
				$currentPercent	=	(abs($difference)/$cur_total) * 100;	
			else if($pre_total > 0)
				$currentPercent	=	(abs($difference)/$pre_total) * 100;
			/*if($pre_total > 0) {
				if($cur_total > $pre_total)
					$currentPercent	=	(($cur_total - $pre_total)/$pre_total) * 100;	
				else 
					$currentPercent	=	(($pre_total - $cur_total)/$pre_total) * 100;
			}*/
			$TransactionSummary['AmountPercentage'] 	= 	round($currentPercent,2);
			$TransactionSummary['AmountDifference'] 	= 	round($difference,2);
			
			
			//TransactionProcess
			$TransactionSummary['Transaction'] 	= 	$cur_order;
			$difference		=	$currentPercent	=	0;
			$difference		=	$cur_order - $pre_order;
			if($cur_order > $pre_order && $cur_order > 0)
				$currentPercent	=	(abs($difference)/$cur_order) * 100;	
			else if($pre_order > 0)				
				$currentPercent	=	(abs($difference)/$pre_order) * 100;	
			$TransactionSummary['TransactionPercentage'] 	= 	round($currentPercent,2);
			$TransactionSummary['TransactionDifference'] 	= 	round($difference,2);
			
			
			//AverageAmountProcess
			$TransactionSummary['Average'] 		= 	round(($cur_total/$cur_order),2);
			$difference		=	$currentPercent	=	$currentAvg = $perviousAvg = 0;			
			if($cur_order > 0)
				$currentAvg = $cur_total/$cur_order;
			if($pre_order > 0)
				$perviousAvg = $pre_total/$pre_order;			
			$difference		=	$currentAvg - $perviousAvg;
			if($currentAvg > $perviousAvg && $currentAvg > 0)
				$currentPercent	=	(abs($difference)/$currentAvg) * 100;	
			else if($perviousAvg > 0)
				$currentPercent	=	(abs($difference)/$perviousAvg) * 100;	
			$TransactionSummary['AveragePercentage'] 	= 	round($currentPercent,2);
			$TransactionSummary['AverageDifference'] 	= 	round($difference,2);
			
			$OutputArray['Summary']			=	$TransactionSummary;
			$OutputArray['CurrentList']		=	$CurrentListArray;	
			
			return $OutputArray;
		} else {
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
			
		//validate amount
			$this->validateAmount($bean->Amount);
		
			$user									=	$userInfo['fromuser'];
			$merchant								=	$userInfo['touser'];
			$admin									=	$userInfo['admin'];
			
			$logStart				=	microtime(true);
			$walletDetails			=	getWalletDetails($user->WalletId);
			
			//MangoPay Log
			$logArray				=	Array();	
			$logArray['UserId']		=	$user->id;
			$logArray['MerchantId']	=	$merchant->id;
			$logArray['URL']		=	'getWalletDetails';
			$logArray['Content']	=	Array('WalletId'=>$user->WalletId);
			$logArray['Start']		=	$logStart;
			$logArray['End']		=	microtime(true);
			$logArray['Response']	=	$walletDetails;
			
			$log 	=	R::dispense('users');
			$log->storeMangoPayLog($logArray);
			
			if($walletDetails) {
				if(isset($walletDetails->Balance->Amount) && ($walletDetails->Balance->Amount >= getCents($bean->Amount))) {
					$userDetails['AuthorId']			=	$user->MangoPayUniqueId;
					$userDetails['CreditedUserId']		=	$merchant->MangoPayUniqueId;
					$userDetails['Currency']			=	$bean->Currency;
					$userDetails['Amount']				=	$bean->Amount;
					$userDetails['DebitedWalletId']		=	$user->WalletId;
					$userDetails['CreditedWalletId']	=	$merchant->WalletId;
					$userDetails['FeesAmount']			=	$admin->MangoPayFees;
					$logStart							=	microtime(true);
					
					$logStart							=	microtime(true);
					$result								=	payment($userDetails);
					//MangoPay Log
					$logArray				=	Array();	
					$logArray['UserId']		=	$user->id;
					$logArray['MerchantId']	=	$merchant->id;
					$logArray['URL']		=	'payment';
					$logArray['Content']	=	$userDetails;
					$logArray['Start']		=	$logStart;
					$logArray['End']		=	microtime(true);
					$logArray['Response']	=	$result;
					$log 	=	R::dispense('users');
					$log->storeMangoPayLog($logArray);
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
		$admin		= 	R::findOne('admins');
		//$adminFees	= $admin->MangoPayFees;
		/*//echo "===>".$bean->Amount;
		//echo "====>".$adminFees;
		if($bean->Amount < $adminFees){
			throw new ApiException("The amount should be greater than fees.", ErrorCodeType::PaymentAccountError);
		}		*/
		
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
	
	/**
    * Refunding order amount
    */
    public function getRefundDetails($type = '')
    {
		$bean 			= 	$this->bean;
	  	
		if(isset($bean->Type) && !empty($bean->Type))
			$type	=	$bean->Type;
		
		//Validate refund params 
		$this->validateRefundParams();
	  	
		//Validate refund  
		$orders		=	$this->validateRefund();
		if($orders) {
			
			//user Details
			$users 	= 	R::findOne('users', 'id = ?', array($orders['fkUsersId']));	
			$admin	= 	R::findOne('admins');
			$details['FeeAmount']		=	$admin->MangoPayFees;
			$details['TransferID']		=	$orders['TransactionId'];	
			$details['CartId']			=	$orders['fkCartId'];	
			$details['AuthorId']		=	$users['MangoPayUniqueId'];		
			$details['Currency']		=	DEFAULT_CURRENCY;		
				
			if(isset($type) && $type == 2  && isset($bean->ProductId) && !empty($bean->ProductId)) {
				//cart Details
				$cart 	= 	R::findOne('carts', 'fkProductsId = ? and CartId = ?', array($bean->ProductId,$orders['fkCartId']));
				if($cart)
					$details['Amount']		=	$cart['TotalPrice'];
				else {
					//throwing error when error in refund
					throw new ApiException("Product not found in cart", ErrorCodeType::NoResultFound);
				}
			} else {
				$details['Amount']			=	$orders['TotalPrice'];			
			}
			
			$logStart	=	microtime(true);
			$result		=	refundTransfer($details);
			//MangoPay Log
			$logArray				=	Array();	
			$logArray['UserId']		=	$users['UsersId'];
			$logArray['MerchantId']	=	$bean->MerchantId;
			$logArray['URL']		=	'refundTransfer';
			$logArray['Content']	=	$details;
			$logArray['Start']		=	$logStart;
			$logArray['End']		=	microtime(true);
			$logArray['Response']	=	$result;
			$log 	=	R::dispense('users');
			$log->storeMangoPayLog($logArray);
			
			if($result && isset($result->Id) && !empty($result->Id)) {
				if($result->Status == 'FAILED'){
					// Error occurred during payment/transaction
					throw new ApiException($result->ResultMessage, ErrorCodeType::CheckBalanceError);
				}
				else{
					if($type == '')
						return $result;	
					else if($type == 1 || $type == 2){
						$productcon	=	'';
						if(isset($bean->ProductId) && !empty($bean->ProductId)) 
							$productcon	=	" and fkProductsId = '".$bean->ProductId."'";
							
						//updating cart table
						if(!empty($orders['fkCartId'])) {
							$sql1 			=	"update carts set  Refund = '2' where CartId='".$orders['fkCartId']."' ".$productcon;
							R::exec($sql1);
						//$cartOrder 	= 	R::findOne('carts', 'CartId = ? and Refund=2', array($orders['fkCartId']));
						//if(is_array($cartOrder) && count($cartOrder) == $orders['TotalItems']) {
							//order table
							$msg	=	'';
							if(isset($bean->msg) && !empty($bean->msg))
								$msg	=	$bean->msg;
							$sql2 			=	"update orders set OrderStatus='2', RefundStatus = '2', Message='".$msg."' where fkCartId='".$orders['fkCartId']."'";
							R::exec($sql2);
							
							$notification 						= 	R::dispense('notifications');
							$notification->orderId 				= 	'Refund';
							$notification->userId 				= 	$orders['fkUsersId'];
							$notification->merchantId 			= 	$bean->MerchantId;
							$notification->orderAmount 			= 	$orders['TotalPrice'];
							$notification->sendNotification(4);// if refunded
							
						//}
						}
						return $result;	
					}
				}
			} else {
				// Error occurred during payment/transaction
				throw new ApiException("Error occurred during refunding old order", ErrorCodeType::CheckBalanceError);
			}			
		}
	}
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validateRefundParams()
    {
		$bean 	= $this->bean;
		$rules 	= [
					'required' => [
						 ['OrderId'],['MerchantId']
					]
				];
				
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the refund properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the refund
	*/
    public function validateRefund()
    {
		$bean 		= 	$this->bean;
		
		//Merchant details
		$merchant 	= 	R::findOne('merchants', 'id = ? and Status=1', array($bean->MerchantId));
		if(!$merchant) {
			// the User was not found
			throw new ApiException("The merchant is not in active state.", ErrorCodeType::UserNotInActiveStatus);
		} else {
			if($merchant->MangoPayUniqueId == '') {
				//Not connected with payment accounts
				throw new ApiException("The merchant is not connected with banking account.", ErrorCodeType::PaymentAccountError);
			}
		}
		
		//Order Details
		$orders 	= 	R::findOne('orders', 'id = ?', array($bean->OrderId));
		if(!$orders) {
			// the User was not found
			throw new ApiException("Order not found.", ErrorCodeType::NoResultFound);
		} else {
			if($orders->TransactionId == '') {
				//Not yet Transaction has done
				throw new ApiException("The User not yet done transaction to refund", ErrorCodeType::PaymentError);
			}
		}
		
		//Checking Order
		if($orders->fkMerchantsId != $bean->MerchantId) {
			//check for order merchantId and refunding merchantId
			throw new ApiException("You are not having access to refund", ErrorCodeType::NotAccessToDoProcess);
		}
		return	$orders;
    }
	
	/*
	* get Special Orders Count
	*/
	public function getSpecialOrdersCount($merchantId){
		
		/*$sql		=	"SELECT SUM(c.ProductsQuantity) as SpecialScold FROM `products` p 
							left join carts c on (p.id = c.fkProductsId)
							WHERE 1 and p.`fkMerchantsId` ='".$merchantId."' and p.`ItemType` = 3 and p.Status = 1";	*/
		$sql		=	"SELECT SUM(c.ProductsQuantity) as SpecialScold FROM `products` p 
						left join carts c on (p.id = c.fkProductsId)
						left join orders o on (c.CartId = o.fkCartId)
						WHERE 1 and p.`fkMerchantsId` ='".$merchantId."' and p.`ItemType` = 3 and p.Status = 1 and o.OrderStatus = 1";
		$result		=  	R::getAll($sql);
		if(isset($result[0]['SpecialScold']) && !empty($result[0]['SpecialScold']) && $result[0]['SpecialScold'] > 0)
			return	$result[0]['SpecialScold'];
		else
			return '0';
	}
	
	/**
    * get getTopsales List
    */
    public function getTopsales($merchantId)
    {
		$condition 		= $field = $time_zone = '';
		$TransactionArray = array();
		$bean 			= 	$this->bean;
		$this->validateMerchants($merchantId,1);
		if($bean->TimeZone)
		$time_zone 		= 	$bean->TimeZone;
		$dataType 		= 	$bean->DataType;
		$curr_date 		= 	date('d-m-Y');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($dataType=='day') {
			$field 		= 	" , HOUR(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) AS hour";
			$condition .= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' group by hour";
		}
		 else if($dataType=='7days') {
			$field 			 = 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition 		.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')  group by day";
		}else if($dataType=='month') {
			$field 		= 	" , DATE_FORMAT(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE), '%m/%d/%Y') AS day";
			$condition .= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by day";
		}
		else if($dataType=='year') {
			$field 		= 	" , MONTH(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) AS month";
			$condition 	.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." group by month";
		}
		$sql 				= 	"SELECT  SUM(o.TotalPrice) as TotalPrice,SUM(o.SubTotal) as SubTotal,SUM(o.VAT) as VATTotal ".$field." from orders as o 
								where 1 and  o.OrderStatus IN (1) and o.fkMerchantsId = ".$merchantId."  ".$condition."";	
		$TransactionList 	=	R::getAll($sql);
		$sql 				= 	"SELECT  SUM(c.TotalPrice) as TotalPrice,SUM(c.ProductsCost*c.ProductsQuantity) as ProductPrice  ".$field." from carts as c left join orders as o on(c.CartId = o.fkCartId) where 1 and  o.OrderStatus IN (1)  and o.fkMerchantsId = ".$merchantId." and c.fkMerchantsId = ".$merchantId." ".$condition."";	
		$Discounts	 		=	R::getAll($sql);
		//echo "<pre>"; print_r($Discounts); echo "</pre>";
		$DiscountAmount = $ProductAmount = $DiscountedAmount	= $TotalAmount	 = $grossSales = $subTotal = 0 ;
		if($Discounts ){
			foreach($Discounts as $d_key=>$d_value){
				$ProductPrice						=	$d_value["ProductPrice"];
				$TotalPrice							=	$d_value["TotalPrice"];
				$ProductAmount						+=  $ProductPrice;
				$TotalAmount						+=  $TotalPrice;
			}
			$DiscountedAmount						=	$ProductAmount - $TotalAmount;
			$subTotal								=	$TotalAmount ;
		}
		if($TransactionList ){
			$VATAmount =  0;
			foreach($TransactionList as $key=>$value){
				$VATTotal							=	$value["VATTotal"];
				$value["VATTotal"]					=	number_format((float)$VATTotal, 2, '.', '');
				$TotalOrderPrice					=	$value["TotalPrice"];
				$value["TotalPrice"]				=	number_format((float)$TotalOrderPrice, 2, '.', '');
				$TransactionListArray[] 			= 	$value;
				$VATAmount							+=  $VATTotal;
			}
			$overAllTotal							=	$VATAmount+$grossSales;
			$TransactionArray['GrossSale']        	=	number_format((float)$ProductAmount, 2, '.', '');
			$TransactionArray['Discount']    		=	number_format((float)$DiscountedAmount, 2, '.', '');
			$TransactionArray['SubTotal']        	=   number_format((float)$subTotal, 2, '.', '');
			$TransactionArray['VATTotal']           =  	number_format((float)$VATAmount, 2, '.', '');
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
    * get top products
    */
    public function getTopProducts($merchantId)
    {
		$condition 		=	$time_zone = '';
		$dataType		=	'day';
		$bean 			= 	$this->bean;
		$this->validateMerchants($merchantId,1);
		if(isset($bean->TimeZone))
		$time_zone 			= 	$bean->TimeZone;
		if(isset($bean->DataType))
		$dataType 			= 	$bean->DataType;
		$curr_date 			= 	date('d-m-Y');
		$cur_month 			= 	date('m');
		$cur_year 			= 	date('Y');
		$group_condition	= ' group by p.id';
		if($dataType=='year') {
			$condition 	.=	 "  and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year."";
		} else if($dataType=='month') {
			$condition .= 	"and DATE_FORMAT(OrderDate,'%m') = ".$cur_month." and DATE_FORMAT(OrderDate,'%Y') = ".$cur_year." ";
		} else if($dataType=='day') {
			$condition .= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
		} else if($dataType=='7days') {
			$condition 		.= 	"and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
		}
		$sql 			= 	"SELECT  p.ItemName as Name,SUM(c.TotalPrice) as TotalPrice from orders as o 
							left join carts as c ON (c.CartId = o.fkCartId)
                         	left join products as p on(p.id = c.fkProductsId)
							where 1   and  o.OrderStatus IN (1) and o.fkMerchantsId = ".$merchantId." and  c.fkMerchantsId = ".$merchantId." and p.fkMerchantsId =  ".$merchantId."  ".$condition." group by p.id order by TotalPrice desc limit 0,10 ";
		$productAnalysis 	=	R::getAll($sql);
		
		$pie_sql 			= 		"select (select sum( c.TotalPrice ) from `carts` AS c
											left join orders as o on (c.CartId = o.fkCartId)
											left join products as p on (p.id = c.fkProductsId)
											where c.`ProductsCost` > c.DiscountPrice  and o.OrderStatus IN (1) and c.fkMerchantsId = ".$merchantId."  ".$condition."  
											limit 0,1)  as specialProducts ,
											(select sum( c.TotalPrice ) from `carts` AS c
											left join orders as o on (c.CartId = o.fkCartId)
											left join products as p on (p.id = c.fkProductsId)
											where (c.`ProductsCost` = c.DiscountPrice) and o.OrderStatus IN (1)  and c.fkMerchantsId = ".$merchantId."  ".$condition." 
											limit 0,1)  as normalProducts
 											from `carts` where 1  limit 0,1" ;	//echo "================>".$pie_sql."</br>";//( (c.`ProductsCost` = c.DiscountPrice and p.ItemType IN(2,3)) 
	   $pieChartData 		=	R::getAll($pie_sql);
		if($productAnalysis ){
			foreach($productAnalysis as $key=>$value){
				$totalPrice							=	$value["TotalPrice"];
				$value["TotalPrice"]				=	$totalPrice;//number_format((float)$totalPrice, 2, '.', ',');
				$ProductListArray[] 				= 	$value;
			}
			$ProductArray['result']            		=  	$ProductListArray;
			if($pieChartData){
				/*foreach($pieChartData as $key=>$value){
					$normalPrice						=	$value["normalProducts"];
					$value["normalProducts"]			=	number_format((float)$normalPrice, 2, '.', ',');
					$specialPrice						=	$value["specialProducts"];
					$value["specialProducts"]			=	number_format((float)$specialPrice, 2, '.', ',');
					$PieChartArray[] 					= 	$value;
				}*/
				$ProductArray['pieChart']  				=	$pieChartData;
			}
			return $ProductArray;
		}
		else{
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
}