<?php

/**
 * Description of Categories
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


class Categories extends RedBean_SimpleModel implements ModelBaseInterface {

    /**
    * Constructor
    */
    public function __construct() {

    }

	/**
    * get category details
    */
	public function getCategoryDetails()
    {
		/**
        * Query to get category list
        */
		$sql 		= 	"SELECT SQL_CALC_FOUND_ROWS c.CategoryName,c.CategoryIcon,c.id as CategoryId,count(mc.fkMerchantId) as MerchantCount from categories as c
						LEFT JOIN merchantcategories as mc ON(c.id = mc.fkCategoriesId) where c.Status = 1 GROUP BY c.id ORDER BY c.CategoryName asc";
   		$result 	=	R::getAll($sql);
		$totalRec 	= 	R::getAll('SELECT FOUND_ROWS() as count ');
		$total 		= 	(integer)$totalRec[0]['count'];
		if($result){
			/**
            * The categories were found
            */
			foreach($result as $key=>$value){
				$image_path 	= 	'';
				if(isset($value['CategoryIcon']) && $value['CategoryIcon'] != ''){
					if (!SERVER){
						if(file_exists(CATEGORY_IMAGE_PATH_REL.$value['CategoryIcon'])){
							$image_path 	= 	CATEGORY_IMAGE_PATH.$value['CategoryIcon'];
						}
					}
					else{
						if(image_exists(3,$value['CategoryIcon']))
							$image_path 	= 	CATEGORY_IMAGE_PATH.$value['CategoryIcon'];
					}
				}
				$value['CategoryIcon']		=	$image_path;
				$value['CategoryName']		=	ucfirst($value['CategoryName']);
				$CategoryArray[]			=   $value;
			}
			$CategoryArray['result'] 		= 	$CategoryArray;
			$CategoryArray['totalCount']	= 	$total;
			return $CategoryArray;
		}
		else{
			/**
	        * throwing error when no data found
	        */
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/*
	* Delete Categories
	*/	
	public function deleteCategories($mercahntId,$catids)
    {
		/**
        * Query to delete merchant categories
        */
		$sql		= 	"delete from merchantcategories where fkCategoriesId not in (".$catids.") and fkMerchantId = '".$mercahntId."'";
   		$result 	= 	R::exec($sql);
	}
	
	/**
    * get product category details
    */
	public function getProdctCategoryList($merchantId)
    {
		/**
        * Query to get category list
        */
		 
		$sql 		= 	"SELECT c.id as CategoryId,c.CategoryName from productcategories as c where c.Status = 1 and fkMerchantId in (".$merchantId.",0) ORDER BY c.CategoryName asc";
   		$result 	= 	R::getAll($sql);
		if($result){
			/**
            * The categories were found
            */
			foreach($result as $key=>$value){
				$value['CategoryName']		=	ucfirst($value['CategoryName']);
				$CategoryListArray[] 	= 	$value;
			}
			$CategoryArray['result'] 	= 	$CategoryListArray;
			return $CategoryArray;
		}
		else{
			/**
			* throwing error when no data found
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
    * Create new category
    */
    public function create(){ // Tuplit new category
		 
		/**
		* Get the bean
		* @var $bean Categories
		*/		 
        $bean 			= 	$this->bean;	
		
		//validate category
		$this->validate();
		
		$merchantId		= 	$bean->fkMerchantId;
		$categoryName	= 	$bean->CategoryName;
		$catId			= 	$bean->CategoryId;
		
		//validate create
		$this->validateCreate();
		
		$categories   					= 	R::dispense('productcategories');
		$categories->fkMerchantId 		= 	$merchantId;
		$categories->CategoryName 		= 	$categoryName;
		$categories->Status 			= 	1;
		if($catId	!= ''){
			$categories->id		= 	$catId;
		}
		else{
			// save the bean to the database
			 $categories->DateCreated 		= 	date('Y-m-d H:i:s');
		}
		$categoryId = R::store($categories);
		return $categoryId;
    }
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		* @var $bean Categories
		*/
        $bean 			= 	$this->bean;
		$catId			=	$bean->CategoryId;
		$condition 		= 	'';
		if($catId != ''){
			$condition	= 	" and id != ".$catId ."";
		}
		if($bean->CategoryName != ''){
			$sql 		= "SELECT  c.id as CategoryId,c.CategoryName from productcategories as c where c.Status = 1 and c.fkMerchantId IN(".$bean->fkMerchantId.",0) ".$condition." and CategoryName = '".trim($bean->CategoryName)."' ";
   			$existingCategory 	= R::getAll($sql);
	        if ($existingCategory) {
	            // if category name exist it wont insert
	            throw new ApiException("Category name already exist ", ErrorCodeType::CategoryAlreadyExist);
			}
		}
	}
	
	/**
	* get single product category details
	*/
	public function getSingleProdctCategory($merchantId,$categoryId)
    {
		/**
		* Query to get category list
		*/
		$sql 		= 	"SELECT  c.id as CategoryId,c.CategoryName from productcategories as c where c.Status = 1 and fkMerchantId IN(".$merchantId.",0) and id = ".$categoryId." ORDER BY c.id desc";
   		$result 	= 	R::getAll($sql);
		if($result){
			/**
			* The categories were found
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
	* Validate category name
	* @throws ApiException if the models fails to validate
	*/
	public function validate()
    {
		$bean 		= 	$this->bean;
		if($bean->Type == 1){
		  	$rules 	= 	[
							'required' => [
								 ['CategoryName']
							],
						];
		}
		else{
			$rules 	= 	[
							'required' => [
								 ['CategoryName'],['CategoryId']
							],
						];
		}
		
        $v 			= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the category properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* @param  delete product category
	*/
    public function deleteCategory(){
		
		/**
		* Get the bean
		* @var $bean Categories
		*/
        $bean 	 		=  	$this->bean;
		$merchantId 	= 	$bean->MerchantId;
		$categoryId 	=  	$bean->CategoryId;
		$this->validateMerchantId($merchantId);
		
		$comments	  	= 	R::dispense('productcategories');
		$sql		  	=	"delete from productcategories where id = ".$categoryId."";
		R::exec($sql);
	}
	
	/**
	* Validate the merchant status
	*/
	public function validateMerchantId($merchantId)
    {
		/**
		* Get the identity of the merchant
		*/
        $merchant = R::findOne('merchants', 'id = ? and Status = ?', [$merchantId,StatusType::ActiveStatus]);
        if (!$merchant) {
            // the merchant was not found
            throw new ApiException("Merchant status is not in active state", ErrorCodeType::MerchantsNotInActiveStatus);
        }
		return $merchant;
    }
}