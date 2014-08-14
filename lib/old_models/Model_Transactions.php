<?php

/**
 * Description of Model_Transactions
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


class Model_Transactions extends RedBean_SimpleModel {

    /**
     * Constructor
     */
    public function __construct() {

    }
	
	/**
     * get TransactionsList
     */
    
	public function getTransactionsList($userId)
    {
		/**
         * Query to get TransactionsList 
         */
		$fields 	= "ord.id as OrderId,ord.fkCartId as CartId,ord.fkMerchantsId as MerchantID,m.FirstName,m.LastName,m.CompanyName,m.Location,m.Icon,ord.TotalItems,ord.TransactionId,ord.TotalPrice,ord.OrderDate";
		$join		= "left join merchants m on (ord.fkMerchantsId = m.id)";
		$sql 		= "SELECT ".$fields." from orders ord ".$join." where ord.fkUsersId = ".$userId." order by ord.id desc";
   		$result 	= R::getAll($sql);
		if($result){
			/**
             * The result were found
             */
			foreach($result as $key=>$value){
				//Image Path
				$image_path = '';				
				if(isset($value['Icon']) && $value['Icon'] != '')
					$image_path = MERCHANT_ICONS_IMAGE_PATH.$value['Icon'];					
				else
					$image_path = ADMIN_IMAGE_PATH.'no_image.jpeg';
				
				$result[$key]['Icon']	=	$image_path;
			 }
			return $result;
		}
		else{
			 /**
	         * throwing error when no data found
	         */
			  throw new ApiException("No Transactions Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
     * get Transactions Details
     */
    
	public function getTransactionsDetails($userId,$transactionId)
    {
		/**
         * Query to get Transactions Details
         */
		$fields 	= "ord.id as OrderId,ord.fkCartId as CartId,ord.fkMerchantsId as MerchantID,m.FirstName,m.LastName,m.CompanyName,m.Location,m.Icon,ord.TotalItems,ord.TotalPrice,ord.TransactionId,ord.OrderDate";
		$join		= "left join merchants m on (ord.fkMerchantsId = m.id)";
		$sql 		= "SELECT ".$fields." from orders ord ".$join." where ord.fkUsersId = ".$userId." and TransactionId='".$transactionId."'";
   		//echo $sql;
		$result 	= R::getAll($sql);
		if($result){
			/**
             * The result were found
             */
			foreach($result as $key=>$value){
				$fields1 	= "c.fkProductsId as ProductId,p.ItemName,c.ProductsQuantity,c.ProductsCost,c.DiscountPrice,c.TotalPrice";
				$join1		= "left join products p on(c.fkProductsId = p.id)";
				$sql1 		= "SELECT ".$fields1." from carts c ".$join1." where CartId='".$value['CartId']."'";
				$result1 	= R::getAll($sql1);
				
				//Image Path
				$image_path = '';				
				if(isset($value['Icon']) && $value['Icon'] != '')
					$image_path = MERCHANT_ICONS_IMAGE_PATH.$value['Icon'];					
				else
					$image_path = ADMIN_IMAGE_PATH.'no_image.jpeg';
				
				$result[$key]['Icon']	=	$image_path;
				$result[$key]['Items']	=	$result1;
			 }
			return $result;
		}
		else{
			 /**
	         * throwing error when no data found
	         */
			  throw new ApiException("No Transactions Found", ErrorCodeType::NoResultFound);
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
	                 ['TransactionId']
	            ]
	        ];
		}
				
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the TransactionId properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
}