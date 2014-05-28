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


class Model_Categories extends RedBean_SimpleModel {

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
     * get category details
     */
    
	public function getCategoryDetails()
    {
		/**
         * Query to get category list
         */
		$sql 		= "SELECT SQL_CALC_FOUND_ROWS c.CategoryName,c.CategoryIcon,c.id as CategoryId,count(mc.fkMerchantId) as MerchantCount from categories as c
					   LEFT JOIN merchantcategories as mc ON(c.id = mc.fkCategoriesId) where c.Status = 1 GROUP BY c.id ORDER BY c.CategoryName asc";
   		$result 	= R::getAll($sql);
		$totalRec 	= R::getAll('SELECT FOUND_ROWS() as count ');
		$total 		= (integer)$totalRec[0]['count'];
		if($result){
			/**
             * The categories were found
             */
				foreach($result as $key=>$value){
					$image_path = '';
					if(isset($value['CategoryIcon']) && $value['CategoryIcon'] != ''){
						if (!SERVER){
							if(file_exists(CATEGORY_IMAGE_PATH_REL.$value['CategoryIcon'])){
								$image_path = CATEGORY_IMAGE_PATH.$value['CategoryIcon'];
							}
						}
						else{
							if(image_exists(3,$value['CategoryIcon']))
								$image_path = CATEGORY_IMAGE_PATH.$value['CategoryIcon'];
						}
					}
					$value['CategoryIcon']		=	$image_path;
					$CategoryArray[]			=   $value;
				}
				$CategoryArray['result'] 		= $CategoryArray;
				$CategoryArray['totalCount']	= $total;
				return $CategoryArray;
		}
		else{
			 /**
	         * throwing error when no data found
	         */
			  throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	public function deleteCategories($mercahntId)
    {
		/**
         * Query to delete merchant categories
         */
		$sql 		= "DELETE FROM merchantcategories where fkMerchantId = ".$mercahntId."";
   		$result 	= R::exec($sql);
	}
}