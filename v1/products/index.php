<?php

/**
 * Products endpoint
 * /v1/Product
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
require_once '../../lib/Model_Products.php';             // Product model
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
 * get product details
 * GET /v1/Products
 */
$app->get('/:productId', tuplitApi::checkToken(), function ($productId) use ($app) {

    try {
		 // Create a http request
         $req = $app->request();
		 
		 /**
         * Retreiving Products detail array
         */
	
		$productDetail 		= new Model_Products();
	 	$productDetailArray =  $productDetail->getProductDetail($productId);
		if($productDetailArray){
			$response 	   = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'ProductDetail';			
			$response->returnedObject = $productDetailArray;			
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
 * get Products list based on category
 * GET /v1/Products/
 */
$app->get('/',tuplitApi::checkToken(), function () use ($app) {

    try {
		 // Create a http request
         $req = $app->request();
		 
		 $merchantId = tuplitApi::$resourceServer->getOwnerId();
		 /**
         * Retreiving Products list array
         */
		$product 			= new Model_Products();
	 	$productList 		=  $product->getProductList($merchantId);
		if($productList){
			$response 	   = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'ProductList';			
			$response->returnedObject = $productList;			
			echo $response;
		}
		else{
			 /**
	         * throwing error when no products found
	         */
			  throw new ApiException("No products Found", ErrorCodeType::NoResultFound);
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
 * Delete Product
 * DELETE /v1/Products/
 */
$app->delete('/:deleteId', tuplitApi::checkToken(), function ($deleteId) use ($app) {

    try {
		 // Create a http request
        $req = $app->request();
		 		
		$product = R::dispense('products');
		$product->id 		= $deleteId;
		$product->Status 	= '3';
		R::store($product);
	
		$response 	   = new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName = 'ProductDeleted';			
		/**
		* returning upon response of Product delete
		*/
		$response->addNotification('Product deleted successfully');			
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
 * New Product insertion
 * POST /v1/products
 */
$app->post('/',tuplitApi::checkToken(), function () use ($app) {
    try {

        // Create a http request
        $req = $app->request();
		$merchantId = tuplitApi::$resourceServer->getOwnerId();
        /**
         * Insert new product Values
         */
		
        $products = R::dispense('products');
		$products->fkMerchantsId 	= $merchantId;
		if($req->params('Photo'))
		$products->Photo 			= 1;
		$products->CategoryId		= $req->params('CategoryId');
		$products->ItemName 		= $req->params('ItemName');
		$products->Price 			= $req->params('Price');		
		$products->Status 			= $req->params('Status');		
		if($req->params('Discount'))
			$products->Discount 		= $req->params('Discount');		
		else
			$products->Discount 		= 0;
		$tempImageName 				= $req->params('Photo');
		
		/**
         * Insert new product
         */
		$ProductId = $products->create();		
	  	if($ProductId) {			
			if(!empty($tempImageName)) {
				$imageName 				= $ProductId . '_' . time() . '.png';
				$imagePath 				= UPLOAD_PRODUCT_IMAGE_PATH_REL.$imageName;
				if ( !file_exists(UPLOAD_PRODUCT_IMAGE_PATH_REL) ){
					mkdir (UPLOAD_PRODUCT_IMAGE_PATH_REL, 0777);
				}
				$temppath = TEMP_PRODUCT_IMAGE_PATH_UPLOAD.$tempImageName;				
				copy($temppath,$imagePath);
				if(SERVER) {
					uploadImageToS3($imagePath,8,$imageName);
					unlink($imagePath);
				}				
				
				$productPhoto 			= R::dispense('products');
				$productPhoto->id 		= $ProductId;
				$productPhoto->Photo 	= $imageName;
				R::store($productPhoto);
			
			}	
		}
		$response 	   = new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName = 'ProductId';			
		$response->returnedObject = $ProductId;
		$response->addNotification('Product Added successfully');
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
 * Update product details
 * PUT /v1/products
 */
$app->put('/:ProductId', tuplitApi::checkToken(),function ($ProductId) use ($app) {

    try {

        // Create a http request
        $request = $app->request();
    	$body = $request->getBody();
		
    	$input = json_decode($body);
		
        /**       
         * @var Model_Products $Products
         */
		 $tempImageName = '';
        $product 	= R::dispense('products');		
		if(isset($input->ProductId)) {				
			$product->id			= $ProductId;
			$ProductId				= $ProductId;
		}
		if(isset($input->Photo) && !empty($input->Photo)) {				
			$product->Photo			= $input->Photo;
			$tempImageName			= $input->Photo;
		}
		if(isset($input->CategoryId)) 				
			$product->CategoryId 	= $input->CategoryId;
		if(isset($input->ItemName))			
			$product->ItemName 		= $input->ItemName;
		if(isset($input->Price)) 			
			$product->Price 		= $input->Price;
		if(isset($input->Status)) 			
			$product->Status 		= $input->Status;
		if(isset($input->Discount) && $input->Discount == 1) 			
			$product->DiscountApplied 	= '1';
		else
			$product->DiscountApplied 	= '0';
			
		if(isset($input->ImageAlreadyExists))	
			$product->ImageAlreadyExists 		= $input->ImageAlreadyExists;
		else
			$product->ImageAlreadyExists 		= 0;
		/**
         * update the product details
         */
	    $product->modify();
		
		if(!empty($tempImageName)) {		
			$imageName 				= $ProductId . '_' . time() . '.png';
			$imagePath 				= UPLOAD_PRODUCT_IMAGE_PATH_REL.$imageName;
			if ( !file_exists(UPLOAD_PRODUCT_IMAGE_PATH_REL) ){
				mkdir (UPLOAD_PRODUCT_IMAGE_PATH_REL, 0777);
			}
			$temppath = TEMP_PRODUCT_IMAGE_PATH_UPLOAD.$tempImageName;				
			copy($temppath,$imagePath);
			if(SERVER) {
				uploadImageToS3($imagePath,8,$imageName);
				unlink($imagePath);
			}
			$productPhoto 			= R::dispense('products');
			$productPhoto->id 		= $ProductId;
			$productPhoto->Photo 	= $imageName;
			R::store($productPhoto);
		}	
		
		 /**
		 * product update was made success
		 */
		$response = new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName = 'Product';
		/**
		* returning upon response of Product update
		*/
		$response->addNotification('Product has been updated successfully');
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