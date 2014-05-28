<?php

/**
 * Description of Model_Product
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


class Model_Products extends RedBean_SimpleModel {

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
     * get Product details
     */
    
	public function getProductListForSpecial($id)
    {
		/**
         * Query to get special products list
         */
		$sql 		= "SELECT id,ItemName from products where fkMerchantsId='".$id."' and ItemType=1 and Status=1";
		//echo $sql;
   		$result 	= R::getAll($sql);
		if($result){			
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
     * Create new product
     */
    public function create($set){ // Tuplit new product
		 
		 /**
         * Get the bean
         * @var $bean Model_Product
         */
		 
        $bean 		= $this->bean;
		
		//echo'<pre>';print_r($bean);echo'</pre>';die();
		// validate the model	
		if($set == 1)
			$this->validate($bean->ItemType);
       
        $bean->DateCreated 			= date('Y-m-d H:i:s');
		if($set == 1)
			$bean->DateModified 		= $bean->DateCreated;		
		$bean->Status 				= 1;
		
		//echo'<pre>';print_r($bean);echo'</pre>';
		
		// save the bean to the database
		$productId = R::store($this);		
	  	return $productId;
    }
	
	
	
	/**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
     */
    public function validate($type='')
    {
		$bean = $this->bean;
		if($type == 2){
			$rules = [
	            'required' => [
	                ['fkMerchantsId'],['ItemName'],['ItemDescription'],['Price'],['ItemType'],['Quantity'],['DiscountPrice'],['Photo'],['DiscountApplied'],['DiscountTier']
	            ]
	        ];
		}
		else {
			$rules = [
	            'required' => [
	                 ['fkMerchantsId'],['ItemName'],['ItemDescription'],['Price'],['ItemType'],['Quantity'],['DiscountPrice'],['Photo']
	            ]
	        ];
		}
				
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the Product properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }

}