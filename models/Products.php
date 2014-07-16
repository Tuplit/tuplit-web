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
				$image_path = PRODUCT_IMAGE_PATH.$result[0]['Photo'];					
			} else if(isset($result['Photo']) && $result[0]['Photo'] == '') {
				$image_path = ADMIN_IMAGE_PATH.'no_image.jpeg';
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
		/**
		* Query to get products list
		*/
		if(!empty($Search))
			$condition  = "AND (p.ItemName LIKE '%".$Search."%'OR pc.CategoryName LIKE '%".$Search."%')";
		else
			$condition  = '';
			
		$fields 		= 'p.id as ProductId,p.ItemName,pc.id as fkCategoryId,p.Photo,p.DiscountApplied,p.Price,p.OriginalPrice,p.ItemType,p.ItemDescription,pc.CategoryName,pc.fkMerchantId as CategoryMerchnatId,p.Status,p.Ordering';
		$sql 			= 	"SELECT ".$fields." from productcategories  as pc 
							left join products as p on (p.fkCategoryId = pc.id  and p.fkMerchantsId = ".$merchantId." and p.Photo!='' and p.Status in (1,2))
							where pc.fkMerchantId IN(".$merchantId.") and  pc.Status=1 ".$condition." order by pc.id asc, p.id desc";
		$result 		= R::getAll($sql);
		
		if(!empty($Search))
			$condition  = "AND ( p.ItemName LIKE '%".$Search."%' )";
		else
			$condition  = '';
			
		$sql2 			= 	"SELECT p.id as ProductId,p.ItemName,p.fkCategoryId,p.Photo,p.DiscountApplied,p.Price,p.OriginalPrice,p.ItemType,p.Status,p.Ordering from products as p							
							where p.fkMerchantsId = $merchantId and p.Photo!='' and p.Status in (1,2) and p.ItemType in (2,3) ".$condition." order by p.Ordering asc";
		$result2 		= 	R::getAll($sql2);
		foreach($result2 as $key=>$cat) {
			$result2[$key]['CategoryName'] = '';
		}
		
		
		if($result || $result2){
			$result	=	array_merge($result,$result2);		
			//echo'<pre>';print_r($result);echo'</pre>';	
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
	* get Product list
	*/
	public function getProductListWithCategory($merchantId,$Search,$app=0)
    {
		global	$discountTierArray;
		/**
		* Query to get products list
		*/
		if(!empty($Search))
			$condition  = 	"AND (p.ItemName LIKE '%".$Search."%'OR pc.CategoryName LIKE '%".$Search."%')";
		else
			$condition  = 	'';
		$fields 		= 	'p.id as ProductId,p.ItemName,p.fkCategoryId,pc.CategoryName,p.Photo,p.DiscountApplied,p.Price,p.ItemType,p.OriginalPrice,pc.CategoryName,pc.fkMerchantId as CategoryMerchnatId,p.Status,p.Ordering';
		$sql 			= 	"SELECT ".$fields." from products as p
							left join productcategories pc on (p.fkCategoryId = pc.id )
							where p.fkMerchantsId = $merchantId and p.Photo!='' and p.Status in (1,2) and pc.Status=1 ".$condition." order by pc.id asc, p.Ordering asc";
		$result 		= 	R::getAll($sql);
		
		if(!empty($Search))
			$condition  = 	"AND ( p.ItemName LIKE '%".$Search."%' )";
		else
			$condition  = 	'';
		
		$sql2 			= 	"SELECT p.id as ProductId,p.ItemName,p.fkCategoryId,p.Photo,p.DiscountApplied,p.Price,p.ItemType,p.OriginalPrice,p.Status,p.Ordering from products as p							
							where p.fkMerchantsId = $merchantId and p.Photo!='' and p.Status in (1,2) and p.ItemType in (2,3) ".$condition." order by p.Ordering asc";
		$result2 		= 	R::getAll($sql2);
		$tempSpecial	=	array();
		foreach($result2 as $key=>$cat) {
			if($cat['ItemType'] == 2) {
				$result2[$key]['CategoryName'] = 'Deals';
				$result2[$key]['fkCategoryId'] = '0';				
			}
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
				$discountTier 				= 	$discountTierArray[$result1[0]['DiscountTier']];
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
					for($si = count($tempSpecial) - 1; $si >= count($tempSpecial) - 2; $si--) {
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
			
			//$productCategoryList = subval_sort($productCategoryList,'Ordering');
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
		$sql 		= 	"SELECT ".$fields." FROM `carts` c ".$join." WHERE 1 ".$condition." group by c.fkProductsId order by total desc limit 0,6";
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
			if($bean->CategoryId){
				$bean->fkCategoryId = $bean->CategoryId;
				unset($bean->CategoryId);
			}        
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
		$sql 			= 	"select count(id) as productCount,DiscountApplied from products where Status = 1 and fkMerchantsId  = ".$merchantId." group by DiscountApplied";
		$countResult 	=	R::getAll($sql);
		return $countResult;
	}
}
