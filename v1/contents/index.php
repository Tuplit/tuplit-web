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

/**
 * Load models
 */
require_once '../../lib/ModelBaseInterface.php';            // base interface class for RedBean models
require_once '../../lib/Model_General.php';                // General model

require_once "../../admin/includes/CommonFunctions.php";

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
         * Retreiving static contents array
         */
        $response 	   = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Ok);
        $response->meta->dataPropertyName = 'staticContent';
		$cms = new Model_General();
		
		$pages['cms'] 				= $cms->getStaticPages();
		$pages['HomeSlider'] 		= $cms->getSliderImages(1);
		$pages['TutorialSlider'] 	= $cms->getSliderImages(2);
		
		
	    $response->returnedObject = $pages;
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
 * Start the Slim Application
 */

$app->run();