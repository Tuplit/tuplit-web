<?php

/**
 * Description of Products
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

class Products extends RedBean_SimpleModel implements ModelBaseInterface {

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
	public function getProductDetail($productid,$merchantId)
    {
		global	$discountTierArray;

		/**
		* Query to get product detail
		*/
		$sql 	= "SELECT p.id,p.ItemName,p.Price,p.fkCategoryId,p.Photo,p.DiscountApplied,p.Status,p.ItemDescription,p.ItemType from products p where p.id='".$productid."' and p.fkMerchantsId='".$merchantId."'";
		$result = R::getAll($sql);
		if($result){
			if($result[0]['ItemType'] == '3') {
				$sql1 		= 	"SELECT sp.id as SpecialId,sp.fkProductsId,sp.Quantity,p.Price from specialproducts sp
									left join products p on(sp.fkProductsId = p.id)
									where sp.fkSpecialId='".$productid."'";
				$result1 	= 	R::getAll($sql1);
				if($result1) {
					$result[0]['SpecialProducts']	=	$result1;	
				}else 
					$result[0]['SpecialProducts']	=	'';
			}
			//Image Path
			if(isset($result[0]['Photo']) && $result[0]['Photo'] != ''){
				if(SERVER)
					$image_path = PRODUCT_IMAGE_PATH.$result[0]['Photo'];
				else if(file_exists(PRODUCT_IMAGE_PATH_REL.$result[0]['Photo']))
					$image_path = PRODUCT_IMAGE_PATH.$result[0]['Photo'];
				else
					$image_path = ADMIN_IMAGE_PATH_OTHER.'no_image.jpeg';
			} else if(isset($result['Photo']) && $result[0]['Photo'] == '') {
				$image_path = ADMIN_IMAGE_PATH_OTHER.'no_image.jpeg';
			}
			$result[0]['Photo']	=	$image_path;
			
			//converting price to float
			$result[0]['Price']	=	floatval($result[0]['Price']);
			
			$result[0]['ItemName']	=	ucfirst($result[0]['ItemName']);
			
			//Calculating discount Price
			$sql1 	= "SELECT DiscountTier from merchants where id='".$merchantId."'";
			$result1 = R::getAll($sql1);
			if($result1) {
				$discountPrice = $result[0]['Price'] - (($result[0]['Price'] / 100) * $discountTierArray[$result1[0]['DiscountTier']]);
				$result[0]['DiscountPrice']	=	floatval($discountPrice);
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
	* get Product list
	*/
	public function getProductList($merchantId,$Search,$app=0)
    {
		
		global	$discountTierArray;
		$type		=	'';
		/**
		* Get the bean
		* @var $bean Product
		*/
        $bean 		= $this->bean;	
		
		if(isset($bean->Type) && !empty($bean->Type) && $bean->Type == 1) {
			$type		=	$bean->Type;
			$orderby	=	'p.ItemName asc';
		} else {
			$orderby	=	'pc.id asc, p.id desc';
		}
		/**
		* Query to get products list
		*/
		if(!empty($Search)) 
			$condition  = "AND (p.ItemName LIKE '%".$Search."%'OR pc.CategoryName LIKE '%".$Search."%')";
		else
			$condition  = '';
			
		$fields 		= 	'p.id as ProductId,p.ItemName,pc.id as fkCategoryId,p.Photo,p.DiscountApplied,p.Price,p.OriginalPrice,p.ItemType,p.ItemDescription,pc.CategoryName,pc.fkMerchantId as CategoryMerchnatId,p.Status,p.Ordering';
		$sql 			= 	"SELECT ".$fields." from productcategories  as pc 
							left join products as p on (p.fkCategoryId = pc.id  and p.fkMerchantsId = ".$merchantId." and p.Photo!='' and p.Status in (1,2))
							where pc.fkMerchantId IN(".$merchantId.") and  pc.Status=1 ".$condition." order by ".$orderby;
		$result 		= R::getAll($sql);
		if(!empty($Search))
			$condition  = "AND ( p.ItemName LIKE '%".$Search."%' )";
		else
			$condition  = '';
		$result2		=	array();		
		if($type == '') {
			$sql2 			= 	"SELECT p.id as ProductId,p.ItemName,p.fkCategoryId,p.Photo,p.DiscountApplied,p.Price,p.OriginalPrice,p.ItemType,p.Status,p.Ordering from products as p							
								where p.fkMerchantsId = $merchantId and p.Photo!='' and p.Status in (1,2) and p.ItemType in (3) ".$condition." order by p.Ordering asc";
			$result2 		= 	R::getAll($sql2);
			foreach($result2 as $key=>$cat) {
				$result2[$key]['CategoryName'] = '';
			}
		}
		
		if($result || $result2){
			$result	=	array_merge($result,$result2);		
			//getting merchant discountTier
			$sql1 		= "SELECT DiscountTier,DiscountType,DiscountProductId from merchants where id='".$merchantId."'";
			$result1 	= R::getAll($sql1);
			if($result1) {
				$discountTier 		= $discountTierArray[$result1[0]['DiscountTier']];
				$DiscountType 		= $result1[0]['DiscountType'];
				$DiscountProductId  = $result1[0]['DiscountProductId'];
			}
			else 
				$discountTier = '1';
			foreach($result as $key=>$value){
				//Image Path
				$image_path = '';				
				if(isset($value['Photo']) && $value['Photo'] != '')
					$image_path = PRODUCT_IMAGE_PATH.$value['Photo'];
				else if(isset($value['Photo']) && $value['Photo'] == '')
					$image_path = MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
				$result[$key]['Photo']	=	$image_path;
				
				//converting price to float
				$result[$key]['Price']	=	floatval($value['Price']);
				if($result[$key]['ProductId'])
					$productId	=	$result[$key]['ProductId'];
				else
					$productId	=	'';
				$result[$key]['ProductId']	=	$productId;
				if($result[$key]['ItemName'])
					$itemName	=	ucfirst($result[$key]['ItemName']);
				else
					$itemName	=	'';	
				$result[$key]['ItemName']	=	$itemName;
				
				if($result[$key]['CategoryName'])
					$categoryName	=	ucfirst($result[$key]['CategoryName']);
				else
					$categoryName	=	'';	
				$result[$key]['CategoryName']	=	$categoryName;
				
				
				if($result[$key]['DiscountApplied'])
					$discountApplied	=	$result[$key]['DiscountApplied'];
				else
					$discountApplied	=	'';	
				$result[$key]['DiscountApplied']	=	$discountApplied;
				
				if($result[$key]['Status'])
					$status	=	$result[$key]['Status'];
				else
					$status	=	'';	
				$result[$key]['Status']	=	$status;
				//Calculating discount Price
				$discountPrice = 0;			
				if($DiscountType == 0){
					if($value['DiscountApplied'] == 1) { 
						$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
					}	
				}
				else if($DiscountType == 1)
				{
					if($DiscountProductId == 'all') { 
						if($value['DiscountApplied'] == 1) { 
							$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
						}
					}
					else{
						if(isset($DiscountProductId) && $DiscountProductId != ''){
							$productListArray = explode(',',$DiscountProductId);
							 if(isset($productListArray) &&  in_array($value['ProductId'],$productListArray)) { 
								if($value['DiscountApplied'] == 1) { 
									$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
								}
							 }							
						}
					}
				}
				$result[$key]['DiscountPrice']	=	floatval($discountPrice);
			}
			
			if($type == '') {
				$productListArray = array();		
				foreach($result as $key=>$value){
					if($app == 0)
						$keyIndex = $value['fkCategoryId'];
					else
						$keyIndex = $value['CategoryName'];
					$productListArray[$keyIndex][] = $value;		
				}
				return $productListArray;
			} else {
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
	* get Product list
	*/
	public function getProductListWithCategory($merchantId,$Search,$app=0)
    {
		global	$discountTierArray;
		/**
		* Query to get products list
		*/
		$staus_condition = '';
		if(!empty($Search))
			$condition  = 	"AND (p.ItemName LIKE '%".$Search."%'OR pc.CategoryName LIKE '%".$Search."%')";
		else
			$condition  = 	'';
		if($app == 1){
			$staus_condition	=	' p.Status = 1 ';
		}
		else
			$staus_condition	=	' p.Status in (1,2)';
		$fields 		= 	'p.id as ProductId,p.ItemName,p.fkCategoryId,pc.CategoryName,p.Photo,p.DiscountApplied,p.Price,p.ItemType,p.OriginalPrice,pc.CategoryName,pc.fkMerchantId as CategoryMerchnatId,p.Status,p.Ordering';
		$sql 			= 	"SELECT ".$fields." from products as p
							left join productcategories pc on (p.fkCategoryId = pc.id )
							where p.fkMerchantsId = $merchantId and p.Photo!='' and ".$staus_condition." and pc.Status=1 ".$condition." order by pc.id asc, p.Ordering asc";
		$result 		= 	R::getAll($sql);
		
		if(!empty($Search))
			$condition  = 	"AND ( p.ItemName LIKE '%".$Search."%' )";
		else
			$condition  = 	'';
		
		$sql2 			= 	"SELECT p.id as ProductId,p.ItemName,p.fkCategoryId,p.Photo,p.DiscountApplied,p.Price,p.ItemType,p.OriginalPrice,p.Status,p.Ordering from products as p							
							where p.fkMerchantsId = $merchantId and p.Photo!='' and ".$staus_condition." and p.ItemType in (3) ".$condition." order by p.Ordering asc";
		$result2 		= 	R::getAll($sql2);
		$tempSpecial	=	array();
		foreach($result2 as $key=>$cat) {
			if($cat['ItemType'] == 3) {
				$tempSpecial[]	=	$cat;
				unset($result2[$key]);
			}
		}

		$tempSpecial = subval_sort($tempSpecial,'ProductId');
		if($result){
			$result							=	array_merge($result2,$result);
			
			//getting merchant discountTier
			$sql1 							= 	"SELECT DiscountTier,DiscountType,DiscountProductId from merchants where id='".$merchantId."'";
			$result1 						= 	R::getAll($sql1);
			if($result1) {	
				$discountTier				=	1;
				if(!empty($result1[0]['DiscountTier']))
					$discountTier 			= 	$discountTierArray[$result1[0]['DiscountTier']];
				$DiscountType 				= 	$result1[0]['DiscountType'];
				$DiscountProductId  		= 	$result1[0]['DiscountProductId'];
			}

			foreach($result as $key=>$value){
				unset($result[$key]['OriginalPrice']);
				
				//Image Path
				$image_path 				= 	'';				
				if(isset($value['Photo']) && $value['Photo'] != '')
					$image_path 			= 	PRODUCT_IMAGE_PATH.$value['Photo'];					
				else if(isset($value['Photo']) && $value['Photo'] == '')
					$image_path 			= 	MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
				$result[$key]['Photo']		=	$image_path;
				
				//converting price to float
				$result[$key]['Price']		=	floatval($value['Price']);				
				$result[$key]['ItemName']	=	ucfirst($value['ItemName']);				
				$result[$key]['Ordering']	=	$value['Ordering'];
				
				//Calculating discount Price
				$discountPrice 				= 	0;			
				if($DiscountType == 0){
					if($value['DiscountApplied'] == 1) { 
						$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
					}	
				}
				else if($DiscountType == 1)
				{
					if($DiscountProductId == 'all') { 
						if($value['DiscountApplied'] == 1) { 
							$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
						}
					}
					else{
						if(isset($DiscountProductId) && $DiscountProductId != ''){
							$productListArray = explode(',',$DiscountProductId);
							 if(isset($productListArray) &&  in_array($value['ProductId'],$productListArray)) { 
								if($value['DiscountApplied'] == 1) { 
									$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
								}
							 }							
						}
					}
				}
				$result[$key]['DiscountPrice']							=	floatval($discountPrice);
			}

			$productCategoryList 	= 	$productDiscountArray			= 	$productSpecialArray	= 	array();
			$spkey														=	0;	
			
			//SpecialProducts array
			if(isset($tempSpecial) && !empty($tempSpecial) && count($tempSpecial) > 0) {
				if(count($tempSpecial) >= 2) {
					$spStart	=	count($tempSpecial) - 1;
					$spEnd		=	count($tempSpecial) - 2;
					for($si = $spStart; $si >= $spEnd; $si--) {
						$productSpecialArray[$spkey]['ProductId']		=	$tempSpecial[$si]['ProductId'];
						$productSpecialArray[$spkey]['ItemName']		=	ucfirst($tempSpecial[$si]['ItemName']);
						if(!empty($tempSpecial[$si]['Photo']))
							$productSpecialArray[$spkey]['Photo']		=	PRODUCT_IMAGE_PATH.$tempSpecial[$si]['Photo'];
						else
							$productSpecialArray[$spkey]['Photo']		=	MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
						$productSpecialArray[$spkey]['Price']			=	$tempSpecial[$si]['OriginalPrice'];
						$productSpecialArray[$spkey]['DiscountPrice']	=	$tempSpecial[$si]['Price'];
						$productSpecialArray[$spkey]['DiscountTier']	=	'';	
						$spkey											=	$spkey	+	1;
						unset($tempSpecial[$si]);
					}
				}
				else if(count($tempSpecial) == 1){
					$productSpecialArray[0]['ProductId']				=	$tempSpecial[0]['ProductId'];
					$productSpecialArray[0]['ItemName']					=	ucfirst($tempSpecial[0]['ItemName']);
					if(!empty($tempSpecial[0]['Photo']))
						$productSpecialArray[0]['Photo']				=	PRODUCT_IMAGE_PATH.$tempSpecial[0]['Photo'];
					else
						$productSpecialArray[0]['Photo']				=	MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
					$productSpecialArray[0]['Price']					=	$tempSpecial[0]['OriginalPrice'];
					$productSpecialArray[0]['DiscountPrice']			=	$tempSpecial[0]['Price'];
					$productSpecialArray[0]['DiscountTier']				=	'';	
					unset($tempSpecial[0]);
				}
			}
			foreach($result as $key=>$value){
				if($value['DiscountPrice'] != 0) {
					unset($value['fkCategoryId']);
					unset($value['CategoryName']);
					unset($value['CategoryMerchnatId']);
					$productDiscountArray[]	=	$value;					
				}
				else {
					if($app == 0)
						$keyIndex 										= 	$value['fkCategoryId'];
					else 
						$keyIndex 										= 	$value['CategoryName'];					
					$productCategoryList[$keyIndex][]					= 	$value;
				}
			}
			$productCategoryList 										= 	array_values($productCategoryList);
			
			foreach($productCategoryList as $key=>$value) {
				if(count($value) > 0) {
					$temp 												=	array();
					$temp['CategoryId'] 								= 	$value[0]['fkCategoryId'];
					$temp['CategoryName'] 								= 	ucfirst($value[0]['CategoryName']);
					foreach($value as $key1=>$value1) {						
						$temp['Items'][$key1]['ProductId']				= 	$value1['ProductId'];
						$temp['Items'][$key1]['ItemName']				= 	ucfirst($value1['ItemName']);
						$temp['Items'][$key1]['Photo']					= 	$value1['Photo'];
						$temp['Items'][$key1]['DiscountApplied']		= 	$value1['DiscountApplied'];
						$temp['Items'][$key1]['Price']					= 	$value1['Price'];
						$temp['Items'][$key1]['Status']					= 	$value1['Status'];
						$temp['Items'][$key1]['DiscountPrice']			= 	$value1['DiscountPrice'];
						$temp['Items'][$key1]['Ordering']				= 	$value1['Ordering'];
					}					
				}	
				$productCategoryList[$key]								= 	$temp;
			}
			
			if(!empty($tempSpecial) && count($tempSpecial) > 0) {
				$temp 												=	array();
				$temp['CategoryId'] 								= 	'0';
				$temp['CategoryName'] 								= 	'Specials';
				foreach($tempSpecial as $key1=>$value1) {						
					$temp['Items'][$key1]['ProductId']				= 	$value1['ProductId'];
					$temp['Items'][$key1]['ItemName']				= 	ucfirst($value1['ItemName']);
					if(!empty($value1['Photo']))
						$temp['Items'][$key1]['Photo']				=	PRODUCT_IMAGE_PATH.$value1['Photo'];
					else
						$temp['Items'][$key1]['Photo']				=	MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
					$temp['Items'][$key1]['DiscountApplied']		= 	$value1['DiscountApplied'];
					$temp['Items'][$key1]['Price']					= 	$value1['OriginalPrice'];
					$temp['Items'][$key1]['Status']					= 	$value1['Status'];
					$temp['Items'][$key1]['DiscountPrice']			= 	$value1['Price'];
					$temp['Items'][$key1]['Ordering']				= 	$value1['Ordering'];
				}	
				$productCategoryList[]								= 	$temp;
			}
			$outputProductListArray['SpecialProducts'] 					= 	$productSpecialArray;
			$outputProductListArray['DiscountProducts'] 				= 	$productDiscountArray;
			$outputProductListArray['MenuProducts'] 					= 	$productCategoryList;
			return $outputProductListArray;
		}
		else{
			return $result;
		}
	}
	
	/**
	* get Popular Product list
	*/
	public function getPopularProducts($merchantId)
    {
		global	$discountTierArray;
		
		/**
		* Query to get Popular products list
		*/		
		$fields 	=   "c.fkProductsId,count(c.fkProductsId) as total,p.ItemName,p.Photo,p.DiscountApplied,p.Price";
		$join		=	"left join products p on (c.fkProductsId = p.id)";
		$condition 	=	"and p.Status=1 and p.fkMerchantsId='".$merchantId."' and c.fkMerchantsId='".$merchantId."'";
		$sql 		= 	"SELECT ".$fields." FROM `carts` c ".$join." WHERE 1 ".$condition." group by c.fkProductsId order by total desc limit 0,15";
		$result 	= R::getAll($sql);
		if($result){		
			//getting merchant discountTier
			$sql1 	= "SELECT DiscountTier,DiscountType,DiscountProductId from merchants where id='".$merchantId."'";
			$result1 = R::getAll($sql1);
			if($result1) {
				$discountTier 		= $discountTierArray[$result1[0]['DiscountTier']];
				$DiscountType 		= $result1[0]['DiscountType'];
				$DiscountProductId  = $result1[0]['DiscountProductId'];
			}
			else 
				$discountTier = '1';
				
			foreach($result as $key=>$value){
				//Image Path
				$image_path = '';				
				if(isset($value['Photo']) && $value['Photo'] != '')
					$image_path = PRODUCT_IMAGE_PATH.$value['Photo'];					
				else if(isset($value['Photo']) && $value['Photo'] == '')
					$image_path = MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';
				$result[$key]['Photo']	=	$image_path;
				
				//converting price to float
				$result[$key]['Price']	=	floatval($value['Price']);
				
				$result[$key]['ItemName']	=	ucfirst($value['ItemName']);
				
				//Calculating discount Price
				$discountPrice = 0;			
				if($DiscountType == 0){
					if($value['DiscountApplied'] == 1) { 
						$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
					}	
				}
				else if($DiscountType == 1)
				{
					if($DiscountProductId == 'all') { 
						if($value['DiscountApplied'] == 1) { 
							$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
						}
					}
					else{
						if(isset($DiscountProductId) && $DiscountProductId != ''){
							$productListArray = explode(',',$DiscountProductId);
							 if(isset($productListArray) &&  in_array($value['fkProductsId'],$productListArray)) { 
								if($value['DiscountApplied'] == 1) { 
									$discountPrice = $value['Price'] - (($value['Price']/100) * $discountTier);
								}
							 }							
						}
					}
				}				
				$result[$key]['DiscountPrice']	=	floatval($discountPrice);
			}
			return $result;
		}
		else{
			return $result;
		}
	}	
	
	/**
	* Create new product
	*/
    public function create(){ // Tuplit new product
		 
		/**
		* Get the bean
		* @var $bean Product
		*/
        $bean 		= $this->bean;	
		$flag 		= $bean->PhotoFlag;
		
		// validate the model	
		$this->validate();
		
		if($flag != 0){
			//error throw for image error
			if($flag == 1)
				throw new ApiException("Problem in Image - Type", ErrorCodeType::ProblemInImage);
			else if($flag == 2)
				throw new ApiException("Problem in Image",ErrorCodeType::ProblemInImage);
			else if($flag == 3)
				throw new ApiException("Problem in Image - Size",ErrorCodeType::ProblemInImage);
			else if($flag == 4)
				throw new ApiException("Problem in Image - Dimension.Minimum should be (100X100)",ErrorCodeType::ProblemInImage);
		}
		unset($bean->PhotoFlag);
		
		//validate product exists or not
		$this->validateCreate();
		
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
    public function modify($type = ''){

		/**
		* Get the bean
		* @var $bean Products
		*/
		$bean 				= $this->bean;
		$DateModified		= date('Y-m-d H:i:s');
		
		// validate the model	
			$this->validate($type);
		
		if(empty($type)) {	
			unset($bean->CatId);
			
			//validate product exists or not
			$this->validateCreate();
			
			$bean->fkCategoryId = $bean->CategoryId;
			unset($bean->CategoryId);
			unset($bean->fkMerchantsId);
			  
			unset($bean->ImageAlreadyExists);
			$bean->DateModified = $DateModified;
			
			// modify the bean to the database
			R::store($this);
		} else {
			$i	=	1;
			foreach($bean->ProductIds as $key=>$val) {
				$sql = "update products set Ordering ='".$i."', DateModified='".$DateModified."', fkCategoryId='".$bean->CatId."' where id = '".$val."'";
				//echo $sql;
				R::exec($sql);
				$i++;
			}
		}		
    }
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate($type = '')
    {
		$bean = $this->bean;
		if($bean->ImageAlreadyExists == 1){
			$rules = [
				'required' => [
					 ['CategoryId'],['ItemName'],['Price'],['Status']
				]
			];
		} else if($type == 1) {
			$rules = [
				'required' => [
					['ProductIds']
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
	* Validate the modification of Products
	* @throws ApiException if the product not exists in the database.
	*/
	public function validateCreate()
    {
		/**
		* Get the bean
		* @var $bean Products
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
				$modifiedName = R::findOne('products', 'ItemName = ? and id != ? and fkMerchantsId = ? and Status = ?',array($bean->ItemName,$bean->id,$bean->fkMerchantsId,StatusType::ActiveStatus));
			}
			else {
				$modifiedName = R::findOne('products', 'ItemName = ? and fkMerchantsId = ? and Status = ?',array($bean->ItemName,$bean->fkMerchantsId,StatusType::ActiveStatus));
			}
			if ($modifiedName) {
				// the Product name was found
				throw new ApiException("Product name already exists.", ErrorCodeType::NotAccessToDoProcess);
			}	
		}
    }
	
	/**
	* get merchant total products and Discount Applied 
	*/
	public function getProductsDiscounttype($merchantId)
    {
		$sql 			= 	"select count(id) as productCount,DiscountApplied from products where Status = 1 and fkMerchantsId  = ".$merchantId." and ItemType = 1 group by DiscountApplied";
		$countResult 	=	R::getAll($sql);
		return $countResult;
	}
	
	/**
	* get merchant total products
	*/
	public function getProductsTotal()
    {
		/**
		* Get the bean
		* @var $bean Products
		*/
        $bean = $this->bean;
		
		$countResult = Array();
		//$sql 			= 	"select fkCategoryId as CategoryId,DiscountApplied,ItemType from products where Status = 1 and fkMerchantsId  = ".$bean->merchantId;
		$sql 			= 	"SELECT pc.id as CategoryId,p.DiscountApplied,p.ItemType from productcategories  as pc 
								left join products as p on (p.fkCategoryId = pc.id  and p.fkMerchantsId ='".$bean->merchantId."' and p.Photo!='' and p.Status in (1,2))
								where pc.fkMerchantId IN(".$bean->merchantId.") and  pc.Status=1  order by pc.id asc, p.id desc";
		$countResult1 	=	R::getAll($sql);
		
		$sql 			= 	"SELECT p.id as CategoryId,p.DiscountApplied,p.ItemType from products as p							
								where p.fkMerchantsId = ".$bean->merchantId." and p.Photo!='' and p.Status in (1,2) and p.ItemType in (3)  order by p.Ordering asc";
		$countResult2 	=	R::getAll($sql);
		
		if($countResult1 || $countResult2)
			$countResult	=	array_merge($countResult1,$countResult2);
		return $countResult;
	}
	
	/**
	* get delete products
	*/
	public function deleteProducts($deleteId)
    {
		/**
		* Get the bean
		* @var $bean Products
		*/
        $bean = $this->bean;
		
		//Deleting products in product table
		$sql = "update products set Status ='3', DateModified='".date('Y-m-d H:i:s')."' where id in (".$deleteId.")";
		R::exec($sql);
		
		//deleting products from specialproducts table
		if(isset($bean->ItemType) && !empty($bean->ItemType) && $bean->ItemType =3) {
			$sql = "update specialproducts set Status ='3' where fkSpecialId in (".$deleteId.")";
			R::exec($sql);
		}
		
	}
	
	/**
	* get Product Analytics
	*/
	public function getProductAnalytics($merchantId)
    {
		/**
		* Get the bean
		* @var $bean Products
		*/
        $bean 		= 	$this->bean;
		$dataType	=	'day';
		$field		=	$condition	=	$condition2	=	$case = '';
		$dataType 	= 	$bean->DataType;
		$Start		=	0;
		if(isset($bean->Start))
			$Start 		= 	$bean->Start;
		if(isset($bean->TimeZone))
			$time_zone	= 	$bean->TimeZone;
		
		$curr_date 		= 	date('Y-m-d');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		$cur_day 		= 	date('D');
		
		$case .=" , CASE WHEN HOUR(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) BETWEEN '04' AND '11' THEN 1
							WHEN HOUR(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) BETWEEN '12' AND  '19' THEN 2
							WHEN ((HOUR(DATE_ADD(o.OrderDate,INTERVAL ' 05:30' HOUR_MINUTE)) BETWEEN '20' AND '23') or (HOUR(DATE_ADD(o.OrderDate,INTERVAL ' 05:30' HOUR_MINUTE)) BETWEEN '00' AND '03')) THEN 3
							END as period ";
		if($dataType == 'day') {	
			$condition	.=	" and Date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$curr_date."'";
			$dateDiffer	=	date('Y-m-d',strtotime("-7 days"));
		} else if($dataType == '7days') {	 
			$dateDiffer	=	date('Y-m-d',strtotime("-7 days"));
			$condition	.= 	" and (DATE(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) <= '".$curr_date."' and DATE(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) > '".$dateDiffer."')";
		}else if($dataType == 'month') {	
			$dateDiffer		=	date('Y-m-d',strtotime("-30 days"));
			$condition	.= 	" and (Month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_month."') and Year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_year."'";
		} else if($dataType == 'year') {	
			$condition	.= 	" and Year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_year."' ";
		} 		
		$sql	=	"SELECT SQL_CALC_FOUND_ROWS p.id as ProductId,p.ItemName as Name,p.Photo, count(o.id) as TotalOrder from orders as o 
							left join carts as c ON (c.CartId = o.fkCartId)
                         	left join products as p on(p.id = c.fkProductsId)
							where 1   and o.Status = 1 and o.TransactionId != '' and  o.OrderStatus IN (1) ".$condition." and o.fkMerchantsId = ".$merchantId." and  
							c.fkMerchantsId = ".$merchantId." and p.Status=1 and p.fkMerchantsId =  ".$merchantId."   group by p.id order by TotalOrder desc limit $Start,8";
		// echo "<br>========================".$sql;
		$result	=	R::getAll($sql);
		
		
		$total				=	R::getAll('SELECT FOUND_ROWS() as count ');
		$totalProducts		=	$total[0]['count'];
	
		if($result) {
			$productIds	=	$ProductArray	=	$outArray = Array();
			$outArray['totalProducts']	=	$totalProducts;
			$outArray['listedProducts']	=	count($result);
			foreach($result as $val) {
				$productIds[] 	=	$val['ProductId'];
				$image_path = MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';	
				if(isset($val['Photo']) && $val['Photo'] != '')
					$image_path = PRODUCT_IMAGE_PATH.$val['Photo'];	
				
				$ProductArray[$val['ProductId']]['ProductID'] 		= 	$val['ProductId'];
				$ProductArray[$val['ProductId']]['ProductName'] 	= 	$val['Name'];
				$ProductArray[$val['ProductId']]['ProductImage'] 	= 	$image_path;							
			}
			if(count($productIds) > 0) {
				$sql 	= "SELECT c.fkProductsId as ProductId, Sum(c.ProductsQuantity) as TotalQty,DATE_FORMAT(o.OrderDate,'%a') as OrderDay, sum(c.TotalPrice * c.ProductsQuantity) as OriginalCost, sum(c.DiscountPrice * c.ProductsQuantity) as DiscountCost, count(o.id) as TotalOrder ".$case." from	carts as c
							left join orders as o on(c.CartId = o.fkCartId) 
							left join products as p on(c.fkProductsId = p.id) 
							where 1 and o.Status = 1 and o.TransactionId != '' and p.Status=1 and  o.OrderStatus IN (1) and o.Status = 1 and p.id in (".implode(',',$productIds).") ".$condition." and o.fkMerchantsId = ".$merchantId." 
							and c.fkMerchantsId = ".$merchantId." GROUP BY c.fkProductsId".trim($case," as period ").",Date(DATE_ADD(o.OrderDate,INTERVAL ' 05:30' HOUR_MINUTE))" ;
				// echo "<br>========================".$sql;
				
				$ProductList 	=	R::getAll($sql);
				if($ProductList ){	
					$TempArray	=	Array();
					foreach($ProductList as $value) {						
						if($value['period'] == 1) {							
							if(isset($TempArray[$value['ProductId']]['Morning'])) {
								$TempArray[$value['ProductId']]['Morning']['OrgAmount'] = round($TempArray[$value['ProductId']]['Morning']['OrgAmount'] + $value['OriginalCost'],2);
								$TempArray[$value['ProductId']]['Morning']['DisAmount'] = round($TempArray[$value['ProductId']]['Morning']['DisAmount'] + $value['DiscountCost'],2);
								$TempArray[$value['ProductId']]['Morning']['TotOrder'] 	= $TempArray[$value['ProductId']]['Morning']['TotOrder'] + $value['TotalOrder'];
							}
							else {								
								$TempArray[$value['ProductId']]['Morning']['OrgAmount'] = round($value['OriginalCost'],2);
								$TempArray[$value['ProductId']]['Morning']['DisAmount'] = round($value['DiscountCost'],2);
								$TempArray[$value['ProductId']]['Morning']['TotOrder'] 	= $value['TotalOrder'];
							}							
						}
						else if($value['period'] == 2) {
							if(isset($TempArray[$value['ProductId']]['Noon'])) {
								$TempArray[$value['ProductId']]['Noon']['OrgAmount'] 	= round($TempArray[$value['ProductId']]['Noon']['OrgAmount'] + $value['OriginalCost'],2);
								$TempArray[$value['ProductId']]['Noon']['DisAmount'] 	= round($TempArray[$value['ProductId']]['Noon']['DisAmount'] + $value['DiscountCost'],2);
								$TempArray[$value['ProductId']]['Noon']['TotOrder'] 	= $TempArray[$value['ProductId']]['Noon']['TotOrder'] + $value['TotalOrder'];
							}
							else {								
								$TempArray[$value['ProductId']]['Noon']['OrgAmount'] = round($value['OriginalCost'],2);
								$TempArray[$value['ProductId']]['Noon']['DisAmount'] = round($value['DiscountCost'],2);
								$TempArray[$value['ProductId']]['Noon']['TotOrder'] = $value['TotalOrder'];
							}
						}
						else if($value['period'] == 3) {
							if(isset($TempArray[$value['ProductId']]['Evening'])) {
								$TempArray[$value['ProductId']]['Evening']['OrgAmount'] 	= round($TempArray[$value['ProductId']]['Evening']['OrgAmount'] + $value['OriginalCost'],2);
								$TempArray[$value['ProductId']]['Evening']['DisAmount'] 	= round($TempArray[$value['ProductId']]['Evening']['DisAmount'] + $value['DiscountCost'],2);
								$TempArray[$value['ProductId']]['Evening']['TotOrder'] 		= $TempArray[$value['ProductId']]['Evening']['TotOrder'] + $value['TotalOrder'];
							}
							else {								
								$TempArray[$value['ProductId']]['Evening']['OrgAmount'] = round($value['OriginalCost'],2);
								$TempArray[$value['ProductId']]['Evening']['DisAmount'] = round($value['DiscountCost'],2);
								$TempArray[$value['ProductId']]['Evening']['TotOrder'] = $value['TotalOrder'];
							}							
						}
						if(isset($TempArray[$value['ProductId']]['WeekList'][$value['OrderDay']]['TotalPrice'])) {
							$TempArray[$value['ProductId']]['WeekList'][$value['OrderDay']]['TotalPrice']	=	round($TempArray[$value['ProductId']]['WeekList'][$value['OrderDay']]['TotalPrice'] + $value['DiscountCost'],2);
						}
						else {
							$TempArray[$value['ProductId']]['WeekList'][$value['OrderDay']]['OrderDay']	=	$value['OrderDay'];
							$TempArray[$value['ProductId']]['WeekList'][$value['OrderDay']]['TotalPrice']	=	round($value['DiscountCost'],2);
						}
					}
					
					foreach($TempArray as $key=>$value) {
						$mqty = $nqty = $eqty = 0;
						if(isset($value['Morning'])) {
							$ProductArray[$key]['Morning'] 	= 	$value['Morning'];
							$mqty							=	$value['Morning']['DisAmount'];
						}
						if(isset($value['Evening'])) {
							$ProductArray[$key]['Evening'] 	= 	$value['Evening'];
							$eqty							=	$value['Evening']['DisAmount'];
						}
						if(isset($value['Noon'])) {
							$ProductArray[$key]['Noon'] 	= 	$value['Noon'];
							$nqty							=	$value['Noon']['DisAmount'];
						}
						if(isset($value['WeekList']))
							$ProductArray[$key]['WeekList'] 	= 	array_values($value['WeekList']);
						
						$tot	=	$mqty + $nqty + $eqty;
						if($tot > 0) {
							if(isset($ProductArray[$key]['Morning']['DisAmount']) && $ProductArray[$key]['Morning']['DisAmount'] > 0)
								$ProductArray[$key]['Morning']['Percentage']	=	round(($mqty/$tot) * 100,2);
							if(isset($ProductArray[$key]['Evening']['DisAmount']) && $ProductArray[$key]['Evening']['DisAmount'] > 0)
								$ProductArray[$key]['Evening']['Percentage']	=	round(($eqty/$tot) * 100,2);
							if(isset($ProductArray[$key]['Noon']['DisAmount']) && $ProductArray[$key]['Noon']['DisAmount'] > 0)
								$ProductArray[$key]['Noon']['Percentage']		=	round(($nqty/$tot) * 100,2);
						}						
					}				
				}			
				$outArray['result'] = array_values($ProductArray);
				return $outArray;
			}
		}
	}
	/**
	* get Product Analytics
	*/
	public function getProductAnalytics_old($merchantId)
    {
		/**
		* Get the bean
		* @var $bean Products
		*/
        $bean 		= 	$this->bean;
		$dataType	=	'day';
		$field		=	$condition	=	$condition2	=	$case = '';
		$dataType 	= 	$bean->DataType;
		$Start		=	0;
		if(isset($bean->Start))
			$Start 		= 	$bean->Start;
		if(isset($bean->TimeZone))
			$time_zone	= 	$bean->TimeZone;
		
		$curr_date 		= 	date('Y-m-d');
		$cur_month 		= 	date('m');
		$cur_year 		= 	date('Y');
		if($dataType == 'day') {
			$case .=" , CASE WHEN HOUR(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) BETWEEN '04' AND '11' THEN 1
							WHEN HOUR(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) BETWEEN '12' AND  '19' THEN 2
							WHEN ((HOUR(DATE_ADD(o.OrderDate,INTERVAL ' 05:30' HOUR_MINUTE)) BETWEEN '20' AND '23') or (HOUR(DATE_ADD(o.OrderDate,INTERVAL ' 05:30' HOUR_MINUTE)) BETWEEN '00' AND '03')) THEN 3
							END as period ";
			$condition	.=	" and Date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$curr_date."'";
			$condition2	.= 	" and (DATE(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) <= '".date('Y-m-d',strtotime($curr_date))."' and DATE(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) > '".date('Y-m-d',strtotime("-7 days"))."')";
		} 		
		$sql	=	"SELECT SQL_CALC_FOUND_ROWS p.id as ProductId,p.ItemName as Name,p.Photo,SUM(c.TotalPrice) as TotalPrice,count(o.id) as TotalOrder from orders as o 
							left join carts as c ON (c.CartId = o.fkCartId)
                         	left join products as p on(p.id = c.fkProductsId)
							where 1   and o.Status = 1 and o.TransactionId != '' and  o.OrderStatus IN (1) ".$condition." and o.fkMerchantsId = ".$merchantId." and  
							c.fkMerchantsId = ".$merchantId." and p.Status=1 and p.fkMerchantsId =  ".$merchantId."   group by p.id order by TotalOrder desc limit $Start,8";
		$result	=	R::getAll($sql);
		// echo "<br>========================".$sql;
		
		$total				=	R::getAll('SELECT FOUND_ROWS() as count ');
		$totalProducts		=	$total[0]['count'];
	
		if($result) {
			$productIds	=	$ProductArray	=	$outArray = Array();
			$outArray['totalProducts']	=	$totalProducts;
			$outArray['listedProducts']	=	count($result);
			foreach($result as $val) {
				$productIds[] 	=	$val['ProductId'];
				$image_path = MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';	
				if(isset($val['Photo']) && $val['Photo'] != '')
					$image_path = PRODUCT_IMAGE_PATH.$val['Photo'];	
				
				$ProductArray[$val['ProductId']]['ProductID'] 		= 	$val['ProductId'];
				$ProductArray[$val['ProductId']]['ProductName'] 	= 	$val['Name'];
				$ProductArray[$val['ProductId']]['ProductImage'] 	= 	$image_path;							
			}
			if(count($productIds) > 0) {
				$sql 	= "SELECT c.fkProductsId as ProductId, Sum(c.ProductsQuantity) as TotalQty, sum(c.TotalPrice) as OriginalCost, sum(c.DiscountPrice) as DiscountCost, count(o.id) as TotalOrder".$case." from	carts as c
							left join orders as o on(c.CartId = o.fkCartId) 
							left join products as p on(c.fkProductsId = p.id) 
							where 1 and o.Status = 1 and o.TransactionId != '' and p.Status=1 and  o.OrderStatus IN (1) and o.Status = 1 and p.id in (".implode(',',$productIds).") ".$condition." and o.fkMerchantsId = ".$merchantId." 
							and c.fkMerchantsId = ".$merchantId." GROUP BY c.fkProductsId".trim($case," as period ");
				$ProductList 	=	R::getAll($sql);
				// echo "<br>========================".$sql;
				
				//echo "<pre>";print_r($ProductList);echo "</pre>";
				if($ProductList ){	
					$TempArray	=	Array();
					foreach($ProductList as $value) {
						if(isset($value['period'])) {
							if($value['period'] == 1)
								$TempArray[$value['ProductId']]['Morning'] = $value;
							else if($value['period'] == 2)
								$TempArray[$value['ProductId']]['Noon'] = $value;
							else if($value['period'] == 3)
								$TempArray[$value['ProductId']]['Evening'] = $value;
						}
					}
					
					if(count($TempArray) > 0) {	
						foreach($TempArray as $key=>$value) {	
							$mqty = $nqty = $eqty = 0;
							if(isset($value['Morning']) && count($value['Morning']) > 0) {							
								$mqty	=	$value['Morning']['TotalQty'];
								$ProductArray[$key]['Morning']['OrgAmount'] 	= round($value['Morning']['OriginalCost'],2);			
								$ProductArray[$key]['Morning']['DisAmount'] 	= round($value['Morning']['DiscountCost'],2);			
								$ProductArray[$key]['Morning']['TotOrder'] 		= $value['Morning']['TotalOrder'];
							}
							if(isset($value['Noon']) && count($value['Noon']) > 0) {							
								$nqty	=	$value['Noon']['TotalQty'];
								$ProductArray[$key]['Noon']['OrgAmount'] = round($value['Noon']['OriginalCost'],2);			
								$ProductArray[$key]['Noon']['DisAmount'] = round($value['Noon']['DiscountCost'],2);			
								$ProductArray[$key]['Noon']['TotOrder'] 	= $value['Noon']['TotalOrder'];			
							}
							if(isset($value['Evening']) && count($value['Evening']) > 0) {							
								$eqty	=	$value['Evening']['TotalQty'];
								$ProductArray[$key]['Evening']['OrgAmount'] = round($value['Evening']['OriginalCost'],2);			
								$ProductArray[$key]['Evening']['DisAmount'] = round($value['Evening']['DiscountCost'],2);			
								$ProductArray[$key]['Evening']['TotOrder'] 	= $value['Evening']['TotalOrder'];			
							}	
							
							$totqty	=	$mqty + $nqty + $eqty;
							
							if(!empty($mqty))
								$ProductArray[$key]['Morning']['Percentage'] 	= round(($mqty/$totqty) * 100,2);
						
							if(!empty($nqty))
								$ProductArray[$key]['Noon']['Percentage'] 		= round(($nqty/$totqty) * 100,2);
								
							if(!empty($eqty))
								$ProductArray[$key]['Evening']['Percentage'] 	= round(($eqty/$totqty) * 100,2);
									
						}
					}		
				}
				
				$sql = "SELECT  p.id as ProductId,Date(o.OrderDate) as OrderDate,SUM(c.TotalPrice) as TotalPrice,count(o.id) as TotalOrders from orders as o 
						left join carts as c ON (c.CartId = o.fkCartId)
						left join products as p on(p.id = c.fkProductsId)
						where 1   and o.Status = 1 and o.TransactionId != '' and p.Status=1  and  o.OrderStatus IN (1) and o.fkMerchantsId = ".$merchantId." and  c.fkMerchantsId = ".$merchantId." 
						".$condition2." and p.fkMerchantsId =  ".$merchantId." and p.id in (".implode(',',$productIds).") group by Date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)),p.id  order by TotalPrice desc";
				$WeekList 	=	R::getAll($sql);
				// echo "<br>========================".$sql;
				
				if($WeekList){
					
					foreach($WeekList as $key=>$value) {
						$TempArray	=	Array();
						$TempArray['OrderDate'] 	= $value['OrderDate'];
						$TempArray['TotalPrice'] 	= round($value['TotalPrice'],2);
						$TempArray['TotalOrders'] 	= $value['TotalOrders'];
						$ProductArray[$value['ProductId']]['WeekList'][] = $TempArray;
					}
				}
				
				$outArray['result'] = array_values($ProductArray);
				return $outArray;
			}
		}
	}
}
