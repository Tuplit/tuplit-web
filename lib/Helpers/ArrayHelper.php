<?php

/**
 * Array Helper class
 * @author 
 */

namespace Helpers;

class ArrayHelper {

    /**
     * Cleans up an array by passing a array of keys to delete
     * @param array $array
     * @param array $toDelete
     * @return array
     */
    public static function array_cleanup( $array, $toDelete )
    {
        foreach($toDelete as $del){
            $cloneArray = $array;
            foreach( $cloneArray as $key => $value )
            {
                if($del === $key)
                    unset( $array[ $key ] );
            }
        }
        return $array;
    }
}