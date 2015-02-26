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
		$from = 0;
		/**
		* Get the bean
		* @var $bean Categories
		*/		 
        $bean 			= 	$this->bean;	
		if(isset($bean->From))
			$from		= $bean->From;		
		
		
		if($from == 1){
			$sql 		= 	"SELECT SQL_CALC_FOUND_ROWS c.CategoryName,c.CategoryIcon,c.id as CategoryId,count(mc.fkMerchantId) as MerchantCount from categories as c
							LEFT JOIN merchantcategories as mc ON(c.id = mc.fkCategoriesId)
							LEFT JOIN merchants AS m ON ( mc.fkMerchantId = m.id and m.Status = 1)
							where c.Status = 1 GROUP BY c.id ORDER BY c.CategoryName asc";
		}
		else{
			//Check for total products   - 03/11/2014
			$merchantsIdIn	=	$wholediscount = $recently = $favouritesql = '';
			$CategoryArray	=	Array();
			$sql		=	"SELECT p.`fkMerchantsId`,count(p.id) as totcount FROM `products` as p
							left join merchants as m on (p.fkMerchantsId = m.id)
							WHERE 1 and p.`ItemType` in (1,3) and m.Status = 1 and p.`Status` in (1,2) group by p.`fkMerchantsId` HAVING totcount > 20 ";
			$producttot = 	R::getAll($sql);
			if($producttot) {
				$merchantsIdInArray = Array();
				foreach($producttot as $val) {
					$merchantsIdInArray[]		=	$val['fkMerchantsId'];
				}
				if(count($merchantsIdInArray) > 0)
					$merchantsIdIn = implode(',',$merchantsIdInArray);
			} else{
				/**
				* throwing error when no data found
				*/
				throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
			}			
			//Check for total products   - 03/11/2014
			if(!empty($merchantsIdIn)) {
				$wholediscount 	= " and fkMerchantsId in (".$merchantsIdIn.") ";
				$recently 		= " and id in (".$merchantsIdIn.") ";
				$merchantsIdIn 	= " and m.id in (".$merchantsIdIn.") ";				
			}	
			
			/* WholeMenu Discounted - Start */
			$wholetot		=	count($merchantsIdInArray);
			$tempresult 	=	$temp = Array();
			$sql			=	"SELECT fkMerchantsId,count(*) as TotalCount FROM products WHERE 1 and ItemType in (1,3) and Status in (1,2) and DiscountApplied = 0 ".$wholediscount." group by fkMerchantsId";
			$temp 			=	R::getAll($sql);	
			foreach($temp as $val) {
				if(in_array($val['fkMerchantsId'],$merchantsIdInArray))
					$wholetot--;
			}
			$tempresult['CategoryName']		=	'Whole Menu Discounted';
			$tempresult['CategoryIcon']		=	ADMIN_IMAGE_PATH_OTHER.'golden_tag.png';
			$tempresult['CategoryId']		=	0;
			$tempresult['MerchantCount']	=	$wholetot;
			$tempresult['CategoryType']		=	1;
			$CategoryArray[]				=	$tempresult;			
			/* WholeMenu Discounted - End */
			
			/* Recently Added - Start */
			$tempresult 	=	$temp = Array();
			$sql			=	"SELECT count(id) as Total FROM merchants WHERE 1 and Status = 1 ".$recently." and Date(DateCreated) between '".date('Y-m-d', strtotime('-30 days'))."' and '".date('Y-m-d')."'";
			$temp 			=	R::getAll($sql);			
			$tempresult['CategoryName']		=	'Recently Added';
			$tempresult['CategoryIcon']		=	ADMIN_IMAGE_PATH_OTHER.'new_tag.png';
			$tempresult['CategoryId']		=	0;
			$tempresult['MerchantCount']	=	$temp[0]['Total'];
			$tempresult['CategoryType']		=	2;
			$CategoryArray[]				=	$tempresult;			
			/* Recently Added - End */
			
			/* favourite - Start */
			$tempresult 	=	$temp	=	Array();
			$fav_ot			=	0;
			if(isset($bean->UserId) && !empty($bean->UserId)) {
				$sql		=	"SELECT count(distinct fkMerchantsId) as Total FROM favorites WHERE 1 ".$wholediscount." and fkUsersId = ".$bean->UserId." and FavouriteType=1";
				$temp 		=	R::getAll($sql);
				$fav_ot		=	$temp[0]['Total'];
				$tempresult['CategoryName']		=	'Favorites';
				$tempresult['CategoryIcon']		=	ADMIN_IMAGE_PATH_OTHER.'fav_tag.png';;
				$tempresult['CategoryId']		=	0;			
				$tempresult['MerchantCount']	=	$fav_ot;
				$tempresult['CategoryType']		=	3;
				$CategoryArray[]				=	$tempresult;
			}			
			/* favourite - End */
			
			$sql 	= 	"SELECT SQL_CALC_FOUND_ROWS c.CategoryName,c.CategoryIcon,c.id as CategoryId,count(mc.fkMerchantId) as MerchantCount from categories as c
							LEFT JOIN merchantcategories as mc ON(c.id = mc.fkCategoriesId)
							LEFT JOIN merchants AS m ON ( mc.fkMerchantId = m.id )
							where m.Status = 1 and c.Status = 1 ".$merchantsIdIn." GROUP BY c.id ORDER BY c.CategoryName asc";
		}
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
				$value['CategoryType']		=	4;
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
			$sql 		= "SELECT  c.id as CategoryId,c.CategoryName from productcategories as c where c.Status = 1 and c.fkMerchantId IN(".$bean->fkMerchantId.",0) ".$condition." and CategoryName = '".trim(addslashes($bean->CategoryName))."' ";
   			$existingCategory 	= R::getAll($sql);
	        if ($existingCategory) {
	            // if category name exist it wont insert
	            throw new ApiException("Category name already exist ", ErrorCodeType::CategoryAlreadyExist);
			}
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
		$sql		  	=	"update productcategories set Status ='3' where id = ".$categoryId."";
		R::exec($sql);
		
		$sql = "update products set Status ='3' where fkCategoryId in (".$categoryId.")";
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
	
	/**
	* Category Product count
	*/
	public function getCategoryProductCount()
    {
		/**
		* Get the identity of the merchant
		*/
		$sql 		= 	"SELECT  count(id) as total from products where Status != 3 and fkMerchantsId='".$this->merchantId."' and fkCategoryId='".$this->CategoryId."' ";
   		$products 	= 	R::getAll($sql);
        if ($products) {
            return $products[0]['total'];
        }
		return '0';
    }
	
	/**
	* get Category Analytics
	*/
	public function getCategoryAnalytics($merchantId)
    {
		/**
		* Get the bean
		* @var $bean Products
		*/
        $bean 		= 	$this->bean;
		$dataType	=	'day';
		$field		=	$condition	=	$condition2	=	$case = $leftjoin = '';
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
		$leftjoin	=	"left join carts as c ON (c.CartId = o.fkCartId)
						left join products as p on(p.id = c.fkProductsId)";
						//left join productcategories as pc on(pc.id = p.fkCategoryId )";
						
		$condition	.= 	"	and  o.OrderStatus IN (1) and o.fkMerchantsId =   ".$merchantId."  and o.Status = 1 and o.TransactionId != '' 
						and c.fkMerchantsId = ".$merchantId."  and p.fkMerchantsId =  ".$merchantId;
		$groupby 	= ",OrderDay";
		if($dataType == 'day') {
			$groupby 	= ",Date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE))";
			$condition	.=	" and Date(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$curr_date."'";
		} else if($dataType == '7days') {	 
			$dateDiffer	=	date('Y-m-d',strtotime("-7 days"));
			$condition	.= 	" and (DATE(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) <= '".$curr_date."' and DATE(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) > '".$dateDiffer."')";
		}else if($dataType == 'month') {	
			$dateDiffer		=	date('Y-m-d',strtotime("-30 days"));
			$condition	.= 	" and (Month(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_month."') and Year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_year."'";
		} else if($dataType == 'year') {	
			$condition	.= 	" and Year(DATE_ADD(o.OrderDate,INTERVAL '".$time_zone."' HOUR_MINUTE)) = '".$cur_year."' ";
		} 		
		
		$catresultIds	=	'0,';
		$categoryDet	=	Array();
		
		$sql 		=	"SELECT id,CategoryName  FROM productcategories WHERE fkMerchantId = ".$merchantId." AND Status = 1";
		$catresult	=	R::getAll($sql);		
		if($catresult) {
			foreach($catresult as $val) {
				$catresultIds				.=	$val['id'].',';
				$categoryDet[$val['id']]	=	$val['CategoryName'];
			}							
		}
		$catresultIds	=	trim($catresultIds,',');
		$sql	=	"SELECT SQL_CALC_FOUND_ROWS p.fkCategoryId as CategoryId, COUNT(o.id) as TotalOrders,p.id as ProductId,p.ItemName,p.Photo from orders as o
						".$leftjoin." where 1 ".$condition." and p.fkCategoryId in (".$catresultIds.") group by p.fkCategoryId order by TotalOrders desc limit $Start,8";
		$result				=	R::getAll($sql);		
		$total				=	R::getAll('SELECT FOUND_ROWS() as count ');
		$totalProducts		=	$total[0]['count'];	
		if($result) {
			$categoryIds	=	$CategoryArray	=	$outArray = Array();
			$outArray['totalCategory']	=	$totalProducts;
			$outArray['listedCategory']	=	count($result);
			foreach($result as $val) {
				$categoryIds[] 	=	$val['CategoryId'];
				
				$image_path = MERCHANT_SITE_IMAGE_PATH.'no_image.jpeg';	
				if(isset($val['Photo']) && $val['Photo'] != '')
					$image_path = PRODUCT_IMAGE_PATH.$val['Photo'];	
				
				if($val['CategoryId'] == 0)
					$name = "Specials";
				else 
					$name = $categoryDet[$val['CategoryId']];
					
				$CategoryArray[$val['CategoryId']]['CategoryId'] 	= 	$val['CategoryId'];
				$CategoryArray[$val['CategoryId']]['CategoryName'] 	= 	$name;
				$CategoryArray[$val['CategoryId']]['ProductName'] 	= 	$val['ItemName'];							
				$CategoryArray[$val['CategoryId']]['ProductImage'] 	= 	$image_path;							
			}
			if(count($categoryIds) > 0) {
				
				$sql	=	"SELECT SQL_CALC_FOUND_ROWS p.fkCategoryId as CategoryId,SUM(c.ProductsQuantity) as TotalQty,DATE_FORMAT(o.OrderDate,'%a') as OrderDay,SUM(c.DiscountPrice * c.ProductsQuantity) as TotalPrice, COUNT(o.id) as TotalOrders".$case." from orders as o
							".$leftjoin." where 1 ".$condition." and p.fkCategoryId in (".implode(',',$categoryIds).") group by p.fkCategoryId".trim($case," as period ")."".$groupby." order by TotalOrders desc";
				$CategorytList 	=	R::getAll($sql);
				if($CategorytList ){				
					$TempArray	=	Array();
					foreach($CategorytList as $value) {					
						if($value['period'] == 1) {							
							if(isset($TempArray[$value['CategoryId']]['Morning'])) {
								$TempArray[$value['CategoryId']]['Morning']['Amount'] 			= 	round($TempArray[$value['CategoryId']]['Morning']['Amount'] + $value['TotalPrice'],2);
								$TempArray[$value['CategoryId']]['Morning']['TotalOrder'] 		= 	$TempArray[$value['CategoryId']]['Morning']['TotalOrder'] + $value['TotalOrders'];
							}
							else {								
								$TempArray[$value['CategoryId']]['Morning']['Amount'] 		= 	round($value['TotalPrice'],2);
								$TempArray[$value['CategoryId']]['Morning']['TotalOrder'] 	= 	$value['TotalOrders'];
							}							
						}
						else if($value['period'] == 2) {
							if(isset($TempArray[$value['CategoryId']]['Noon'])) {
								$TempArray[$value['CategoryId']]['Noon']['Amount'] 				= 	round($TempArray[$value['CategoryId']]['Noon']['Amount'] + $value['TotalPrice'],2);
								$TempArray[$value['CategoryId']]['Noon']['TotalOrder'] 			= 	$TempArray[$value['CategoryId']]['Noon']['TotalOrder'] + $value['TotalOrders'];
							}
							else {								
								$TempArray[$value['CategoryId']]['Noon']['Amount'] 			= 	round($value['TotalPrice'],2);
								$TempArray[$value['CategoryId']]['Noon']['TotalOrder'] 		= 	$value['TotalOrders'];
							}
						}
						else if($value['period'] == 3) {
							if(isset($TempArray[$value['CategoryId']]['Evening'])) {
								$TempArray[$value['CategoryId']]['Evening']['Amount'] 			= 	round($TempArray[$value['CategoryId']]['Evening']['Amount'] + $value['TotalPrice'],2);
								$TempArray[$value['CategoryId']]['Evening']['TotalOrder'] 		= 	$TempArray[$value['CategoryId']]['Evening']['TotalOrder'] + $value['TotalOrders'];
							}
							else {								
								$TempArray[$value['CategoryId']]['Evening']['Amount'] 		= 	round($value['TotalPrice'],2);
								$TempArray[$value['CategoryId']]['Evening']['TotalOrder'] 	= 	$value['TotalOrders'];
							}							
						}
						if(isset($TempArray[$value['CategoryId']]['WeekList'][$value['OrderDay']]['TotalPrice'])) {
							$TempArray[$value['CategoryId']]['WeekList'][$value['OrderDay']]['TotalPrice']	=	round($TempArray[$value['CategoryId']]['WeekList'][$value['OrderDay']]['TotalPrice'] + $value['TotalPrice'],2);
						}
						else {
							$TempArray[$value['CategoryId']]['WeekList'][$value['OrderDay']]['OrderDay']	=	$value['OrderDay'];
							$TempArray[$value['CategoryId']]['WeekList'][$value['OrderDay']]['TotalPrice']	=	round($value['TotalPrice'],2);
						}
					}
					foreach($TempArray as $key=>$value) {
						$mqty = $nqty = $eqty = 0;
						if(isset($value['Morning'])) {
							$CategoryArray[$key]['Morning'] 	= 	$value['Morning'];
							$mqty							=	$value['Morning']['Amount'];
						}
						if(isset($value['Evening'])) {
							$CategoryArray[$key]['Evening'] 	= 	$value['Evening'];
							$eqty							=	$value['Evening']['Amount'];
						}
						if(isset($value['Noon'])) {
							$CategoryArray[$key]['Noon'] 	= 	$value['Noon'];
							$nqty							=	$value['Noon']['Amount'];
						}
						if(isset($value['WeekList']))
							$CategoryArray[$key]['WeekList'] 	= 	array_values($value['WeekList']);
						
						$tot	=	$mqty + $nqty + $eqty;
						if($tot > 0) {
							if(isset($CategoryArray[$key]['Morning']['Amount']) && $CategoryArray[$key]['Morning']['Amount'] > 0)
								$CategoryArray[$key]['Morning']['Percentage']	=	round(($mqty/$tot) * 100,2);
							if(isset($CategoryArray[$key]['Evening']['Amount']) && $CategoryArray[$key]['Evening']['Amount'] > 0)
								$CategoryArray[$key]['Evening']['Percentage']	=	round(($eqty/$tot) * 100,2);
							if(isset($CategoryArray[$key]['Noon']['Amount']) && $CategoryArray[$key]['Noon']['Amount'] > 0)
								$CategoryArray[$key]['Noon']['Percentage']	=	round(($nqty/$tot) * 100,2);
						}						
					}
				}
				$outArray['result'] = array_values($CategoryArray);
				return $outArray;
			}
		}
	}
}