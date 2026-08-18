<?php

namespace Core\JustArray;

use Core\JustArray\Exceptions\InvalidArrayPathException;
use Core\JustArray\Exceptions\KeyNotExistsException;
use Dotenv\Exception\InvalidPathException;
use Error;
use Exception;

/**
 *  Main Environment Array class. Use this API Class to work with every Array key/value in the environment.
 */
class JustArray {    
    /**
     * find
     * Get a value from a multi level array key/value.
     *  @param array $array Array to search by path.
     *  @param string $path The path to reach the value.
     *  
     * @return mixed The result of the search by path `null` if there's no value inside the array.
     */
    public static function find(array $array, string $path): mixed {
        try {
            return static::findReference($array, $path);
        } catch (Exception $ex) {
            if($_ENV['production'] === true) 
                echo $ex->getMessage();
            return null;
        }
    }
    
    /**
     * Add or update a new value inside a array based on a wanted path.
     *
     * @param  mixed $array The array to modify.
     * @param  mixed $newValue New value to insert or update.
     * @param  mixed $place The path where you want add or update a new value.
     * @return array
     */
    public static function add(array &$array, mixed $newValue, string $place): array{
        $pathSegments = explode('.', $place); //Get path segments.

        //Try to find the reference first to update it...
        try {
            $value = &static::findReference($array, $place);
            $value = $newValue;
        } catch (KeyNotExistsException $ex) {
            $current = &$array; //Store the reference of the array.

            //Iterate each path segment
            foreach ($pathSegments as $index => $wantedKey) {
                //If the path segment already exists then access to it...
                if(array_key_exists($wantedKey, $current)) {
                    $current = &$current[$wantedKey];
                    continue;
                };

                //If the curren path segment is the last then put the wanted value
                if($index === count($pathSegments) - 1) {
                    $current[$wantedKey] = $newValue;
                    break;
                }

                //While the index doesn't reach the last of them create a new nested array every time.
                $current[$wantedKey] = [];
            }
        } catch (InvalidPathException $ex) {

        }

        return $array;
    }

    /**
     * Search a reference inside an array and return its reference or a copy value.
     *
     * @param array $array The array to search from.
     * @param string $path The path of the value/reference wanted.
     * @return mixed The value found or the reference value found which means you can get the array reference to modify it.
     */
    private static function &findReference(array &$array, string $path): mixed{
        $pathSegments = explode('.', $path);
        $value = &$array;

        foreach ($pathSegments as $index => $parent) {
            if (!is_array($value)) 
                throw new InvalidArrayPathException("Cannot access $parent on non-array value.");
            
            if(!array_key_exists($parent, $value))  throw new KeyNotExistsException("$parent key does't exists inside the given array check your path syntax.");
            
            $value = &$value[$parent];
        }

        return $value;
    }
}