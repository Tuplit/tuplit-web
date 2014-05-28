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

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../lib/Model_Merchants.php';                 	// merchant model
require_once '../../lib/Model_Categories.php';                 	// category model

require_once "../../admin/includes/CommonFunctions.php";
require_once "../../admin/includes/phmagick.php";

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
         * @var Model_Merchants $merchant
         */
        $merchant 					= R::dispense('merchants');
		$merchant->FirstName 		= $req->params('FirstName');
		$merchant->LastName 		= $req->params('LastName');
		$merchant->Email 			= $req->params('Email');
		$merchant->Password 		= $req->params('Password');
		$merchant->CompanyName 		= $req->params('CompanyName');
		$merchant->BrowserDetails	= $_SERVER['HTTP_USER_AGENT'];
	    /**
         * Create the merchant account
         */
	    $merchantId = $merchant->create();
		
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
        $merchantDetails = RedBeanHelper::export($merchant->unbox(),['IpAddress','Status','DateModified','DateCreated','Password','BrowserDetails']);
		
		$merchantDetails['MerchantId'] = $merchantDetails['id'];
		
		$response->returnedObject = $merchantDetails;
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
        $request = $app->request();
    	$body = $request->getBody();
		
    	$input = json_decode($body); 
		//echo "<pre>"; print_r( $input); echo "</pre>";
		$requestedById = tuplitApi::$resourceServer->getOwnerId();
        /**
         * Get a new merchant account
         * @var Model_Merchants $merchant
         */
        $merchant 					= R::dispense('merchants');
		$merchant->id		 		= $requestedById;
		$iconExist = $merchantExist = '';
		if(isset($input->Address)) 				
				$merchant->Address 				= $input->Address;
		if(isset($input->Email)) 				
				$merchant->Email 				= $input->Email;
		if(isset($input->CompanyName))			
				$merchant->CompanyName 			= $input->CompanyName;
		if(isset($input->PhoneNumber)) 			
				$merchant->PhoneNumber 			= $input->PhoneNumber;
		if(isset($input->WebsiteUrl)) 			
				$merchant->WebsiteUrl 			= $input->WebsiteUrl;
		if(isset($input->ShortDescription)) 	
				$merchant->ShortDescription 	= $input->ShortDescription;
		if(isset($input->Description)) 			
				$merchant->Description 			= $input->Description;
		if(isset($input->DiscountTier)) 			
				$merchant->DiscountTier 		= $input->DiscountTier;
		if(isset($input->PriceRange)) 			
				$merchant->PriceRange 			= $input->PriceRange;
		if(isset($input->OpeningHours)) 			
				$merchant->OpeningHours 		= $input->OpeningHours;
		if(isset($input->IconExist)) 			
				$iconExist 						= $input->IconExist;
		if(isset($input->MerchantExist)) 			
				$merchantExist 					= $input->MerchantExist;
		
		
		$existingAccount = R::findOne('merchants', 'Email = ? and Status <> ? and id != ? order by DateModified desc', array($merchant->Email,StatusType::DeleteStatus,$merchant->id));
        if ($existingAccount) {
            // an account with that email already exists in the system - don't create account
            throw new ApiException("This Email Address is already associated with another merchant account", ErrorCodeType::EmailAlreadyExists);
		}
		
		
		if(isset($input->IconPhoto)  && $input->IconPhoto != '') {
			$imageName 				= $merchant->id . '_' . time() . '.png';
			$imagePath 				= UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL.$imageName;
			if ( !file_exists(UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL) ){
		  		mkdir (UPLOAD_MERCHANT_ICONS_IMAGE_PATH_REL, 0777);
			}
			copy($input->IconPhoto,$imagePath);
			$phMagick = new phMagick($imagePath);
			$phMagick->setDestination($imagePath)->resize(100,100);
			if(SERVER) {
				uploadImageToS3($imagePath,6,$imageName);
				unlink($imagePath);
			}
			$merchant->Icon = $imageName;
			$iconExist      =  $imageName;
		}
		if(isset($input->MerchantPhoto) && $input->MerchantPhoto != '') {
			$coverImageName 				= $merchant->id . '_' . time() . '.png';
			$coverImagePath 				= UPLOAD_MERCHANT_IMAGE_PATH_REL.$coverImageName;
			if ( !file_exists(UPLOAD_MERCHANT_IMAGE_PATH_REL) ){
		  		mkdir (UPLOAD_MERCHANT_IMAGE_PATH_REL, 0777);
			}
			copy($input->MerchantPhoto,$coverImagePath);
			$phMagick = new phMagick($coverImagePath);
			$phMagick->setDestination($coverImagePath)->resize(640,240);
			if(SERVER) {
				uploadImageToS3($coverImagePath,7,$coverImageName);
				unlink($coverImagePath);
			}
			$merchant->Image = $coverImageName;
			$merchantExist 		=	$coverImageName;
		}
		/**
         * update the merchant account
         */
	    $merchantId = $merchant->modify($requestedById,'',$iconExist,$merchantExist);
		
		
		if(isset($input->Categories)){
		 	$categories 		= 	new Model_Categories();
	 		$deleteCategory 	=  $categories->deleteCategories($merchant->id);
		 	$categoryArray		=	explode(',',$input->Categories);
		 	if(isset($categoryArray)){
			 	foreach($categoryArray as $key=>$val){
					$merchantCategory = R::dispense('merchantcategories');
					$merchantCategory['fkMerchantId'] = $merchant->id;
					$merchantCategory['fkCategoriesId'] = $val;
					$merchantCategory['DateCreated'] = date('Y-m-d H:i:s');
					R::store($merchantCategory);
				}
			}
		}
	    
        /**
         * New merchant creation was made success
         */
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'merchant';
		/**
        * returning upon repsonse of merchant updation
		*/
        $response->addNotification('Merchant has been updated successfully');
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
 * Get merchant details
 * GET /v1/merchants
 */
$app->get('/:merchantId', tuplitApi::checkToken(), function ($merchantId) use ($app) {

    try {
		$from	= 0;
		$userId = '';
        // Create a http request
        $request = $app->request();
		$requestedById = tuplitApi::$resourceServer->getOwnerId();
		
    	/**
         * Get merchant details
         * @var Model_Merchants $merchant
         */
		if($request->params('From'))
			$from	 		= $request->params('From');
		if($request->params('UserId') != ''){
			$userId	 		= $request->params('UserId');
		}
         $merchant 					= R::dispense('merchants');
		
		
		$merchant->From		 		= $from;
		$merchant->UserId	 		= $userId;
		$merchantDetails			= $merchant->getMerchantsDetails($merchantId);
        
		if($merchantDetails){
			$icon_image_path = $image_path = '';
			if(isset($merchantDetails['Icon']) && $merchantDetails['Icon'] != ''){
				if (!SERVER){
					if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchantDetails['Icon'])){
						$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchantDetails['Icon'];
					}
				}
				else{
					if(image_exists(6,$merchantDetails['Icon']))
						$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchantDetails['Icon'];
				}
				$merchantDetails['Icon'] = $icon_image_path;
			}
			if(isset($merchantDetails['Image']) && $merchantDetails['Image'] != ''){
				if (!SERVER){
					if(file_exists(MERCHANT_IMAGE_PATH_REL.$merchantDetails['Image'])){
						$image_path = MERCHANT_IMAGE_PATH.$merchantDetails['Image'];
					}
				}
				else{
					if(image_exists(7,$merchantDetails['Image']))
						$image_path = MERCHANT_IMAGE_PATH.$merchantDetails['Image'];
				}
				$merchantDetails['Image'] = $image_path;
			}
			 /**
	         * merchant details retrieved successfully
	         */
	        $response = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'merchant';
			/**
	        * returning upon repsonse of merchant details
			*/
			$response->returnedObject = $merchantDetails;
			
	        $response->addNotification('Merchant details has been retrieved successfully');
	        echo $response;
		}
		else {
			/** 
			* Some error has occurred while creating Favourites
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
 * POST /v1/users/forgotpassword
 */
$app->get('/checkResetPassword/:userId', function ($userId) use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a user table instance
         */
		$merchants = new Model_Merchants();
		/**
         * Call check reset password function
         */
		// echo $userId;
		$merchantsDetails = $merchants->checkResetPassword($userId);		
		/**
         * Send mail to registered user
         */
		 if($merchants){
			$response->setStatus(HttpStatusCode::Created);
        	$response->meta->dataPropertyName = 'user';
			$response->addNotification('You are allowed to reset password.');
			/*$content	=	array("status"	    =>	"Success",
						  	 	  "message"  	=>	"You are allowed to reset password");
			$response->returnedObject = $content;*/
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
 * Forgot password
 * POST /v1/merchants/password
 */
$app->post('/resetPassword', function () use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a merchant table instance
         */
        $merchant = R::dispense('merchants');
		$merchant->MerchantId	= $req->params('MerchantId');
		$merchant->Password 	= $req->params('Password');
		if($req->params('OldPassword'))
			$merchant->OldPassword 	= $req->params('OldPassword');
		$merchantId		 		= $merchant->updatePassword();
		/**
         * Send mail to registered merchant
         */
		if($merchantId != ''){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'merchant';
			$response->addNotification('Password updated successfully.');
			/*$content	=	array("status"	    =>	"Success",
						  	 	  "message"  	=>	"Password updated successfully.");
			$response->returnedObject = $content;*/
			echo $response;
		}
		else{
			// Error occured while reseting password
			throw new ApiException("Error in reseting password" ,  ErrorCodeType::ErrorInUpdateForgetPassword);
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
 * Forgot password
 * POST /v1/merchants/forgotpassword
 */
$app->post('/forgetPassword', function () use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a merchant table instance
         */
        $merchant = R::dispense('merchants');
		
		$merchant->Email = $req->params('Email');
		$merchantDetails = $merchant->forgotPassword();
		
		/**
         * Send mail to registered merchant
         */
		 if($merchantDetails){
		 	$merchantDetails = $merchantDetails[0];
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
		}
		else{
			// Error occured while reseting password
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
 * Get merchant list
 * GET /v1/merchants
 */
$app->get('/', function () use ($app) {

    try {

        // Create a http request
      	$req = $app->request();

		$longitude = $latitude =  $type = $discountTier = $search = $category = '';
		$start	= $type = 0;
		
		if($req->params('Latitude') != '')
			$latitude 		= $req->params('Latitude');
		if($req->params('Longitude'))
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
 * Start the Slim Application
 */

$app->run();