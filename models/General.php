<?php

/**
 * Description of General
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


class General extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Constructor
	*/
    public function __construct() {

    }

	/**
	* get job type
	*/
    public function getStaticPages()
    {
		/**
		* Query to get static contents
		*/
		$sql	 	=	"SELECT PageName,Content FROM staticpages";
   		$result 	= 	R::getAll($sql);
		if(is_array($result) && count($result) >0){
			/**
			* The users were found
			*/
			foreach($result as $key=>$value){
					$content    = array("PageName"			=>		$value['PageName'],
										"Content"			=>		ucfirst($value['Content']),
										);
					$StaticArray[]	=	$content;
			}
			return $StaticArray;
		}
		else{
			/**
			* throwing error when static data
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	public function getSliderImages($type)
    {
		/**
		* Query to get static contents
		*/
		$sql 		= 	"SELECT * from sliderimages where Status = 1 ORDER BY `Order` ASC";
   		$result 	= 	R::getAll($sql);
		if(is_array($result) && count($result) >0){
			/**
			* The users were found
			*/
			foreach($result as $key=>$value){
				if($value['SliderType'] == $type){
					$image_path 	= 	SLIDER_IMAGE_PATH.$value['SliderImages'];
					$content    	= 	array("Order"			=>		$value['Order'],
											"ImageUrl"		=>		$image_path,
											);
					$SliderArray[]	=	$content;
				}
			}
			return $SliderArray;
		}
		else{
			/**
			* throwing error when static data
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
	* get web-page content
	*/
    public function getWebPageContent($url)
    {
		/**
		* Query to get web-page content
		*/
		$sql 		= 	"SELECT PageName,Content FROM webcontent where PageUrl='".$url."'";
   		$result 	= 	R::getAll($sql);
		if(is_array($result) && count($result) >0){
			/**
			* The Contents were found
			*/
			$content    = array("PageName"			=>		ucfirst($result[0]['PageName']),
								"Content"			=>		ucfirst($result[0]['Content']),
								);
			return $content;
		}
		else{
			/**
			* throwing error when Content not Found
			*/
			throw new ApiException("Content not Found", ErrorCodeType::NoResultFound);
		}
	}
	
	public function create(){ 
		/**
		* Get the bean
		*/
        $bean 			= 	$this->bean;
		
		// validate the parameters
        $this->validate();
		
		// validate create
        $this->validateCreate();		
		
    }
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate()
    {
	
	}
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		*/
        $bean 	= 	$this->bean;
		
	}
}