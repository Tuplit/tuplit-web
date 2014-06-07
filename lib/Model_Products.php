<?php

/**
 * Description of Model_Products
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
    
	public function getProductDetail($productid)
    {
		/**
         * Query to get product detail
         */
		
		$sql 	= "SELECT id,ItemName,Price,fkCategoryId,Photo,DiscountApplied,Status from products where id='".$productid."'";
		//echo $sql;
   		
		$result = R::getAll($sql);
		if($result){			
			if(isset($result[0]['Photo']) && $result[0]['Photo'] != ''){
				$image_path = PRODUCT_IMAGE_PATH.$result[0]['Photo'];					
			} else if(isset($result['Photo']) && $result[0]['Photo'] == '') {
				$image_path = SITE_PATH.'/merchant/webresources/images/no_image.jpeg';
			}
			$result[0]['Price']	=	floatval($result[0]['Price']);
			$result[0]['Photo']	=	$image_path;
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
     * get Product list
     */
    
	public function getProductList($id,$app=0)
    {
		/**
         * Query to get products list
         */
		$fields 		= 'p.id as ProductId,p.ItemName,p.fkCategoryId,p.Photo,p.DiscountApplied,p.Price,pc.CategoryName,pc.fkMerchantId as CategoryMerchnatId,p.Status';
		$sql 			= 	"SELECT ".$fields." from products as p
							left join productcategories pc on (p.fkCategoryId = pc.id )
							where p.fkMerchantsId = $id and p.Photo!='' and p.Status in (1,2) and pc.Status=1 order by pc.id asc, p.id desc";
		//echo $sql;
   		
		$result 	= R::getAll($sql);
		if($result){			
			foreach($result as $key=>$value){
				$image_path = '';				
				if(isset($value['Photo']) && $value['Photo'] != ''){
					$image_path = PRODUCT_IMAGE_PATH.$value['Photo'];					
				} else if(isset($value['Photo']) && $value['Photo'] == '') {
					$image_path = MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
				}
				
				$result[$key]['Price']	=	floatval($value['Price']);
				$result[$key]['Photo']	=	$image_path;				
			}
			
			$productListArray = array();		
			foreach($result as $key=>$value){
				if($app == 0)
					$keyIndex = $value['fkCategoryId'];
				else
					$keyIndex = $value['CategoryName'];
				$productListArray[$keyIndex][] = $value;				
			}
			return $productListArray;
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
    public function create(){ // Tuplit new product
		 
		 /**
         * Get the bean
         * @var $bean Model_Product
         */
        $bean 		= $this->bean;	
		
		// validate the model	
		$this->validate();
		//validate product exists or not
		$this->validateModify();
		
		if($bean->Discount == '1')
			$bean->DiscountApplied 	= '1';			
		unset($bean->Discount);
		$bean->fkCategoryId	= $bean->CategoryId;
		unset($bean->CategoryId);
        $bean->DateCreated 	= date('Y-m-d H:i:s');
		$bean->DateModified = $bean->DateCreated;			
		$bean->Status = 1;
		
		// save the bean to the database
		$productId = R::store($this);		
	  	return $productId;
    }
	
	/**
     * @param Modify the Products	 
     */
    public function modify(){

		/**
         * Get the bean
         * @var $bean Model_Products
         */
		$bean = $this->bean;
		
		// validate the model	
		$this->validate();
		
		//validate product exists or not
		$this->validateModify();
		if($bean->CategoryId){
			$bean->fkCategoryId = $bean->CategoryId;
			unset($bean->CategoryId);
		}
        $bean->DateModified = date('Y-m-d H:i:s');
		unset($bean->ImageAlreadyExists);
        // modify the bean to the database
        R::store($this);
    }
	
	/**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
     */
    public function validate()
    {
		$bean = $this->bean;
		if($bean->ImageAlreadyExists == 1){
			$rules = [
				'required' => [
					 ['CategoryId'],['ItemName'],['Price'],['Status']
				]
			];
		}
		else{
			$rules = [
				'required' => [
					 ['Photo'],['CategoryId'],['ItemName'],['Price'],['Status']
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
	
	/**
     * Validate the model
     * @throws ApiException if the models fails to validate required fields
    public function validate324($type='')
    {
		$bean = $this->bean;
		if($type == ''){			
			$rules = [
	            'required' => [
	                 ['Photo'],['CategoryId'],['ItemName'],['Price'],['Discount'],['Status']
	            ]
	        ];
		}
				
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the Product properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }*/
	
	
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
			$modifiedBy = R::findOne('products', 'id = ?', [$bean->id]);
			if (!$modifiedBy) {
				// the Product was not found
				throw new ApiException("Product Id not found.", ErrorCodeType::NotAccessToDoProcess);
			}
		}
		
		if(isset($bean->ItemName)) {
			if(isset($bean->id) && !empty($bean->id) && $bean->id != 0) {		
				$modifiedName = R::findOne('products', 'ItemName = ? and id != ? ',array($bean->ItemName,$bean->id));
			}
			else {
				$modifiedName = R::findOne('products', 'ItemName = ?',array($bean->ItemName));
			}
			if ($modifiedName) {
				// the Product name was found
				throw new ApiException("Product name already exists.", ErrorCodeType::NotAccessToDoProcess);
			}	
		}
    }
}