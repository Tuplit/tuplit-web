<?php

/**
* Merchants endpoint
* /v1/merchants
*
* @author 
*/

/**
* Load configuration
*/
require_once('../../config.php');
require_once '../../admin/includes/CommonFunctions.php';
require_once '../../admin/includes/phmagick.php';

/**
* Load models
*/
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../models/Merchants.php';                 	// merchant model
require_once '../../models/Orders.php';                 	// orders model
require_once '../../models/Categories.php';                 // category model
require_once '../../models/Favorites.php';


/**
* Library objects
*/
use RedBean_Facade as R;
use Helpers\RedBeanHelper as RedBeanHelper;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Exceptions\ApiException as ApiException;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
/**
* Initialize application
*/
tuplitApi::init(true);
$app = new \Slim\Slim();


/**
* New merchant creation
* POST /v1/merchants
*/
$app->post('/', function () use ($app) {

    try {

        // Create a http request
        $req = $app->request();

		/**
		* Get a new merchant account
		* @var Merchants $merchant
		*/
        $merchant 					= R::dispense('merchants');
		$merchant->FirstName 		= $req->params('FirstName');
		$merchant->LastName 		= $req->params('LastName');
		$merchant->Email 			= $req->params('Email');
		$merchant->PhoneNumber		= $req->params('PhoneNumber');
		$merchant->BusinessName		= $req->params('BusinessName');
		$merchant->BusinessType		= $req->params('BusinessType');
		$merchant->CompanyName		= $req->params('CompanyName');
		$merchant->RegisterCompanyNumber	= $req->params('RegisterCompanyNumber');
		$merchant->Address			= $req->params('Address');
		$merchant->Country			= $req->params('Country');
		$merchant->PostCode			= $req->params('PostCode');
		$merchant->Password 		= $req->params('Password');
		$merchant->Currency 		= $req->params('Currency');
		$merchant->HowHeared 		= $req->params('HowHeared');
		$merchant->BrowserDetails	= $_SERVER['HTTP_USER_AGENT'];
		
		/**
		* Create the merchant account
		*/
	    $merchantId = $merchant->create();
		if($merchantId) {			
			for($i=0;$i<=6;$i++) {
				$opening	= R::dispense('merchantshoppinghours');
				$opening->fkMerchantId 	= $merchantId;
				$opening->OpeningDay 	= $i;
				$opening->DateType 		= 0;
				// save the bean to the database
				R::store($opening);
			}
		}
		
		/**
		* After successful registration email was sent to registered merchant
		*/
		$adminDetails 						=   R::findOne('admins', 'id=?', ['1']);
		$adminMail							=	$adminDetails->EmailAddress;
		$mailContentArray['fileName']		=	'merchantregistration.html';
		$mailContentArray['from']			=	$adminMail;
		$mailContentArray['toemail']		= 	$req->params('Email');
		$mailContentArray['subject']		= 	"Registration";
		$mailContentArray['email']	    	=	$req->params('Email');
		$mailContentArray['name']			=	ucfirst($req->params('FirstName').' '.$req->params('LastName'));
		$mailContentArray['password']		=	$req->params('Password');
		
		sendMail($mailContentArray,5); 
		
		/**
		* After successful registration email was sent to admin for approve registered merchant
		*/
		$adminDetails 							=   R::findOne('admins', 'id=?', ['1']);
		$adminMail								=	$adminDetails->EmailAddress;
		$mailAdminContentArray['fileName']		=	'adminmerchantregistration.html';
		$mailAdminContentArray['from']			=	$adminMail;
		$mailAdminContentArray['toemail']		= 	$adminMail;
		$mailAdminContentArray['subject']		= 	"Merchant Approval";
		$mailAdminContentArray['merchantName']	=	ucfirst($req->params('FirstName').' '.$req->params('LastName'));
		$mailAdminContentArray['merchantEmail']	=	$req->params('Email');
		$mailAdminContentArray['link']			=	'/admin/MerchantList?cs=1&status=0';
		sendMail($mailAdminContentArray,6); 
		
		
		/**
		* New merchant creation was made success
		*/
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'merchant';
		/**
		* returning upon repsonse of new merchant creation
		*/
        $merchantDetails 				= RedBeanHelper::export($merchant->unbox(),['IpAddress','Status','DateModified','DateCreated','Password','BrowserDetails']);
		$merchantDetails['MerchantId'] 	= $merchantDetails['id'];
		$response->returnedObject 		= $merchantDetails;
        $response->addNotification('Merchant has been created successfully');
        echo $response;
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
* Update merchant details
* PUT /v1/merchants
*/
$app->put('/', tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $request 			= 	$app->request();
		$iconExist 			= 	$merchantExist = '';
    	$body 				= 	$request->getBody();
    	$input 				= 	json_decode($body); 
		$merchantId 		= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* @var Merchants $merchant
		*/
        $merchant 								= 	R::dispense('merchants');
		$merchant->id		 					= 	$merchantId;		
		if(isset($input->Email)) 				
				$merchant->Email 				= 	$input->Email;
		if(isset($input->CompanyName))			
				$merchant->CompanyName 			= 	$input->CompanyName;
		if(isset($input->PhoneNumber)) 			
				$merchant->PhoneNumber 			= 	$input->PhoneNumber;
		if(isset($input->WebsiteUrl)) 			
				$merchant->WebsiteUrl 			= 	$input->WebsiteUrl;
		if(isset($input->ShortDescription)) 	
				$merchant->ShortDescription 	= 	$input->ShortDescription;
		if(isset($input->Description)) 			
				$merchant->Description 			= 	$input->Description;
		if(isset($input->DiscountTier)) 			
				$merchant->DiscountTier 		= 	$input->DiscountTier;
		if(isset($input->PriceRange)) 			
				$merchant->PriceRange 			= 	$input->PriceRange;
		if(isset($input->IconExist)) 			
				$iconExist 						= 	$input->IconExist;
		if(isset($input->MerchantExist)) 			
				$merchantExist 					= 	$input->MerchantExist;
		if(isset($input->DiscountType)) 			
				$merchant->DiscountType 		= 	$input->DiscountType;
		if(isset($input->DiscountProductId)) 			
				$merchant->DiscountProductId 	= 	$input->DiscountProductId;
		if(isset($input->Street)) 				
				$merchant->Street 				= 	$input->Street;
		if(isset($input->City)) 			
				$merchant->City 				= 	$input->City;
		if(isset($input->State)) 			
				$merchant->State 				= 	$input->State;	
		if(isset($input->ZipCode)) 			
				$merchant->PostCode 			= 	$input->ZipCode;		
		if(isset($input->Country)) 			
				$merchant->LocationCountry		= 	$input->Country;	
		if(isset($input->FBId)) 			
				$merchant->FBId 				= 	$input->FBId;	
		if(isset($input->TwitterId)) 			
				$merchant->TwitterId			= 	$input->TwitterId;	
		if(isset($input->IconPhoto)  && $input->IconPhoto != '') {
			$imageName 				= $merchant->id . '_' . time() . '.png';
			$imagePath 				= UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL.$imageName;
			if ( !file_exists(UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL) ){
		  		mkdir (UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL, 0777);
			}
			
			imagethumb_addbg($input->IconPhoto, $imagePath,'','',100,100);
			if(SERVER) {
				//newly added
				if($iconExist != ''){
					$icon_path = basename($iconExist);
					if($icon_path != '')
						deleteImages(6,$icon_path);
				}
				uploadImageToS3($imagePath,6,$imageName);
				unlink($imagePath);
			}
			$merchant->Icon 	= 	$imageName;
			$iconExist      	=  	$imageName;
		}
		if(isset($input->MerchantPhoto) && $input->MerchantPhoto != '') {
			$coverImageName 	= 	$merchant->id . '_' . time() . '.png';
			$coverImagePath 	= 	UPLOAD_MERCHANT_IMAGE_PATH_REL.$coverImageName;
			if ( !file_exists(UPLOAD_MERCHANT_IMAGE_PATH_REL) ){
		  		mkdir (UPLOAD_MERCHANT_IMAGE_PATH_REL, 0777);
			}	
			imagethumb_addbg($input->MerchantPhoto, $coverImagePath,'','',640,260);
			if(SERVER) {
				//newly added
				if($merchantExist != ''){
					$cimage_path = basename($merchantExist);
					if($cimage_path != '')
						deleteImages(7,$merchantExist);
				}
				uploadImageToS3($coverImagePath,7,$coverImageName);
				unlink($coverImagePath);
			}
			$merchant->Image 	= 	$coverImageName;
			$merchantExist 		=	$coverImageName;
		}
		
	
		/**
		* update the merchant account
		*/
	    $merchantId1 			= 	$merchant->modify($merchantId,$iconExist,$merchantExist);
		
		/***
		* update the opening hours
		*/
		if(isset($input->OpeningHours)){
			foreach($input->OpeningHours as $key1=>$value) {				
				$value 				= (array)$value;
				$hours				= R::dispense('merchantshoppinghours');				
				$hours->id			= $value['id'];
				$hours->OpeningDay	= $key1;
				$hours->Start		= $value['Start'];
				$hours->End			= $value['End'];
				$hours->DateType	= $value['DateType'];
				R::store($hours);
			}
		}
		if(isset($input->Categories)){
			$categories 		= 	R::dispense('categories');		
	 		$deleteCategory 	=  	$categories->deleteCategories($merchant->id,$input->Categories);
		 	$categoryArray		=	explode(',',$input->Categories);
		 	if(isset($categoryArray)){
			 	foreach($categoryArray as $key=>$val){
					$existingCategory = R::findOne('merchantcategories', 'fkMerchantId = ? and fkCategoriesId = ?', array($merchant->id,$val));
					if(!$existingCategory) {
						$merchantCategory 					= 	R::dispense('merchantcategories');
						$merchantCategory['fkMerchantId'] 	= 	$merchant->id;
						$merchantCategory['fkCategoriesId'] = 	$val;
						$merchantCategory['DateCreated'] 	= 	date('Y-m-d H:i:s');
						R::store($merchantCategory);
					}
				}
			}
		}
		
		//storing slideshow pictures
		if(isset($input->ImageCount)  && !empty($input->ImageCount) && isset($input->TotalImageExt)  && !empty($input->TotalImageExt) ) {
			$ImageExt	=	explode(',',$input->TotalImageExt);
			$uploadimg	=	explode(',',$input->ImageCount);
			if(is_array($ImageExt) && count($ImageExt) > 0 && is_array($uploadimg) && count($uploadimg) > 0) {				
				foreach($uploadimg as $key=>$upval) {
					if(!empty($upval)) {
						$mystoreImageName	=	$mystoreImagePath	=	$tempPath	=	'';
						$tempPath			=	TEMP_PRODUCT_IMAGE_PATH_UPLOAD.$merchant->id.'_'.$upval.'_myStore.'.$ImageExt[$key];
						$mystoreImageName 	= 	$upval.'_'.time().'.png';
						$mystoreImagePath 	= 	UPLOAD_MERCHANT_IMAGE_PATH_REL.$merchant->id.'/'.$mystoreImageName;
						if ( !file_exists(UPLOAD_MERCHANT_IMAGE_PATH_REL.$merchant->id.'/') ){
							mkdir (UPLOAD_MERCHANT_IMAGE_PATH_REL.$merchant->id.'/', 0777);
						}
						imagethumb_addbg($tempPath, $mystoreImagePath,'','',1000,350);
						if(SERVER) {
							if($mystoreImagePath != ''){
								$cimage_path = basename($mystoreImagePath);
								if($cimage_path != '')
									deleteImages(7,$merchant->id.'/'.$mystoreImagePath);
							}
							uploadImageToS3($mystoreImagePath,7,$merchant->id.'/'.$mystoreImageName);
							unlink($mystoreImagePath);
						}
						$merchantSlideshow 					= 	R::dispense('merchantslideshow');
						$merchantSlideshow->fkMerchantId 	= 	$merchant->id;
						$merchantSlideshow->SlideshowName 	= 	$mystoreImageName;
						$merchantSlideshow->DateCreated 	= 	date('Y-m-d H:i:s');
						R::store($merchantSlideshow);
						unlink($tempPath);
					}
				}
			}
		}	

		//deleting slideshow pictures
		if(isset($input->DeleteIds)  && !empty($input->DeleteIds) && isset($input->DeleteImg)  && !empty($input->DeleteImg) ) {
			$delmerchant				= 	R::dispense('merchants');
			$delmerchant->merchantId	= 	$merchantId;
			$delmerchant->DeleteIds		= 	$input->DeleteIds;
			$delstatus 					=	$delmerchant->deleteSlideshow();
			if($delstatus) {
				$deleteimages			=	explode(',',$input->DeleteImg);
				if(is_array($deleteimages) && count($deleteimages) > 0) {  
					foreach($deleteimages as $delnam) {
						if(SERVER) {
							if(image_exists(7,$merchant->id.'/'.$delnam))
								deleteImages(7,$merchant->id.'/'.$delnam);
						} else {
							unlink(UPLOAD_MERCHANT_IMAGE_PATH_REL.$merchantId.'/'.$delnam);
						}
					}
				}
			}
		}
		
		/**
		* merchant updated was made success
		*/
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'merchant';
        $response->addNotification('Merchant detail has been updated successfully');
        echo $response;
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
* Forgot password
* get /v1/merchants/forgotpassword
*/
$app->get('/forgetPassword', function () use ($app) {

    try {
		// Create a http request
        $req 				= 	$app->request();
		$response 			= 	new tuplitApiResponse();
        $merchant 			= 	R::dispense('merchants');
		$merchant->Email 	= 	$req->params('Email');
		$merchantDetails 	= 	$merchant->forgotPassword();
		/**
         * Send mail to merchant
         */
		 if($merchantDetails){
		 		$merchantDetails 				= 	$merchantDetails[0];
			if($merchantDetails['OrderMail'] == 1) {
				$adminDetails 					=   R::findOne('admins', 'id=?', ['1']);
				$adminMail						=	$adminDetails->EmailAddress;
				$mailContentArray['fileName']	=	'userForgotPasswordMail.html';
				$mailContentArray['from']		=	$adminMail;
				$mailContentArray['toemail']	= 	trim($merchantDetails['Email']);
				$mailContentArray['subject']	= 	"Forgot Password";
				$mailContentArray['name']		=	ucfirst($merchantDetails['FirstName'].' '.$merchantDetails['LastName']);
				$mailContentArray['link']		=	'/ResetPassword.php?UID='.encode($merchantDetails['id']).'&Type=2';
				sendMail($mailContentArray,4); //Send mail - Updated password Details     		
				$content	=	array("status"	    =>	"Success",
									  "message"  	=>	"An email has been sent to you with link to reset your password.");
				$response->returnedObject = $content;
				$response->setStatus(HttpStatusCode::Created);
				$response->meta->dataPropertyName = 'merchant';
				echo $response;
			} else {
				// Error occurred 
				throw new ApiException("Sorry! your order mail status is off." ,  ErrorCodeType::ErrorInUpdateForgetPassword);
			}
		}
		else{
			// Error occurred 
			throw new ApiException("Sorry! You cannot use this link again" ,  ErrorCodeType::ErrorInUpdateForgetPassword);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
* Get merchant details
* GET /v1/merchants
*/
$app->get('/:merchantId',  function ($merchantId) use ($app) {

    try {
		$from			= 	1;
		$userId 		= 	'';
        // Create a http request
        $request = $app->request();
		$requestedById 	= 	tuplitApi::$resourceServer->getOwnerId();	
    	
		if($request->params('From') != '') {
			$from	 	= 	$request->params('From');
		}
		if($request->params('UserId') != ''){
			$userId	 	= 	$request->params('UserId');
		}
		
		if($request->params('Platform')){
			$platformText = $request->params('Platform');
		}
		else{
			$platformText = 'web';
		}
        $merchant 				= R::dispense('merchants');		
		$merchant->From		 	= $from;
		$merchant->UserId	 	= $userId;		
		$merchant->Platform	 	= $platformText;
		if($request->params('Latitude'))
			$merchant->Latitude		= $request->params('Latitude');
		
		if($request->params('Longitude'))
			$merchant->Longitude	= $request->params('Longitude');
		$merchantDetails			= $merchant->getMerchantsDetails($merchantId);       
		if($merchantDetails){
			$icon_image_path = $image_path = '';
			if(isset($merchantDetails['merchantDetails']['Icon']) && $merchantDetails['merchantDetails']['Icon'] != ''){
				$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchantDetails['merchantDetails']['Icon'];				
			}
			$merchantDetails['merchantDetails']['Icon'] = $icon_image_path;
			if(isset($merchantDetails['merchantDetails']['Image']) && $merchantDetails['merchantDetails']['Image'] != ''){
				$image_path = MERCHANT_IMAGE_PATH.$merchantDetails['merchantDetails']['Image'];
			}
			$merchantDetails['merchantDetails']['Image'] = $image_path;
			/**
			* merchant details retrieved successfully
			*/
	        $response = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'merchant';
			if(isset($merchantDetails['AllowCart']))
				$response->meta->AllowCart 		= $merchantDetails['AllowCart'];
			$response->meta->CurrentTime 		= date('Y-m-d H:i:s');
			/**
	        * returning upon response of merchant details
			*/
			$response->returnedObject = $merchantDetails['merchantDetails'];			
	        $response->addNotification('Merchant details has been retrieved successfully');
	        echo $response;
		}
		else {
			/** 
			* Merchant not found
			*/
			throw new ApiException("Merchant not found", ErrorCodeType::NoResultFound);
		}

    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});
/**
 * CheckReset password
 * POST /v1/merchants/forgotpassword
 */
$app->get('/checkResetPassword/:userId', function ($userId) use ($app) {

    try {
		// Create a http request
        $req 				= $app->request();
		$response 			= new tuplitApiResponse();		
		$merchants 			= R::dispense('merchants');		
		$merchantsDetails 	= $merchants->checkResetPassword($userId);		
		
		if($merchants){
			$response->setStatus(HttpStatusCode::Created);
        	$response->meta->dataPropertyName = 'user';
			$response->addNotification('You are allowed to reset password.');			
			echo $response;
		}
		else{
			throw new ApiException("Sorry! You cannot use this link again" ,  ErrorCodeType::PasswordResetNotValid);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
 * resetPassword password
 * POST /v1/merchants/resetPassword
 */
$app->put('/resetPassword', function () use ($app) {

    try {
		// Create a http request
        $request 	= 	$app->request();
    	$body 		= 	$request->getBody();
    	$input 		= 	json_decode($body); 
	    $response 	= 	new tuplitApiResponse();
		$merchant 	= 	R::dispense('merchants');
		
		if(isset($input->MerchantId)) 	$merchant->MerchantId 		= $input->MerchantId;
		if(isset($input->Password)) 	$merchant->Password 		= $input->Password;
		if(isset($input->OldPassword)) 	$merchant->OldPassword 		= $input->OldPassword;
		
		$merchantId	= 	$merchant->updatePassword();
		/**
         * Send mail to registered merchant
         */
		if($merchantId != ''){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'merchant';
			$response->addNotification('Password updated successfully.');
			echo $response;
		}
		else{
			// Error occurred while resetting password
			throw new ApiException("Error in resetting password" ,  ErrorCodeType::ErrorInUpdateForgetPassword);
		}
		

    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});


/**
 * Merchants Favorites(Like/Unlike)
 * POST /v1/merchants/favorites
 */
$app->post('/favorites',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req 			= 	$app->request();		
		$requestedById 	= 	tuplitApi::$resourceServer->getOwnerId();
		$userType 		= 	tuplitApi::$resourceServer->getOwnerType();
		if($userType == 'merchant') {
			throw new ApiException("Sorry! Only users can favorite the merchants." ,  ErrorCodeType::AlreadyFavoured);
		}
		
		// Create a json response object
        $response 		= 	new tuplitApiResponse();
	   
		/**
         * Get a favorites table instance
         */
        $favorite 					= 	R::dispense('favorites');
		$favorite->MerchantId 		= 	$req->params('MerchantId');
		$favorite->UsersId 	 		= 	$requestedById;
		$favorite->FavouriteType	= 	$req->params('FavouriteType');
		$favoritemsg				= 	$favorite->create();
		if($favoritemsg != ''){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'favorites';
			$response->addNotification($favoritemsg);			
			echo $response;
		}
		else{
			// Error occurred while saving favorites
			throw new ApiException("Error in saving favorites." ,  ErrorCodeType::ErrorInSaving);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});


/**
 * Get merchant list
 * GET /v1/merchants
 */
$app->get('/', function () use ($app) {

    try {

        // Create a http request
      	$req = $app->request();

		$longitude = $latitude =  $type = $discountTier = $search = $category = '';
		$start	= $type = 0;
		
		if($req->params('Latitude'))
			$latitude 		= $req->params('Latitude');
		if($req->params('Longitude') )
			$longitude 		= $req->params('Longitude');
		if($req->params('Type'))
			$type	 		= $req->params('Type');
		if($req->params('Start') !='')
			$start 			= $req->params('Start');
		if($req->params('SearchKey') !='')
			$search 		= addSlashes($req->params('SearchKey'));
		if($req->params('DiscountTier') !='')
			$discountTier 	= $req->params('DiscountTier');
		if($req->params('Category') !='')
			$category 	= $req->params('Category');
		/**
         * Get a merchant table instance
         */
        $merchant 					= R::dispense('merchants');
		$merchant->Latitude 		= $latitude;
		$merchant->Longitude	 	= $longitude;
		$merchant->Start	 		= $start;
		$merchant->Type		 		= $type;
		$merchant->SearchKey 		= $search;
		$merchant->DiscountTier		= $discountTier;
		$merchant->Category			= $category;
		
	    $merchantList 				= $merchant->getMerchantList();
		if($merchantList){
			// Create a json response object
      		$response 	   = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'merchantDetails';
			$response->meta->totalCount = $merchantList['totalCount'];
			$response->meta->listedCount = $merchantList['listedCount'];
			$response->returnedObject = $merchantList['result'];
			echo $response;
		}
		else{
			 /**
	         * throwing error when no data found
	         */
			  throw new ApiException("No merchants Found", ErrorCodeType::NoResultFound);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
 * get Customer list 
 * GET /v1/merchants/customerList/
 */
$app->get('/customerList/',tuplitApi::checkToken(), function () use ($app) {

    try {
		 $req 			= 	$app->request();		
		 $merchantId 	= 	tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response 		= 	new tuplitApiResponse();
		/**
         * Get a merchants table instance
         */
        $merchant 		= 	R::dispense('merchants');
		if($req->params('UserName') )
			$merchant->UserName 		= 	$req->params('UserName');
		if($req->params('TotalOrders') )
			$merchant->TotalOrders 		= 	$req->params('TotalOrders');
		if($req->params('TotalPrice') )
			$merchant->TotalPrice 		= 	$req->params('TotalPrice');
		if($req->params('Start') !='')
			$merchant->Start 			= 	$req->params('Start');
		if($req->params('Limit') !='')
			$merchant->Limit 			= 	$req->params('Limit');
		if($req->params('Type') !='')
			$merchant->Type 			= 	$req->params('Type');
		if($req->params('FromDate') !='')
			$merchant->FromDate 		= 	$req->params('FromDate');
		if($req->params('ToDate') !='')
			$merchant->ToDate 			= 	$req->params('ToDate');
		$merchant->MerchantId 			=  	$merchantId;
	 	$customerList 					=  	$merchant->getCustomerList($merchantId);
		if($customerList){
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'CustomerList';		
			$response->meta->totalCount 		= 	$customerList['totalCount'];
			$response->meta->listedCount 		= 	$customerList['listedCount'];
			$response->returnedObject 			= 	$customerList['result'];	
			echo $response;
		}
		else{
			 /**
	         * throwing error when no customers found
	         */
			  throw new ApiException("No customers found", ErrorCodeType::NoResultFound);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});


/**
 * get transaction list 
 * GET /v1/merchants/transaction/
 */
$app->get('/transaction/',tuplitApi::checkToken(), function () use ($app) {
    try {
		$userName = $visitCount = $totalSpend = $start =  $limit = '';
		$req 			= 	$app->request();		
		$merchantId 	= 	tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response 		= 	new tuplitApiResponse();
		
		/**
         * Get a orders table instance
         */
        $orders 	= 	R::dispense('orders');
		if($req->params('Start') !='')		$orders->Start 		=  $req->params('Start');
		if($req->params('Limit') !='')		$orders->Limit 		=  $req->params('Limit');
		if($req->params('DataType') !='')	$orders->DataType	=  $req->params('DataType');
		if($req->params('StartDate') !='')	$orders->StartDate	=  $req->params('StartDate');
		if($req->params('EndDate') !='')	$orders->EndDate	=  $req->params('EndDate');
		
		$orders->MerchantId 			=  $merchantId;
		$transactionList				=  $orders->getTransactionList($merchantId);
		if($transactionList){
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'TransactionList';		
			$response->returnedObject 			= $transactionList['result'];	
			echo $response;
		}
		else{
			 /**
	         * throwing error when no transaction found
	         */
			  throw new ApiException("No transactions found", ErrorCodeType::NoResultFound);
		}
		
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
 * Merchants Connect(Mangopay)
 * POST /v1/merchants/connect
 */
$app->post('/connect',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req 			= 	$app->request();		
		$requestedById 	= 	tuplitApi::$resourceServer->getOwnerId();
		
		// Create a json response object
        $response 		= 	new tuplitApiResponse();
		
		/**
         * Get a merchants table instance
         */
		$merchants 				= R::dispense('merchants');		
		
		if($req->params('CompanyName'))
	    	$merchants->CompanyName		=	$req->params('CompanyName');
		if($req->params('FirstName'))
			$merchants->FirstName		=	$req->params('FirstName');
		if($req->params('LastName'))
			$merchants->LastName		=	$req->params('LastName');
		if($req->params('Email'))
			$merchants->Email			=	$req->params('Email');
		if($req->params('Address'))
			$merchants->Address			=	$req->params('Address');
		if($req->params('Country'))
			$merchants->Country			=	$req->params('Country');
		if($req->params('Currency'))
			$merchants->Currency		=	$req->params('Currency');
		if($req->params('Birthday'))
			$merchants->Birthday		=	$req->params('Birthday');
			
		$mangopayDetails				=   $merchants->addMangoPayDetails($requestedById);
		if($mangopayDetails){
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'MangoPay Account';	
			//$response->returnedObject 			= $mangopayDetails;	
			$response->addNotification('Mango Pay account has been created successfully');
       		echo $response;
		}
		
    
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
 * get product transaction 
 * GET /v1/merchants/productAnalysis/
 */
$app->get('/productAnalysis/',tuplitApi::checkToken(), function () use ($app) {
    try {
		$userName = $visitCount = $totalSpend = $start =  $limit = '';
		$productAnalysis	=	array();
		$req 			= 	$app->request();		
		$merchantId 	= 	tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response 		= 	new tuplitApiResponse();
		
		/**
         * Get a orders table instance
         */
        $orders 	= 	R::dispense('orders');
		
		if($req->params('Type') !='')		$orders->Type 		=  $req->params('Type');
		if($req->params('Start') !='')		$orders->Start 		=  $req->params('Start');
		if($req->params('Limit') !='')		$orders->Limit 		=  $req->params('Limit');
		if($req->params('DataType') !='')	$orders->DataType	=  $req->params('DataType');
		if($req->params('StartDate') !='')	$orders->StartDate	=  $req->params('StartDate');
		if($req->params('EndDate') !='')	$orders->EndDate	=  $req->params('EndDate');
		if($req->params('Sort') !='')		$orders->Sorting	=  $req->params('Sort');
		if($req->params('Field') !='')		$orders->Field		=  $req->params('Field');
		$orders->MerchantId 			=  $merchantId;
		$productAnalysis				=  $orders->getProductAnalysis($merchantId);
		if($productAnalysis){
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'ProductAnalytics';		
			$response->returnedObject 			= $productAnalysis;	
			echo $response;
		}
		else{
			 /**
	         * throwing error when no transaction found
	         */
			  throw new ApiException("No results found", ErrorCodeType::NoResultFound);
		}
		
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});



/**
* Get Transaction sList 
* POST /v1/merchants/gettransactions/
*/
$app->get('/getTransactionList/',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req 						= 	$app->request();
		$merchantId 				= 	tuplitApi::$resourceServer->getOwnerId();
		
        $merchant 					= 	R::dispense('merchants');		
		$merchant->merchantId		=	$merchantId;
		
		if($req->params('Start') !='')		$merchant->Start 	=  $req->params('Start');
		if($req->params('End') !='')		$merchant->End 		=  $req->params('End');
		if($req->params('Status') !='')		$merchant->Status	=  $req->params('Status');
		if($req->params('Nature') !='')		$merchant->Nature	=  $req->params('Nature');
		
		$transactionDetails 		= 	$merchant->getPaymentsList();
		if($transactionDetails){
			$response 				= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'TransactionsList';
			$response->returnedObject 			= $transactionDetails;
			echo $response;
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
* Update merchant details
* PUT /v1/merchants
*/
$app->put('/settings', tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $request 			= 	$app->request();
		$iconExist 			= 	$merchantExist = '';
    	$body 				= 	$request->getBody();
    	$input 				= 	json_decode($body); 
		$merchantId 		= 	tuplitApi::$resourceServer->getOwnerId();
		//echo "<pre>"; echo print_r($input); echo "</pre>";
		//die();
		/**
		* @var Merchants $merchant
		*/
        $merchant 									= 	R::dispense('merchants');
		$merchant->id		 						= 	$merchantId;
		
		if(isset($input->FirstName)) 				
				$merchant->FirstName 				= 	$input->FirstName;
		if(isset($input->LastName)) 				
				$merchant->LastName 				= 	$input->LastName;
		if(isset($input->Email))			
				$merchant->Email 					= 	$input->Email;
		if(isset($input->PhoneNumber)) 			
				$merchant->PhoneNumber 				= 	$input->PhoneNumber;
		if(isset($input->BusinessName)) 			
				$merchant->BusinessName 			= 	$input->BusinessName;
		if(isset($input->BusinessType)) 	
				$merchant->BusinessType 			= 	$input->BusinessType;
		if(isset($input->Currency)) 			
				$merchant->Currency 				= 	$input->Currency;
		if(isset($input->Address)) 			
				$merchant->Address 					= 	$input->Address;
		if(isset($input->CompanyName)) 			
				$merchant->CompanyName 				= 	$input->CompanyName;
		if(isset($input->RegisterCompanyNumber)) 			
				$merchant->RegisterCompanyNumber 	= 	$input->RegisterCompanyNumber;
		if(isset($input->Country)) 			
				$merchant->Country 					= 	$input->Country;
		if(isset($input->PostCode)) 			
				$merchant->PostCode 				= 	$input->PostCode;	
		if(isset($input->DiscountTier)) 			
				$merchant->DiscountTier 			= 	$input->DiscountTier;	
		if(isset($input->DiscountProductId)) 			
				$merchant->DiscountProductId 		= 	$input->DiscountProductId;	
		if(isset($input->DiscountType)) 			
				$merchant->DiscountType 			= 	$input->DiscountType;	
		if(isset($input->OrderMail)) 			
				$merchant->OrderMail 				= 	$input->OrderMail;	
		
		/**
		* update the merchant account
		*/
	    $merchantId 								= 	$merchant->modifySettings($merchantId);
			
		/**
		* New merchant creation was made success
		*/
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'merchant';
        $response->addNotification('Merchant settings has been updated successfully');
        echo $response;
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
* Get discount product List 
* POST /v1/merchants/discount/
*/
$app->get('/discount/',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req 						= 	$app->request();
		$merchantId 				= 	tuplitApi::$resourceServer->getOwnerId();
		
		if($req->params('MerchantId') !='')		$merchantId 	=  $req->params('MerchantId');	
		
        $merchant 					= 	R::dispense('merchants');
		$merchant->merchantId		=	$merchantId;
		if($req->params('Type') !='')			$merchant->Type 	=  $req->params('Type');	
		if($req->params('Discount') !='')		$merchant->Discount 	=  $req->params('Discount');	
		$ProductCounts 				= 	$merchant->getProductCounts();
		if($ProductCounts){
			$response 				= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'ProductCounts';
			$response->returnedObject 			= $ProductCounts;
			if(isset($ProductCounts['Discounted']) && $ProductCounts['Discounted']==1)
				$response->addNotification('Merchant having 1/3 of products discounted');
			else
				$response->addNotification('Merchant not having 1/3 of products discounted');
			echo $response;
		} else {
			 /**
	         * throwing error when no Products found
	         */
			 throw new ApiException("No Products found", ErrorCodeType::NoResultFound);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
* Get slideshow images 
* POST /v1/merchants/slideshows
*/
$app->get('/slideshows/',tuplitApi::checkToken(),function () use ($app) {

    try {
		// Create a http request
        $req 						= 	$app->request();
		$merchantId 				= 	tuplitApi::$resourceServer->getOwnerId();
		
		if($req->params('MerchantId') !='')		$merchantId 	=  $req->params('MerchantId');	
        $merchant 					= 	R::dispense('merchants');		
		$merchant->merchantId		=	$merchantId;
		$slideshows 				= 	$merchant->getSlideshowDetails();
		if($slideshows){
			$response 				= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'slideshows';
			$response->returnedObject 			= $slideshows;
			$response->addNotification('Merchant slideshow images retrieved successfully');
			echo $response;
		} else {
			 /**
	         * throwing error when no slideshow images found
	         */
			 throw new ApiException("No slideshow images found", ErrorCodeType::NoResultFound);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
* New salesperson creation
* POST /v1/merchants
*/
$app->post('/salesperson/',tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $req = $app->request();
		$merchantId 			= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* create a new salesperson account
		*/
        $salesperson 				= 	R::dispense('merchants');
		$salesperson->FirstName 	= 	$req->params('FirstName');
		$salesperson->LastName 		= 	$req->params('LastName');
		$salesperson->Email 		= 	$req->params('Email');
		$salesperson->Password 		= 	$req->params('Password');
		$salesperson->BrowserDetails	= $_SERVER['HTTP_USER_AGENT'];
		$salesperson->MerchantId	= 	$merchantId;
		
		/**
		* Create the salesperson account
		*/
	    $merchantId = $salesperson->createSalesperson();
		if($merchantId) {			
			/**
			* New salesperson creation was made success
			*/
			$response = new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'salesperson';
			$response->returnedObject 			= $merchantId;
			$response->addNotification('Salesperson has been created successfully');
			echo $response;
		} else {
			 /**
	         * throwing error creating sub user
	         */
			 throw new ApiException("Error in creating Salesperson", ErrorCodeType::NoResultFound);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});

/**
* get sales person list
* GET /v1/merchants
*/
$app->get('/salesperson/',tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $req = $app->request();
		$merchantId 			= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* Get a new salesperson account
		*/
        $salesperson 				= 	R::dispense('merchants');
		$salesperson->merchantId	=	$merchantId;
	    $salespersonlist 			= 	$salesperson->getSalespersonList();
		if($salespersonlist) {			
			$response 				= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 'salespersonlist';
			$response->meta->totalCount 		= $salespersonlist['totalCount'];
			$response->meta->listedCount 		= $salespersonlist['totalCount'];
			$response->returnedObject 			= $salespersonlist['result'];
			$response->addNotification('Salespersons retrieved successfully');
			echo $response;
		} else {
			 /**
	         * throwing error 
	         */
			 throw new ApiException("Error in getting salespersons", ErrorCodeType::NoResultFound);
		}
    }
    catch (ApiException $e){
        // If occurs any error message then goes here
        tuplitApi::showError(
            $e,
            $e->getHttpStatusCode(),
            $e->getErrors()
        );
    }
    catch (\Slim\Exception\Stop $e){
        // If occurs any error message for slim framework then goes here
    }
    catch (Exception $e) {
        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }
});


/**
 * Start the Slim Application
 */

$app->run();