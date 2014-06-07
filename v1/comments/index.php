<?php

/**
 * comment details endpoint
 * /v1/Comments
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
require_once '../../lib/Model_Comments.php';              // comments model


require_once "../../admin/includes/CommonFunctions.php";	// load all common functions

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
 * Comments
 * POST /v1/
 */
$app->post('/',tuplitApi::checkToken(), function () use ($app) {

    try {
        // Create a http request
        $req = $app->request();
		
		$userId = tuplitApi::$resourceServer->getOwnerId();
		$platform = 0;
		if($req->params('Platform')){
			$platformText = $req->params('Platform');
			if($platformText == 'ios')
				$platform = 1;
			else
				$platform = 2;
		}
		else{
			$platformText = 'web';
		}
		/**
         * @var Model_Comments $comments
         */
		$comments 		 		= R::dispense('comments');
		$comments->MerchantId 	= $req->params('MerchantId');
		$comments->UserId 		= $userId;
		$comments->Action 		= 1;
		$comments->Platform 	= $platform ;
		if($req->params('CommentText')){
			$commentsText = addCommentTextEmoji($platformText,$req->params('CommentText'));
			$comments->CommentText 	= $commentsText;
		}
		$commentArray['CommentId']       	= $comments->CommentsProcess();
		$response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'comments';
		$response->returnedObject = $commentArray;
        $response->addNotification('Comments has been added successfully');
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
 * Delete Comments
 * DELETE /v1/Comments
 */
$app->delete('/:CommentId',tuplitApi::checkToken(), function ($CommentId) use ($app) {

    try {

        // Create a http request
        $req = $app->request();
		$userId = tuplitApi::$resourceServer->getOwnerId();
		$platform = 0;
		if($req->params('Platform')){
			$platformText = $req->params('Platform');
			if($platformText == 'ios')
				$platform = 1;
			else
				$platform = 2;
		}
		/**
         * @var Model_Comments $comments
         */
		$comments 		 		= R::dispense('comments');
		$comments->Action 		= 0;
		$comments->UserId 		= $userId;
		$comments->CommentId 	= $CommentId ;
		$Comments       		= $comments->CommentsProcess();
		
		$response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'comments';
        $response->addNotification('Comments has been deleted successfully');
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
 * Get User  - comments
 * GET /v1/
 */
$app->get('/', function () use ($app) {
	try {

        // Create a http request
        $req = $app->request();
		$start = $type = 0;
		$limit	= 20;
		$merchantId = $userId = '';
		if($req->params('Platform')){
			$platformText = $req->params('Platform');
		}
		else{
			$platformText = 'web';
		}
		if($req->params('Start')){
			$start = $req->params('Start');
		}
		if($req->params('MerchantId'))
			$merchantId 	= $req->params('MerchantId');
			
		if($req->params('UserId'))
			$userId 		= $req->params('UserId');
			
		if($req->params('Limit'))
			$limit		 		= $req->params('Limit');
			
		if($req->params('Type'))
			$type		 		= $req->params('Type');
		/**
         * @var Model_Comments $comments
         */
		$comments 		 				= R::dispense('comments');
		$comments->MerchantId 			= $merchantId;
		$comments->UserId 				= $userId;
		$comments->Platform 			= $platformText;
		$comments->Start 				= $start;
		$comments->Limit 				= $limit;
		$comments->Type 				= $type;
		$commentsList           		= $comments->commentsLists();
		
		
		$response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName 	= 'comments';
		$response->meta->totalCount 		= $commentsList['Total'];
		$response->meta->listedCount 		= count($commentsList['List']);
		$response->returnedObject 			= $commentsList['List'];
		$response->meta->currentDate 		= date('Y-m-d H:i:s');
        $response->addNotification('Comments has been listed successfully');
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
 * Start the Slim Application
 */

$app->run();