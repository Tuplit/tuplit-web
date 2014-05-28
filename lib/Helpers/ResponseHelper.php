<?php

/**
 * Array Helper class
 * @author 
 */

namespace Helpers;

class ResponseHelper {
	static $response;
    /**
     * Cleans up an array by passing a array of keys to delete
     * @param array $array
     * @param array $toDelete
     * @return array
     */
    public static function setResponse( $response )
    {
        self::$response = $response;
    }
	public static function getResponse()
    {
        return self::$response;
    }
}