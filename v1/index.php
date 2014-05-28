<?php

/**
 * Version endpoint
 * /v1
 *
 * @author 
 */

/**
 * Load configuration
 */
require_once('../config.php');

/**
 * Load libraries
 */
require('../vendor/autoload.php');                      // composer library
require_once '../lib/tuplitApi.php';                   // application
require_once '../lib/tuplitApiResponse.php';           // api response object
require_once '../lib/tuplitApiResponseMeta.php';       // api response meta object
require_once '../lib/tuplitApiSetup.php';              // database setup script
require_once '../lib/Helpers/PasswordHelper.php';       // password helper
require_once '../lib/Enumerations/AccountType.php';     // account type enumeration
require_once '../lib/Enumerations/HttpStatusCode.php';  // status code enumeration

use RedBean_Facade as R;
use Enumerations\HttpStatusCode as HttpStatusCode;

/**
 * Initialize application
 */
tuplitApi::init();
$app = new \Slim\Slim();

/**
 * Setup the database
 * GET /v1/setup
 */
$app->get('/setup', function () use ($app) {

    try {

        // Create the database schema
        tuplitApiSetup::init();

        $response = new tuplitApiResponse();
        $response->setStatus(HttpStatusCode::Found);
        $response->addNotification("Database connected successfully.");

        echo $response;
    }
    catch(Exception $e) {

        // If occurs any error message then goes here
        tuplitApi::showError($e);
    }

});

/**
 * Start the Slim Application
 */
$app->run();