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
					$str = str_replace("\r\n","<br>",trim($value['Content']));
					$content    = array("PageName"			=>		$value['PageName'],
										"Content"			=>		ucfirst(nl2br($str)),
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
	
	/**
	* get countries List
	*/
    public function getcountries()
    {
		/**
		* Query to get countries
		*/
		$sql 		= 	"SELECT * FROM locations where fkCurrencyId != 0 and Status='1' order by  Location asc";
   		$result 	= 	R::getAll($sql);
		if(is_array($result) && count($result) >0){
			$countries	=	array();
			/**
			* The countries were found
			*/
			foreach($result as $val)
				$countries[$val['id']]	=	$val;
			return $countries;
		}
		else{
			/**
			* throwing error when countries not Found
			*/
			throw new ApiException("Countries not Found", ErrorCodeType::NoResultFound);
		}
	}
	
	/**
	* get currencies List
	*/
    public function getcurrencies()
    {
		/**
		* Query to get currencies
		*/
		$sql 		= 	"SELECT * FROM currencies where Status='1'";
   		$result 	= 	R::getAll($sql);
		if(is_array($result) && count($result) >0){
			$currencies	=	array();
			/**
			* The currencies were found
			*/
			foreach($result as $val)
				$currencies[$val['fkLocationId']]	=	$val;
			return $currencies;
		}
		else{
			/**
			* throwing error when currencies not Found
			*/
			throw new ApiException("Currencies not Found", ErrorCodeType::NoResultFound);
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
     * get & check version, build
     */
    public function checkCurrentVersion(){
		
		// validate the model
        $this->validate();
		
		$bean 		= 	$this->bean;
		$device 	= 	$bean->device_type;
		$status 	= 	$bean->app_type;
		$version 	= 	$bean->version;
		$build 		=	$bean->build;
		
		$app_exists = 	R::findOne('appversions','device_type =? and app_type=? order by id desc ',array($device,$status ));
		if($app_exists){
			if($version == $app_exists->version && $build == $app_exists->build){
				$message 		= 	'Up to date';
				$message_code 	= 	1;
			}
			else if($version <= $app_exists->version && $build <= $app_exists->build){
				$message_code 	= 	2;
				$message 		= 	'You are using an older version '.$version.(($build!='')?' build '.$build:'');
				$message 	   .= 	' - New version '.$app_exists->version.(($app_exists->build!='')?' build '.$app_exists->build:'');
			}
			else if($version < $app_exists->version){
				$message_code 	= 	2;
				$message 		=  	'You are using an older version '.$version.(($build!='')?' build '.$build:'');
				$message 		.= 	' - New version '.$app_exists->version.(($app_exists->build!='')?' build '.$app_exists->build:'');
			}
			else{
				$message 		= 	'Invalid version detail';
				$message_code 	= 	3;
			}
			$result['status_code'] = $message_code;
			$result['message'] 	= $message;
			return $result;
		}
		else{
			return false;
		}
	}
	
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate()
    {
		$bean 	=	 $this->bean;
		$rules 	= 	[
						'required' => [
							['version'],['build'],['device_type'],['app_type']
						],
					];
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check version, build, device_type, app_type" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }		
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
	/**
	* get legal content
	*/
    public function getLegalContent()
    {
		/**
		* Query to get legal contents
		*/
		$sql	 	=	"SELECT PageName,Content FROM webcontent where `PageUrl` IN('terms_of_service','privacy_policy','cookie_policy','merchant_agreement','user_agreement') and Status = 1 order by id ";
		$result 	= 	R::getAll($sql);
		$content = '';
		if(is_array($result) && count($result) >0){
			/**
			* The result were found
			*/
			foreach($result as $key=>$value){
				if($value['Content'] != ''){
					$content  .= '<h1>'.ucfirst($value['PageName']).'</h1>';
					$content .= '<p>'.ucfirst($value['Content']).'</p>';
					
				}
			}
			return $content;
		}
		else{
			/**
			* throwing error when static data
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
		}
	}
}