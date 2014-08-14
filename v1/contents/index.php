<?php

/**
* Content endpoint
* /v1/content
*
* @author 
*/

/**
* Load configuration
*/
require_once('../../config.php');
require_once "../../admin/includes/CommonFunctions.php";

/**
* Load models
*/
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../models/General.php';                // General model

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
* Get static pages
* GET /v1/
*/
$app->get('/', function () use ($app) {

    try {

		/**
		* Retrieving static contents array
		*/
        $response 	   = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Ok);
        $response->meta->dataPropertyName = 'staticContent';
		$cms = R::dispense('general');
		
		$pages['cms'] 				= $cms->getStaticPages();
		$pages['HomeSlider'] 		= $cms->getSliderImages(1);
		$pages['TutorialSlider'] 	= $cms->getSliderImages(2);
		/**
         * Get Discount Tier array
         */
		$pages['DiscountTier']   	= array();
		global $discountTierArray;
		if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0 ){
			$pages['DiscountTier'] 	= $discountTierArray;
		}
	    $response->returnedObject 	= $pages;
        $response->addNotification('Static content has been retrieved successfully');
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
* Get web-page contents
* GET /v1/webpagecontent/
*/
$app->get('/webpagecontent/:url', function ($url) use ($app) {

    try {

		/**
		* Retrieving web-page static contents array
		*/
        $response 	   	= new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Ok);
        $response->meta->dataPropertyName = 'webPageContent';
		
		$webcontent		= R::dispense('general');
		$pageContent 	= $webcontent->getWebPageContent($url);				
	    $response->returnedObject = $pageContent;
        $response->addNotification('Web page content has been retrieved successfully');
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
* Get countries
* GET /v1/countries
*/
$app->get('/countries/', function () use ($app) {

    try {

		$response 	   	= new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Ok);
        $response->meta->dataPropertyName = 'countries';
		
		$countries					= R::dispense('general');
		$countriesList 				= $countries->getcountries();
	    $response->returnedObject 	= $countriesList;
        $response->addNotification('Countries has been retrieved successfully');
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
* Get currencies
* GET /v1/currencies
*/
$app->get('/currencies/', function () use ($app) {

    try {

		$response 	   	= new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Ok);
        $response->meta->dataPropertyName = 'currencies';
		
		$currencies					= R::dispense('general');
		$currenciesList 			= $currencies->getcurrencies();
	    $response->returnedObject 	= $currenciesList;
        $response->addNotification('Currencies has been retrieved successfully');
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