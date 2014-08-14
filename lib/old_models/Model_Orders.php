<?php

/**
 * Description of Model_Orders
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


class Model_Orders extends RedBean_SimpleModel {

    /**
     * Identifier
     * @var int
     */
    public $id;
	 /**
     * Identifier
     * @var int
     */
	 public $Status;

    /**
     * Constructor
     */
    public function __construct() {

    }
	
	 /**
     * Place an order
     */
    public function create($type,$userDetails){
		 /**
         * Get the bean
         */
        $bean 					= $this->bean;
		
		// validate the model parameters
        $this->validateCreateParams($type);
		
		if($userDetails->CurrentBalance < $bean->TotalPrice){
				//throws when No Balnce
				throw new ApiException("Low balance. User not having enough balance", ErrorCodeType::CheckBalanceError);
		}
		//Getting cart details		
		$input					= 	$bean->CartDetails;		
		$temp					= 	json_decode($input,1);
		if(!empty($temp)) {
			if(!is_array($temp))
				$CartDetails[0] = $temp;
			else
				$CartDetails	= $temp;
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
		
		$sql1 		= "SELECT * from products where id in (".$productIds.") and Status = 1";
   		$result1 	= R::getAll($sql1);
		if(count($result1) == $productcount) {
			//Storing carts
			foreach($CartDetails as $val) {	
				$carts						= R::dispense('carts');
				$carts->CartId				= $CartId;
				$carts->fkUsersId			= $bean->UserId;
				$carts->fkMerchantsId		= $bean->MerchantId;
				$carts->fkProductsId		= $val['ProductId'];
				$carts->ProductsQuantity	= $val['ProductsQuantity'];
				$carts->ProductsCost		= $val['ProductsCost'];
				$carts->DiscountPrice		= $val['DiscountPrice'];
				$carts->TotalPrice			= ($val['ProductsQuantity'] * $val['DiscountPrice']);
				$carts->PurchasedDate		= date('Y-m-d H:i:s');
				
				// save the carts to the database
				if($_SERVER['REMOTE_ADDR'] == '172.21.4.56'){  } else {
					R::store($carts);
				}
			}
			
			//Storing Order
			$transId						= 'TUPLIT-TRANS-'. time();
			$bean->fkCartId					= $CartId;
			$bean->fkUsersId				= $bean->UserId;		
			$bean->fkMerchantsId			= $bean->MerchantId;
			$bean->TransactionId			= $transId;
			$bean->OrderDate 				= date('Y-m-d H:i:s');
			$bean->OrderStatus 				= '0';        
			$bean->Status 					= '1';
			unset($bean->UserId);
			unset($bean->MerchantId);
			unset($bean->CartDetails);
			
			// save the bean to the database
			if($_SERVER['REMOTE_ADDR'] == '172.21.4.215'){ 
				$orderId					=	"13";
			} else {
				$orderId 		=    R::store($this);
				if($bean->OrderDoneBy == 1) {
					$sql			=	'update users set  CurrentBalance = CurrentBalance - '.$bean->TotalPrice.' where id='.$bean->fkUsersId;
					R::exec($sql);
					//TotalPrice
				}
			}
			$resultArray['orderId'] 		= $orderId;
			$resultArray['CartDetails'] 	= $CartDetails;
			$resultArray['CartId'] 			= $CartId;
			$resultArray['TransactionId'] 	= $transId;
			 
			return $resultArray;
		} else {
			 /**
	         * throwing error when product data found
	         */
			  throw new ApiException("One of the product in your cart is not in active status ", ErrorCodeType::NoResultFound);
		}
		die();
    }
	
	/**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
     */
    public function validateCreateParams($type='')
    {
		$bean = $this->bean;
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
    
	public function getNewOrderDetails($merchantId)
    {
		/**
         * Query to get NewOrders 
         */
		$sql 	= "SELECT ord.id as OrderId,u.FirstName,u.LastName, ord.TotalPrice,OrderDoneBy from orders ord 
					left join users u on (ord.fkUsersId = u.id)
					where ord.OrderStatus = 0 and ord.fkMerchantsId = ".$merchantId." and u.Status = 1 order by ord.id desc limit 0,2 ";
		//echo $sql;
   		$result 	= R::getAll($sql);
		if($result){
			/**
             * The result were found
             */
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
     * get Order List
     */
    
	public function getOrderList($merchantId)
    {
		/**
         * Query to get OrderList 
         */
		 $userId = '';
		 $bean = $this->bean;
		 if(isset($bean->UserId))
			$userId		=	$bean->UserId;
		if($bean->Start)
			$start = $bean->Start;
		else
			$start = 0;
		if($bean->Limit)
			$limit = $bean->Limit;
		else
			$limit = 10;
		$fields			= "u.UniqueId as UserId,u.FirstName,u.LastName,u.Email,u.Photo,ord.id as OrderId,ord.fkCartId as CartId,ord.TotalPrice,ord.TransactionId,ord.OrderStatus,ord.OrderDate as OrderDate,ord.OrderDoneBy";
		$joincondition	= "left join users u on (ord.fkUsersId = u.id)";
		$condition		= "ord.OrderStatus in (0,1,2)";
		if($userId != '')
		$condition		.= " and u.id = ".$userId."  order by ord.id desc limit $start,$limit";
		$sql 			= "SELECT SQL_CALC_FOUND_ROWS ".$fields." from orders as ord  ".$joincondition." where u.Status = 1  and ord.fkMerchantsId = ".$merchantId." and ".$condition." ";
   		//echo $sql;
		$result 		= 	R::getAll($sql);
		$totalRec 		= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 			= (integer)$totalRec[0]['count'];
		$listedCount	= count($result);
		if($result){
			/**
             * The result were found
             */	
			foreach($result as $key=>$value) {
				if(!empty($value['Photo'])){
					$result[$key]['Photo'] 		= USER_IMAGE_PATH.$value['Photo'];
					$result[$key]['ThumbPhoto'] = USER_THUMB_IMAGE_PATH.$value['Photo'];
				}
				else{
					$result[$key]['Photo']= $result[$key]['Photo'] = ADMIN_IMAGE_PATH."no_user.jpeg";
					$result[$key]['ThumbPhoto']= $result[$key]['ThumbPhoto'] = ADMIN_IMAGE_PATH."no_user.jpeg";
				}
				$fields1		= "c.fkProductsId,p.ItemName,c.ProductsQuantity,c.ProductsCost,c.DiscountPrice,c.TotalPrice";
				$joincondition1	= "left join products p on (c.fkProductsId = p.id)";
				$sql1 			= "SELECT ".$fields1." from carts as c ".$joincondition1." where c.CartId='".$value['CartId']."'";
				$result1 		= R::getAll($sql1);
				$result[$key]['Products'] = $result1;
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
    public function modify($userid, $merchnatid){

		/**
         * Get the bean
         * @var $bean Model_Orders
         */
		$bean 			= 	$this->bean;
		
		//validate
		$this->validateModifyParams();
		
		//validate Order exists or not
		$orderdetails 	=	$this->validateModify($userid, $merchnatid);
		
		if($orderdetails) {
			$bean->id 		= 	$bean->OrderId;
			unset($bean->OrderId);
			
			if(!empty($userid) && $bean->OrderStatus == 1) {
				$userdata = R::findOne('users', 'id = ?', [$userid]);
				if($userdata->CurrentBalance >= $orderdetails->TotalPrice) {
					$newamount		=	$userdata->CurrentBalance - $orderdetails->TotalPrice;
					$sql4			=	'update users set  CurrentBalance ='.$newamount.' where id='.$userid;
					R::exec($sql4);
				}else {
					throw new ApiException("Low balance. User not having enough balance", ErrorCodeType::CheckBalanceError);
				}
			}
			// modify the bean to the database
			$sql 		=	"update orders set  OrderStatus =".$bean->OrderStatus.", OrderDate='".date('Y-m-d H:i:s')."' where id=".$bean->id;
			R::exec($sql);				
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
	public function validateModify($userid, $merchnatid)
    {
       	/**
         * Get the bean
         * @var $bean Model_Orders
         */
        $bean = $this->bean;
		
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
         * @var $bean Model_Orders
         */
		$sql			=	"SELECT ord.id as OrderId, ord.fkCartId AS CartId, ord.fkUsersId AS UserId, ord.fkMerchantsId AS MerchantId, ord.TotalPrice,ord.TransactionId, m.CompanyName, m.Address, m.Location FROM `orders` AS ord
								LEFT JOIN merchants AS m ON ( ord.fkMerchantsId = m.id )
								WHERE 1 AND ord.id = '".$orderId."'";
		$result 		= 	R::getAll($sql);
		if($result) {
			$sql1		=	"SELECT  c.fkProductsId as ProductID, p.ItemName, c.ProductsQuantity, c.ProductsCost, c.DiscountPrice, c.TotalPrice  FROM carts c
								left join products p on (c.fkProductsId = p.id)
								where c.CartId='".$result[0]['CartId']."'";
			$result1 	= 	R::getAll($sql1);
			if($result1)
				$result[0]['Products']	= $result1;
			return	$result;
		}
		else {
			/**
	         * throwing error when no data found
	         */
			  throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
}