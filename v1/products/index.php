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
 * get specialProducts list
 * GET /v1/Products
 */
$app->get('/regularProducts/:merchantId', function ($merchantId) use ($app) {

    try {
		  /**
         * Retreiving Products list array
         */
		$specialproduct = new Model_Products();
	 	$specialproductDetails 	=  $specialproduct->getProductListForSpecial($merchantId);
		//echo "<pre>";   print_r($specialproductDetails);   echo "</pre>";
		if($specialproductDetails){
			$response 	   = new tuplitApiResponse();
	        $response->setStatus(HttpStatusCode::Created);
	        $response->meta->dataPropertyName = 'specialproductDetails';			
			$response->returnedObject = $specialproductDetails;			
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
 * New user creation
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
		$products->ItemName 		= $req->params('ItemName');
		$products->ItemDescription 	= $req->params('ItemDescription');
		$products->Photo 			= $req->params('Photo');
		$products->Price 			= $req->params('Price');
		$products->ItemType 		= $req->params('ItemType');
		$products->Quantity 		= $req->params('Quantity');		
		$products->DiscountPrice 	= $req->params('Price');
		$tempImageName = $req->params('Photo');
		if($req->params('ItemType')==2) {
			$products->DiscountApplied	= 1;
			$products->DiscountTier 		= $req->params('DiscountTier');
			$products->DiscountPrice		= $req->params('DiscountPrice');
		} 
		
		//echo'<pre>';print_r($product);echo'</pre>';
	    /**
         * Insert new product
         */
	   //$ProductId = $products->create(1);
	   $ProductId = 31;
		
		
	  	if($ProductId != 0) {
			if(!empty($tempImageName)) {
				$imageName 				= $merchantId . '_' . time() . '.png';
				$imagePath 				= UPLOAD_PRODUCT_IMAGE_PATH_REL.$imageName;
				if ( !file_exists(UPLOAD_PRODUCT_IMAGE_PATH_REL) ){
					mkdir (UPLOAD_PRODUCT_IMAGE_PATH_REL, 0777);
				}
				$temppath = TEMP_USER_IMAGE_PATH.$tempImageName;
				echo $temppath.'<br>'.$imagePath;
				/*copy($temppath,$imagePath);
				$phMagick = new phMagick($temppath);
				$phMagick->setDestination($imagePath)->resize(100,100);
				if(SERVER) {
					uploadImageToS3($imagePath,6,$imageName);
					unlink($imagePath);
				}
				$merchant->Icon = $imageName;
				$iconExist      =  $imageName;*/
			}
		
		/*
			if($products->ItemType == 3) {
				$specialProducts = R::dispense('specialproducts');				
				$sepecialProductsIds = $req->params('SpecialProductsIds');
				$sepecialProductsIdsArray = explode(",",$sepecialProductsIds);
				//echo'<pre>';print_r($sepecialProductsIdsArray);echo'</pre>';
				foreach($sepecialProductsIdsArray as $val) {					
					$specialProducts->fkSpecialId 	= $ProductId;
					$specialProducts->fkProductsId  = $val;					
					$specialId = $specialProducts->create(2);
					echo $specialId;
				}
			}
		*/
		}
		$response 	   = new tuplitApiResponse();
		$response->setStatus(HttpStatusCode::Created);
		$response->meta->dataPropertyName = 'ProductId';			
		$response->returnedObject = $ProductId;			
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