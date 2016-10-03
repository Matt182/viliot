<?php
namespace viliot\utils;

/**
 * Class with helper methods
 */
class Helper {

    /**
     * check if all fields from $criteria are in $array
     *
     * @param array $array
     * @param array $criteria
     */
    public static function isValid( $array,  $criteria) {
        foreach ($criteria as $item) {
            if(!isset($array[$item])) {
                return false;
            }
        }
        return true;
    }

    /**
     * delete empty values from array
     *
     * @param array $array
     */
    public static function clearEmpty($array) {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_push($result, Helper::clearEmpty($value));
                continue;
            }
            if ($value != "") {
                $result[$key] = $value;
            }
        }
        return $result;
    }

}
