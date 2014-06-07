<?php

/**
 * Description of Model_Categories
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
     * get NewOrderCount details
     */
    
	public function getNewOrderDetails($merchantId)
    {
		/**
         * Query to get NewOrderCount 
         */
		$sql 		= "SELECT count(id) as NewOrderCount from orders where OrderStatus = 0 and fkMerchantsId = ".$merchantId." ";
   		$result 	= R::getAll($sql);
		if($result){
			/**
             * The result were found
             */
				
				//$NewOrderArray['result'] 		= $result;
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
    
	public function getOrderList($merchantId,$type)
    {
		/**
         * Query to get OrderList 
         */
		$fields			= "ord.fkUsersId as UserId,u.FirstName,u.LastName,u.Photo,ord.id as OrderId,ord.fkCartId as CartId,ord.TotalPrice,ord.OrderStatus,Date(ord.OrderDate) as OrderDate";
		$joincondition	= "left join users u on (ord.fkUsersId = u.id)";
		if($type == '1') 
			$condition	= "ord.OrderStatus in (0,1,2)";
		else
			$condition	= "ord.OrderStatus = 0 ";
		$sql 			= "SELECT ".$fields." from orders as ord  ".$joincondition." where ord.fkMerchantsId = ".$merchantId." and ".$condition;
   		//echo $sql;
		$result 		= R::getAll($sql);
		if($result){
			/**
             * The result were found
             */	
			 if($type == '1') {
				 foreach($result as $key=>$value) {
					if(!empty($value['Photo']))
						$result[$key]['Photo'] = USER_IMAGE_PATH.$value['Photo'];
					else
						$result[$key]['Photo'] = ADMIN_IMAGE_PATH."no_user.jpeg";
						
					$fields1		= "c.fkProductsId,p.ItemName,c.ProductsQuantity,c.ProductsCost,c.DiscountPrice";
					$joincondition1	= "left join products p on (c.fkProductsId = p.id)";
					$sql1 			= "SELECT ".$fields1." from carts as c ".$joincondition1." where c.fkCartId='".$value['CartId']."' limit 0,2";
					//echo $sql1;
					$result1 		= R::getAll($sql1);
					$result[$key]['Products'] = $result1;
				 }
			 }
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
    public function modify(){

		/**
         * Get the bean
         * @var $bean Model_Orders
         */
		$bean = $this->bean;
		
		//validate
		$this->validate();
		
		//validate Order exists or not
		$this->validateModify();
		
		$bean->id = $bean->OrderId;
		unset($bean->OrderId);
		
        // modify the bean to the database
        R::store($this);
    }
	
	/**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
     */
    public function validate($type='')
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
     * Validate the modification of Products
     * @throws ApiException if the product not exists in the database.
     */
	public function validateModify()
    {
       	/**
         * Get the bean
         * @var $bean Model_Products
         */
        $bean = $this->bean;
		
        /**
         * Get the identity of the product making the change         
         */
		if(isset($bean->id) && !empty($bean->id) && $bean->id != 0) {			
			$modifiedBy = R::findOne('orders', 'id = ?', [$bean->id]);
			if (!$modifiedBy) {
				// the Product was not found
				throw new ApiException("Order Id not found.", ErrorCodeType::NotAccessToDoProcess);
			}
		}	
    }
}