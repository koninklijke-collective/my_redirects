<?php

namespace KoninklijkeCollective\MyRedirects\Utility;

/**
 * Utility: Array
 *
 * @package KoninklijkeCollective\MyRedirects\Utility
 */
class ArrayUtility
{

    /**
     * Filter array recursively
     *
     * @param array $array
     * @param callback $callback
     * @return array
     */
    public static function filter(array $array, $callback = null)
    {
        $array = array_filter(...array_filter([$array, $callback]));
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::filter($array[$key], $callback);
            }
        }
        return $array;
    }

}
