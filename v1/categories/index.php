<?php

/**
* Categories endpoint
* /v1/categories
*
* @author 
*/

/**
* Load configuration
*/
require_once '../../config.php';
require_once '../../admin/includes/CommonFunctions.php';

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';             // base interface class for RedBean models
require_once '../../models/Categories.php'; 

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
* get category list
* POST /v1/categories
*/
$app->get('/', function () use ($app) {

    try {
		/**
		* Retrieving category list array
		*/
		$req 				= 	$app->request();
		$categories 			= 	R::dispense('categories');
		if($req->params('From'))
			$categories->From 	= 	$req->params('From');
	 	$categoryDetails 	 	=   $categories->getCategoryDetails();
		if($categoryDetails){
			$response 	   	= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'categoryDetails';
			$response->meta->totalCount 		= 	$categoryDetails['totalCount'];
			$response->returnedObject 			= 	$categoryDetails['result'];
			echo $response;
		}
		else{
			/**
			* throwing error when static data
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
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
* get product category list
* GET /v1/categories/products/
*/
$app->get('/products',tuplitApi::checkToken(), function () use ($app) {

    try {
		$merchantId 		= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* Retrieving product category list array
		*/
		$categories 		= 	R::dispense('categories');
	 	$categoryDetails 	=  	$categories->getProdctCategoryList($merchantId);
		if($categoryDetails){
			$response 	   	= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'productCategoryDetails';
			$response->returnedObject 			= 	$categoryDetails['result'];
			echo $response;
		}
		else{
			/**
			* throwing error when static data
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
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
* add category
* POST /v1/categories/products
*/
$app->post('/products',tuplitApi::checkToken(), function () use ($app) {

    try {
		$catId		= 	'';
        // Create a http request
        $req		= 	$app->request();
		$merchantId = 	tuplitApi::$resourceServer->getOwnerId();
			
        $categories 				= 	R::dispense('categories');
		$categories->CategoryId 	= 	'';
		$categories->Type 			= 	1;//add
		$categories->fkMerchantId 	= 	$merchantId;
		$categories->CategoryName	= 	$req->params('CategoryName');
		
		/**
		* Insert new category
		*/
		$CategoryId['CategoryId'] 	= 	$categories->create();		
		if($CategoryId){
			$response 	   			= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 	'ProductCategory';			
			$response->returnedObject 			= 	$CategoryId;
			$response->addNotification('Category has been added successfully');
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
* edit category
* PUT /v1/categories/products
*/
$app->put('/products',tuplitApi::checkToken(), function () use ($app) {

    try {
		
        // Create a http request
        $req 		= 	$app->request();
		$merchantId = 	tuplitApi::$resourceServer->getOwnerId();
		$catId 		= 	'';
		$body 		= 	$req->getBody();
    	$input 		= 	json_decode($body); 
		
		if(isset($input->CategoryId)) 		
			$catId  					= 	$input->CategoryId;
        $categories 					= 	R::dispense('categories');
		$categories->CategoryId 		= 	$catId;
		$categories->Type 				= 	2;//edit
		$categories->fkMerchantId 		= 	$merchantId;
		if(isset($input->CategoryName))
			$categories->CategoryName	= 	$input->CategoryName;
		else
			$categories->CategoryName	= 	'';

			/**
		* Insert new category
		*/
		$CategoryId['CategoryId'] 		= 	$categories->create();		
		if($CategoryId){
			$response 	   				= 	new tuplitApiResponse();
			$response->setStatus(HttpStatusCode::Created);
			$response->meta->dataPropertyName 	= 	'ProductCategory';			
			$response->returnedObject 			= 	$CategoryId;
			$response->addNotification('Category has been updated successfully');
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
* get product category list
* GET /v1/categories/productcategories/
*/
$app->get('/productcategories/:categoryId',tuplitApi::checkToken(), function ($categoryId) use ($app) {

    try {
		$merchantId 		= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* Retrieving product category list array
		*/
		$categories 		= 	R::dispense('categories');
	 	$categoryDetails 	=  	$categories->getSingleProdctCategory($merchantId,$categoryId);
		if($categoryDetails){
			$response 	   	= 	new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName 	= 	'singleCategoryDetails';
			$response->returnedObject 			= 	$categoryDetails;	
			echo $response;
		}
		else{
			/**
			* throwing error when static data
			*/
			throw new ApiException("No results Found", ErrorCodeType::NoResultFound);
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
* Delete categories
* DELETE /v1/categories
*/
$app->delete('/:CategoryId',tuplitApi::checkToken(), function ($CategoryId) use ($app) {

    try {

        // Create a http request
        $req 			= 	$app->request();
		$merchantId 	= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* @var Categories $categories
		*/
		$categories 		 		= 	R::dispense('categories');
		$categories->MerchantId 	= 	$merchantId;
		$categories->CategoryId 	= 	$CategoryId ;
		$Categories       			= 	$categories->deleteCategory();
		$response 					= 	new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Created);
        $response->meta->dataPropertyName = 'product categories';
        $response->addNotification('Category has been deleted successfully');
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
* get category product count
* GET /v1/categories/productscount/
*/
$app->get('/productscount/:CategoryId',tuplitApi::checkToken(), function ($CategoryId) use ($app) {

    try {
		$merchantId 		= 	tuplitApi::$resourceServer->getOwnerId();
		
		/**
		* Retrieving product category list array
		*/
		$categories 				= 	R::dispense('categories');
		$categories->merchantId		=	$merchantId;
		$categories->CategoryId		=	$CategoryId;
	 	$productCount 				=  	$categories->getCategoryProductCount();
		$response 	   	= 	new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName 	= 	'CategoryProductCount';
		$response->meta->TotalCount 		= 	$productCount;
		$response->addNotification('Category product count retrieved successfully');
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