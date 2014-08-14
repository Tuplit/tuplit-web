<?php

/**
 * Users endpoint
 * /v1/users
 *
 * @author 
 */

/**
 * Load configuration
 */
require_once('../../config.php');
require_once "../../admin/includes/CommonFunctions.php";
require_once "../../admin/includes/phmagick.php";

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';        // base interface class for RedBean models
require_once '../../models/Users.php';                 	// user model
require_once '../../models/Friends.php';                // friends model
require_once '../../models/Orders.php';                 // orders model
require_once '../../models/Favorites.php';				// favourites model

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
 * New user creation
 * POST /v1/users
 */
$app->post('/', function () use ($app) {

    try {

        // Create a http request
        $req = $app->request();
		
        /**
         * Get a new user account
         * @var Users $user
         */
        $user 					= 	R::dispense('users');
		$user->FirstName 		= 	$req->params('FirstName');
		$user->LastName 		= 	$req->params('LastName');
		$user->Email 			= 	$req->params('Email');
		$user->Password 		= 	$req->params('Password');
		if($req->params('CellNumber'))
			$user->CellNumber 	= 	$req->params('CellNumber');
		
		if($req->params('FBId'))
			$user->FBId 		= 	$req->params('FBId');
		if($req->params('GooglePlusId'))
			$user->GooglePlusId = 	$email = $req->params('GooglePlusId');
		if($req->params('PinCode'))
			$user->PinCode 		= 	$req->params('PinCode');
		if($req->params('ZipCode'))
			$user->ZipCode 		= 	$req->params('ZipCode');
		
		if($req->params('Country')) {
			$user->Country 		= 	$req->params('Country');
			$Nationality 		= 	$req->params('Country');
		}
		else {
			$user->Country 		= 	'US';
			$Nationality 		= 	'US';
		}
			
		if($req->params('Location')) {
			$user->Location 	= 	$req->params('Location');
			$Address 			= 	$req->params('Location');
		}
		else  {
			$user->Location 	= 	'US';
			$Address 			= 	'US';
		}
			
		if($req->params('Platform')){
			$platformText 		= 	$req->params('Platform');
			if($platformText == 'ios')
				$platform 		= 	1;
			else if($platformText == 'android')
				$platform 		= 	2;
			else
				$platform 		= 	0;
		}
		else{
			$platform 			= 	0;
		}
		$user->Platform 		= 	$platform;
		$flag = $coverFlag 		= 	0;
		if (isset($_FILES['Photo']['tmp_name']) && $_FILES['Photo']['tmp_name'] != '') {
			$flag 				= 	checkImage($_FILES['Photo'],1);
		}
		
	    $user->PhotoFlag 		= 	$flag;
		
		

	    /**
         * Create the account
         */
		$userId 			= 	$user->create();
		/**
         * Saving user Photo
         */
		 if(isset($userId) && $userId != ''){
				$user->Nationality			=	$Nationality;
				$user->Address				=	$Address; 
				$user->Currency				=	'USD';
				$user->Birthday				=	'1991-01-01';		 
				$user->addMangoPayDetails($userId);
				unset($user->Currency);
				unset($user->Birthday);
				unset($user->Nationality);
				unset($user->Address);
				
				$user->Photo 				= 	'';
				if (isset($_FILES['Photo']['tmp_name']) && $_FILES['Photo']['tmp_name'] != '') {
					$imageName 				= 	$userId . '_' . time() . '.png';
					$imageOriginalPath 		= 	UPLOAD_USER_PATH_REL.$imageName;
					$imageThumbPath 		= 	UPLOAD_USER_THUMB_PATH_REL.$imageName;
					copy($_FILES['Photo']['tmp_name'],$imageOriginalPath);
					
					$phMagick 				= 	new phMagick($imageOriginalPath);
					$phMagick->setDestination($imageThumbPath)->resize(100,100);
					
					if(SERVER){
						uploadImageToS3($imageOriginalPath,1,$imageName);
						uploadImageToS3($imageThumbPath,2,$imageName);
						unlink($imageOriginalPath);
						unlink($imageThumbPath);
					}
					$user->Photo 			= 	$imageName;
					unlink($_FILES['Photo']['tmp_name']);
					$user->PhotoFlag 		= 	5;//success
					$user->modify($userId);
				}
				
				
		 }
		/**
         * After successful registration email was sent to registered user
         */
		if($req->params('Email') && (!$req->params('FBId')) && (!$req->params('GooglePlusId')) ){
			$adminDetails 						=   R::findOne('admins', 'id=?', ['1']);
			$adminMail							=	$adminDetails->EmailAddress;
			$mailContentArray['fileName']		=	'registration.html';
			$mailContentArray['from']			=	$adminMail;
			$mailContentArray['toemail']		= 	$req->params('Email');
			$mailContentArray['subject']		= 	"Registration";
			$mailContentArray['email']	    	=	$req->params('Email');
			$mailContentArray['name']			=	ucfirst($req->params('FirstName').' '.$req->params('LastName'));
			$mailContentArray['password']		=	$req->params('Password');
			sendMail($mailContentArray,2); 
		}
        /**
         * New user creation was made success
         */
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'user';
		/**
        * returning upon repsonse of new user creation
		*/
        $userDetails = RedBeanHelper::export($user->unbox(),['IpAddress','Status','DateModified','DateCreated','ActualPassword','Password','PhotoFlag','UniqueId','PinCode']);
		
		$userDetails['UserId'] = $userDetails['id'];
		if($userDetails['Photo'] !=''){
			$userDetails['Photo'] = USER_THUMB_IMAGE_PATH.$userDetails['Photo'];
		}
		$response->returnedObject = $userDetails;
        $response->addNotification('User has been created successfully');
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
 * CheckReset password
 * GET /v1/users/checkResetPassword/:userId
 */
$app->get('/checkResetPassword/:userId', function ($userId) use ($app) {

    try {
		// Create a http request
        $req 			= 	$app->request();
		
		// Create a json response object
       $response 		= 	new tuplitApiResponse();
	   
		/**
         * Get a user table instance
         */
		$users 			= 	R::dispense('users');
		
		/**
         * Call check reset password function
         */
		$userDetails 	= 	$users->checkResetPassword($userId);
		
		/**
         * Send mail to registered user
         */
		 if($userDetails){
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
 * Merchants Favorites List
 *GET/v1/users/favorites
 */
$app->get('/favorites',tuplitApi::checkToken(),function () use ($app) {	
    try {
		// Create a http request		
        $req 				= 	$app->request();			
		$requestedById 		= 	tuplitApi::$resourceServer->getOwnerId();		
		$start 				= 	0;

		// Create a json response object
        $response 			= 	new tuplitApiResponse();
			
		/**
         * Get a favorites table instance
         */
        $favorite 			= 	R::dispense('favorites');
		$favorite->UsersId	= 	$requestedById;		
		if($req->params('Latitude'))	$favorite->Latitude		= 	$req->params('Latitude');
		if($req->params('Longitude'))	$favorite->Longitude	= 	$req->params('Longitude');
		if($req->params('Search'))		$favorite->Search		= 	$req->params('Search');
		
		if($req->params('Start'))
			$favorite->Start		= 	$req->params('Start');
		else
			$favorite->Start		= 	$start;
		
		/**	
		*	Getting Favorites List
		*/
		$favoriteList				=	$favorite->usersFavoritesList();
		
		if($favoriteList){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName 	= 	'userFavoritesList';
			$response->meta->totalCount 		= 	$favoriteList['totalCount'];
			$response->meta->listedCount 		= 	$favoriteList['listedCount'];
			$response->returnedObject 			= 	$favoriteList['result'];
			echo $response;
		}
		else{
			/** 
			* Some error has occurred while getting favorites list
			*/
			throw new ApiException("Error in getting user favorites list." ,  ErrorCodeType::UserFavouriteListError);
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
 * Check users balance for payment
 *POST/v1/users/checkbalance
 */
$app->post('/checkbalance',tuplitApi::checkToken(),function () use ($app) {	
    try {
		// Create a http request		
        $req 			= 	$app->request();			
		$userId 		= 	tuplitApi::$resourceServer->getOwnerId();
		
		// Create a json response object
        $response		 = 	new tuplitApiResponse();
			
		/**
         * Get a users table instance
         */
		$user 					= 	R::dispense('users');
		if($req->params('UserId') == '')
			$user->UsersId		= 	$userId;
		else
			$user->UsersId		= 	$req->params('UserId');
		$user->PaymentAmount	= 	$req->params('PaymentAmount');
		
		/**
		*	Checking weather user having enough balance for payment
		*/
		$AllowPayment			= 	$user->checkBalance();
		if($AllowPayment){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName 	= 	'AllowPayment';		
			$response->returnedObject 			= 	$AllowPayment;
			echo $response;
		}
		else{
			/** 
			* Some error has occurred while checking balance
			*/			
			throw new ApiException("Error in checking balance." ,  ErrorCodeType::CheckBalanceError);
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
 * Check users location for Cart
 *POST/v1/users/checklocation
 */
$app->post('/checklocation',function () use ($app) {	
     try {
		// Create a http request
        $req 				= 	$app->request();		
		$requestedById 		= 	tuplitApi::$resourceServer->getOwnerId();
		
		// Create a json response object
       $response 			= 	new tuplitApiResponse();
		/**
         * Get a merchants table instance
         */
        $merchant 				= 	R::dispense('merchants');
		$merchant->MerchantId 	= 	$req->params('MerchantId');
		$merchant->Latitude 	= 	$req->params('Latitude');
		$merchant->Longitude 	= 	$req->params('Longitude');
		
		$AllowCart				= 	$merchant->checkLocation();
		if($AllowCart){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName 	= 	'AllowCart';
			$response->returnedObject 			= 	$AllowCart;
			echo $response;
		}
		else{
			/** 
			* Some error has occurred while Checking Location
			*/
			throw new ApiException("Error in Checking Location." ,  ErrorCodeType::CheckLocationError);
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
 * Reset Password
 * POST /v1/users/resetPassword
 */
$app->put('/resetPassword', function () use ($app) {

    try {
		// Create a http request
        $request 	= 	$app->request();
    	$body 		= 	$request->getBody();
    	$input 		= 	json_decode($body); 
		
		// Create a json response object
       $response 	= 	new tuplitApiResponse();
		/**
         * Get a users table instance
         */
        $users = R::dispense('users');
		if(isset($input->UserId)) 		$users->UserId 		= $input->UserId;
		if(isset($input->Password)) 	$users->Password 	= $input->Password;
		/*$users->UserId		= $req->params('UserId');
		$users->Password 	= $req->params('Password');*/
		
		//Resetting password
		$userId		 		= $users->updatePassword();
		
		if($userId != ''){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'users';
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
 * Forgot password
 * GET /v1/users/forgotpassword
 */
$app->get('/forgetPassword', function () use ($app) {

    try {
		// Create a http request
        $req = $app->request();
		
		// Create a json response object
       $response = new tuplitApiResponse();
		/**
         * Get a users table instance
         */
        $users = R::dispense('users');		
		$users->Email = $req->params('Email');
		
		$usersDetails = $users->forgotPassword();
		/**
         * Send mail to reset password
         */
		 if($usersDetails){
		 	$usersDetails = $usersDetails[0];
			$adminDetails 					=   R::findOne('admins', 'id=?', ['1']);
			$adminMail						=	$adminDetails->EmailAddress;
			$mailContentArray['fileName']	=	'userForgotPasswordMail.html';
			$mailContentArray['from']		=	$adminMail;
			$mailContentArray['toemail']	= 	trim($usersDetails['Email']);
			$mailContentArray['subject']	= 	"Forgot Password";
			$mailContentArray['name']		=	ucfirst($usersDetails['FirstName'].' '.$usersDetails['LastName']);
			$mailContentArray['link']		=	'/ResetPassword.php?UID='.encode($usersDetails['id']).'&Type=1';
			sendMail($mailContentArray,1); //Send mail - Updated password Details
     		
			$content	=	array("status"	    =>	"Success",
						  	 	  "message"  	=>	"An email has been sent to you with link to reset your password.");
			$response->returnedObject = $content;
			$response->setStatus(HttpStatusCode::Created);
        	$response->meta->dataPropertyName = 'users';
       		echo $response;
		}
		else{
			// Error occured while reseting password
			throw new ApiException("Error in updating password" ,  ErrorCodeType::ErrorInUpdateForgetPassword);
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
 * Edit User Details
 * PUT /v1/users
 */
$app->put('/',tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $request 		= 	$app->request();
    	$body 			= 	$request->getBody();
    	$input 			= 	json_decode($body);
		$userId 		= 	tuplitApi::$resourceServer->getOwnerId();
        /**
         * Get a new user account
         * @var Users $user
         */
		$platform 		= 	0;
        $user 			= 	R::dispense('users');
        $user->id 		= 	$userId;
		if(isset($input->FirstName)) 	$user->FirstName 		= $input->FirstName;
		if(isset($input->LastName)) 	$user->LastName 		= $input->LastName;
		if(isset($input->Email)) 		$user->Email 			= $input->Email;
		if(isset($input->Password)) 	$user->Password 		= $input->Password;
		if(isset($input->ZipCode)) 		$user->ZipCode 			= $input->ZipCode;
		if(isset($input->Country)) 		$user->Country 			= $input->Country;
		if(isset($input->PinCode)) 		$user->PinCode	 		= $input->PinCode;
		if(isset($input->FBId)) 		$user->FBId	 			= $input->FBId;
		if(isset($input->GooglePlusId ))$user->GooglePlusId 	= $input->GooglePlusId ;
		if(isset($input->Platform)) 	$user->Platform 		= $input->Platform;
		if(isset($input->Location)) 	$user->Location 		= $input->Location;
		if(isset($input->CellNumber)) 	$user->CellNumber 		= $input->CellNumber;
		if(isset($input->Photo) && $input->Photo !=''){			
			$imageName 				= 	$userId . '_' . time() . '.png';
			$user->Photo 			= 	$imageName;
		}
		$userListResult  = R::getAll("select Photo from users where id ='".$userId."' and Status=1");
		if(isset($input->Photo) && $input->Photo !=''){
			
			$image_base64 = $input->Photo;
			$decode_img = base64_decode($image_base64);
			$typecheck = getImageMimeType($decode_img);
				if($typecheck != ''){
					$img = imagecreatefromstring($decode_img);
					if($img != false)
				    {
				    	$imageName = $userId . '_' . time() . '.png';
						$imageOriginalPath 		= UPLOAD_USER_PATH_REL.$imageName;
						$imageThumbPath 		= UPLOAD_USER_THUMB_PATH_REL.$imageName;
					
						imagepng($img, $imageOriginalPath);
						
						$phMagick = new phMagick($imageOriginalPath);
						$phMagick->setDestination($imageThumbPath)->resize(100,100);
						
						$userImage = $userListResult[0]['Photo'];	
						if(!SERVER){
							if($userImage != ''){
								$imageOriginalPath 		= UPLOAD_USER_PATH_REL.$userImage;
								$imageThumbPath 		= UPLOAD_USER_THUMB_PATH_REL.$userImage;
								if(file_exists($imageThumbPath))
									unlink($imageThumbPath);
								if(file_exists($imageOriginalPath))
									unlink($imageOriginalPath);
							}
						}
						
						if(SERVER){
							deleteImages(1,$userImage);
							deleteImages(2,$userImage);
							
							uploadImageToS3($imageOriginalPath,1,$imageName);
							uploadImageToS3($imageThumbPath,2,$imageName);
							
							unlink($imageThumbPath);
							unlink($imageOriginalPath);
						}
						$user->Photo = $imageName;
				    }
			   }
			   else{
			   		/**
			        * Error in photo creation
					*/
					throw new ApiException("Please check the user's properties (Photo)" ,ErrorCodeType::ProblemInImage);
			   }
		}
		$user->modify($userId,1);
		
		/*$userListResult  = R::getAll("select Photo from users where id ='".$userId."' and Status=1");
		if($userListResult) {
			if(isset($input->Photo) && $input->Photo !=''){			
				$imageOriginalPath 		= 	UPLOAD_USER_PATH_REL.$imageName;
				$imageThumbPath 		= 	UPLOAD_USER_THUMB_PATH_REL.$imageName;
				copy($input->Photo,$imageOriginalPath);
				
				$phMagick = new phMagick($imageOriginalPath);
				$phMagick->setDestination($imageThumbPath)->resize(100,100);
				
				if(SERVER){
					uploadImageToS3($imageOriginalPath,1,$imageName);
					uploadImageToS3($imageThumbPath,2,$imageName);
					unlink($imageOriginalPath);
					unlink($imageThumbPath);
				}
			}
		}*/
        /**
         * New user creation was made success
         */
        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'user';
		/**
        * returning upon repsonse 
		*/
        $response->addNotification('User Details has been updated successfully');
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
 * Check Friends
 * POST /v1/users/checkfriends
 */
$app->post('/checkfriends',tuplitApi::checkToken(), function () use ($app) {

    try {
		// Create a http request
        $req 	= $app->request();
		$body 	= $req->getBody();
		
    	$input = json_decode($body); 
		$requestedById = tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response = new tuplitApiResponse();
		/**
         * Get a friends table instance
         */
		$friends = R::dispense('friends');
		if(isset($input->FacebookFriends))
			$friends->FacebookFriends		= $input->FacebookFriends;
		else
			$friends->FacebookFriends		= '';
		if(isset($input->ContactFriends))
			$friends->ContactFriends		= $input->ContactFriends;
		else
			$friends->ContactFriends		= '';
		$friends->UserId 	= $requestedById;
		/**
         * Call check reset password function
         */
		$friendsDetails = $friends->checkInviteFriends();
		/**
         * Send mail to registered user
         */
		 if($friendsDetails){

			$response->setStatus(HttpStatusCode::Created);
        	$response->meta->dataPropertyName = 'user';
			$response->returnedObject = $friendsDetails;
			$response->addNotification('Invite friends details verified successfully');
			echo $response;
		}
		else{
			/*
			* Check friends not available
			*/
            throw new ApiException("Users you requested already in friends/invited status", ErrorCodeType::NoResultFound);
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
 * user friends List
 * GET/v1/users/friends
 */
$app->get('/friends',tuplitApi::checkToken(),function () use ($app) {	
    try {
		// Create a http request		
        $req 				= 	$app->request();	
		
		$requestedById 		= 	tuplitApi::$resourceServer->getOwnerId();		
		$start 				= 	0;

		// Create a json response object
        $response 			= 	new tuplitApiResponse();
			
		/**
         * Get a friends table instance
         */
        $friends 				= 	R::dispense('friends');
		$friends->UserId		= 	$requestedById;	
		if($req->params('Search') != '')		
			$friends->Search	= 	$req->params('Search');
		
		if($req->params('Start'))
			$friends->Start		= 	$req->params('Start');
		else
			$friends->Start		= 	$start;
			
		$friendsList			= 	$friends->usersFriendsList();
		
		if($friendsList && $friendsList['listedCount'] != 0){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName 	= 	'userFriendsList';
			$response->meta->totalCount  		= 	$friendsList['totalCount'];
			$response->meta->listedCount  		= 	$friendsList['listedCount'];
			$response->returnedObject 	 		= 	$friendsList['result'];
			echo $response;
		}
		else{
			// No friends found for this user
			throw new ApiException("No friends found for this user." ,  ErrorCodeType::UserFriendsListError);
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
 * Set New Pin
 * PUT /v1/users/setPIN
 */
$app->put('/setPIN',tuplitApi::checkToken(), function () use ($app) {

    try {

        // Create a http request
        $request = $app->request();
    	$body = $request->getBody();
		
    	$input = json_decode($body,1); 
		$requestedById = $userId = tuplitApi::$resourceServer->getOwnerId();
        /**
         * Get a new user account
         * @var Users $user
         */
        $user = R::dispense('users');
        $user->id = $requestedById;
		if(isset($input['PinCode']))
			$user->PinCode = $input['PinCode'];
		
		//Updating New Pin
		$setPinMsg	= $user->setNewPin();
		if($setPinMsg){
			// Create a json response object
			$response = new tuplitApiResponse();
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'setPincode';
			$response->addNotification("Your new pincode is updated successfully");			
			echo $response;
		}
		else{
			/** 
			* Some error has occurred while setting new pin
			*/
			throw new ApiException("Error in setting new pin." ,  ErrorCodeType::SetPinError);
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
 * verify Pin
 *POST/v1/users/verifyPin
 */
$app->post('/verifyPin',tuplitApi::checkToken(),function () use ($app) {	
    try {
		// Create a http request		
        $req = $app->request();			
		$requestedById = tuplitApi::$resourceServer->getOwnerId();		
			
		/**
         * Get a users table instance
         */
        $user = R::dispense('users');
		$user->UsersId			= $requestedById;		
		$user->PinCode	= $req->params('PinCode');
		
		/**
		*	Checking weather verificationPin matches with users pincode
		*/
		$PinVerify	= $user->verifyPin();
		if($PinVerify){
			// Create a json response object
			$response = new tuplitApiResponse();
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName = 'PinVerify';
			if($PinVerify['PinVerify'] == 1)
				$response->addNotification('Your pin is verified successfully.');
			else
				$response->addNotification('Your pin is mismatched. Try Again...');
			$response->returnedObject = $PinVerify;
			echo $response;
		}
		else{
			/** 
			* Some error has occurred while verifying Pin
			*/			
			throw new ApiException("Error in verifying Pin." ,  ErrorCodeType::verifyPinError);
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
 * Search Users
 * GET/v1/users/
 */
$app->get('/',tuplitApi::checkToken(),function () use ($app) {	
    try {
		// Create a http request		
        $req = $app->request();			
		$MerchantId = tuplitApi::$resourceServer->getOwnerId();		
		
		// Create a json response object
        $response = new tuplitApiResponse();
		
		/**
         * Get a users table instance
         */
        $user 	= R::dispense('users');
		if($req->params('Search') != '')
			$user->Search		= $req->params('Search');
		$user->Latitude		= $req->params('Latitude');
		$user->Longitude	= $req->params('Longitude');
		if($req->params('Start') != '') 
			$user->Start	= $req->params('Start');
		else
			$user->Start	= 0;
		/**	
		*	Getting users List
		*/
		$usersList	= $user->getUserList($MerchantId);
		if($usersList){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName 	= 'userList';
      		$response->meta->TotalUsers 		= $usersList['TotalCount'];
      		//$response->meta->TotalListed 		= $usersList['TotalListed'];
			$response->returnedObject 			= $usersList['result'];
			echo $response;
		}
		else{
			/** 
			* No users found in your location
			*/
			throw new ApiException(" No users found in your location." ,  ErrorCodeType::NoResultFound);
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
 * User's orders
 * GET/v1/users/
 */
$app->get('/:userId/orders',tuplitApi::checkToken(),function ($userId) use ($app) {	
    try {
		// Create a http request		
        $req = $app->request();			
		
		// Create a json response object
        $response = new tuplitApiResponse();

		/**
         * Get a users table instance
         */
        $orders 	= R::dispense('orders');
		if($req->params('Start') != '') 
			$orders->Start	= $req->params('Start');
		else
			$orders->Start	= 0;
		/**	
		*	Getting orders List
		*/
		
		$ordersList	= $orders->getUserOrderDetails($userId);
		if($ordersList['Total'] > 0){
     		$response->setStatus(HttpStatusCode::Created);
      		$response->meta->dataPropertyName 	= 'OrdersList';
      		$response->meta->TotalOrders 		= $ordersList['Total'];
      		$response->meta->ListedOrders 		= count($ordersList['OrderDetails']);
			$response->returnedObject 			= $ordersList['OrderDetails'];
			echo $response;
		}
		else{
			/** 
			* No orders found
			*/
			throw new ApiException(" No Orders found for this user." ,  ErrorCodeType::NoResultFound);
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
 * get user transaction list 
 * GET /v1/users/transactions
 */
$app->get('/transactions',tuplitApi::checkToken(), function () use ($app) {
    try {
		$req 			= 	$app->request();
		$Type			=	'';
		if($req->params('UserId') != '') 
			$userId		= $req->params('UserId');
		else
		$userId 		= 	tuplitApi::$resourceServer->getOwnerId();
		// Create a json response object
        $response 		= 	new tuplitApiResponse();
				
		/**
         * Get a orders table instance
         */
        $orders 	= 	R::dispense('orders');
		if($req->params('Start') !='')		$orders->Start 		=  	$req->params('Start');
		if($req->params('Limit') !='')		$orders->Limit 		=  	$req->params('Limit');

		$transactionList							=  $orders->getUserOrderDetails($userId,1);
		if($transactionList){
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 		= 'TransactionList';		
	        $response->meta->totalCount 			= $transactionList['Total'];		
	        $response->meta->listedCount 			= $transactionList['Listed'];		
			$response->returnedObject 				= $transactionList['OrderDetails'];	
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

/*
/**
 * Get user Details
 * GET /v1/users
 */
$app->get('/:userId',tuplitApi::checkToken(), function ($userId) use ($app) {

    try {		
		// Create a http request
        $req = $app->request();
		$requestedById = tuplitApi::$resourceServer->getOwnerId();
		$ownerType = tuplitApi::$resourceServer->getOwnerType();
        /**
         * Get a user account details
         * @var Users $user
         */
        $user = R::dispense('users');
		$details = array();$type = 'all';
		
		 if($userId == 'self')
		 	$userId = $requestedById;
		 if($req->params('Type') != '') {
		 		$type =	$req->params('Type');
		 }
		$user->Type = $type;
        $userDetails 	= $user->getUserDetails($userId);	
		if($userDetails){
	        $response = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Ok);
	        $response->meta->dataPropertyName 	= 	'userDetails';
			$response->meta->CurrentTime 		= 	date('Y-m-d H:i:s');
			if(isset($userDetails['userMetaDetails']) && is_array($userDetails['userMetaDetails'])){
				$response->meta->TotalOrders 		= 	$userDetails['userMetaDetails']['TotalOrders'];
				$response->meta->TotalComments 		= 	$userDetails['userMetaDetails']['TotalComments'];
			}
			$response->returnedObject 			= 	$userDetails['userDetails'];
	        $response->addNotification('User details has been retrieved successfully');
	        echo $response;
		}
		else {
			/** 
			* Some error has occurred while getting user details
			*/
			throw new ApiException("User not found", ErrorCodeType::NoResultFound);
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
 * Put settings
 * GET /v1/Users
 */
$app->put('/settings',tuplitApi::checkToken(), function () use ($app) {

    try {

        /**
         * update settingds
         */
        $response 				= new tuplitApiResponse();
		
		 // Create a http request
        $request 				= $app->request();
    	$body 					= $request->getBody();
    	$input 					= json_decode($body); 
		$userId 				= tuplitApi::$resourceServer->getOwnerId();
		
    	$user 					= R::dispense('users');
		
		if(isset($input->Type) && !empty($input->Type))
			$user->Type 		= $input->Type;
		
		if(isset($input->Action))
			$user->Action 		= $input->Action;
		
		$settings 				= $user->updateSettings($userId);
		if($settings) {
			$response->setStatus(HttpStatusCode::Ok);
			$response->meta->dataPropertyName = 'settings';
			$response->addNotification('Settings has been updated successfully');
			echo $response;
		}else {
			/** 
			* throw error when the failed to update
			*/
			throw new ApiException("Update settings failed." ,  ErrorCodeType::UpdateSettingsError);
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
 * POST Transfer Amount
 * POST /v1/users
 */
$app->POST('/transfer',tuplitApi::checkToken(), function () use ($app) {

    try {
		 // Create a http request
        $req = $app->request();
		$userId 				= 	tuplitApi::$resourceServer->getOwnerId();
        $users 					= 	R::dispense('users');
		$users->UserId	 		= 	$userId;
		$users->ToUserId 		= 	$req->params('ToUserId');
		$users->Amount 			= 	$req->params('Amount');
		if($req->params('Notes'))
			$users->Notes 		= 	$req->params('Notes');
		else
			$users->Notes 		= 	'';
	   
	    $transferId 			= 	$users->transferAmount();		
		 if($transferId){
			$response 			= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName = 'TransferAmount';		
			$response->addNotification('Amount transferred successfully');
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
 * Users Connect(Mangopay)
 * POST /v1/users/connect
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
		$users 				= R::dispense('users');		
		
		
		if($req->params('FirstName'))
			$users->FirstName			=	$req->params('FirstName');
		if($req->params('LastName'))
			$users->LastName			=	$req->params('LastName');
		if($req->params('Email'))
			$users->Email				=	$req->params('Email');
		if($req->params('Address'))
			$users->Address				=	$req->params('Address');
		if($req->params('Nationality'))
	    	$users->Nationality			=	$req->params('Nationality');
		if($req->params('Country'))
			$users->Country				=	$req->params('Country');
		if($req->params('Occupation'))
			$users->Occupation			=	$req->params('Occupation');
		if($req->params('Currency'))
			$users->Currency			=	$req->params('Currency');
		if($req->params('Birthday'))
			$users->Birthday			=	$req->params('Birthday');
		if($req->params('IncomeRange'))
			$users->IncomeRange			=	$req->params('IncomeRange');
			
		$mangopayDetails				=   $users->addMangoPayDetails($requestedById);
		if($mangopayDetails){
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 'MangoPay Account';		
			$response->addNotification('MangoPay account has been created successfully');
       		echo $response;
		}
		else {
			/** 
			* throw error when the failed to register
			*/
			throw new ApiException("Error in registering with mangopay." ,  ErrorCodeType::ErrorInPaymentRegistration);
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
 * PUT Location update
 * PUT /v1/users
 */
$app->put('/currentLocation',tuplitApi::checkToken(), function () use ($app) {

    try {
	
		 // Create a http request
        $req 				= 	$app->request();
		$userId 			= 	tuplitApi::$resourceServer->getOwnerId();
		$body 				= 	$req->getBody();
    	$input 				= 	json_decode($body); 
		
		//create a user table instance
		
        $users 				= 	R::dispense('users');
		$users->UserId	 	= 	$userId;
		if(isset($input->Latitude)) 
		$users->Latitude 	= 	$input->Latitude;
		if(isset($input->Longitude))
		$users->Longitude 	= 	$input->Longitude;
	    $locationUpdate		= 	$users->locationUpdate();		
		 if($locationUpdate){
			$response 			= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName = 'CurrentLocation';		
			$response->addNotification('Location updated successfully');
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
 * Start the Slim Application
 */

$app->run();