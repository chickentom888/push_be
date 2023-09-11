<?php

namespace Dcore\Library;

class Arrays
{
    /**
     * @param array $array
     * @param       $keys
     * @param bool $flip
     *
     * @return array|mixed
     */
    static function selectKeys(array $array, $keys, $flip = true)
    {
        is_string($keys) && $keys = explode(',', str_replace(' ', '', $keys));
        is_array($keys) && $flip && $keys = array_flip($keys);

        return array_intersect_key($array, $keys);
    }

    static function unsetMulti(array &$array, $field, $user_key = true)
    {
        if (is_string($field)) {
            unset($array[$field]);
        }

        is_array($field) && $user_key && $field = array_keys($field);

        foreach ($field as $f) {
            unset($array[$f]);
        }

        return $array;
    }

    /**
     * @param $object
     *
     * @return array
     */
    public static function arrayFrom($object)
    {
        return json_decode(json_encode($object), true);
    }


    /**
     * @param        $array
     * @param string|array $key
     *
     * @return array
     */
    public static function reAssignKey($array, $key)
    {
        $new_array = [];
        if (is_array($array)) {
            foreach ($array as $array_key => $item) {
                $newKey = "";
                if (is_string($key)) {
                    $newKey = $item[$key];
                } else {
                    $lastElement = end($key);
                    foreach ($key as $keyItem) {
                        $newKey .= $item[$keyItem];
                        $keyItem != $lastElement && $newKey .= "_";
                    }
                }

                $new_array[$newKey] = $item;

            }
        }
        return $new_array;
    }

    public static function groupArray($array, $key)
    {
        if ($array == null) return [];
        $new_array = [];
        foreach ($array as $item) {
            $new_array[$item[$key]][] = $item;
        }
        return $new_array;
    }

    public static function arrayColumn($array, $column)
    {
        return array_values(array_unique(array_column($array, $column)));
    }

    public static function searchArrayByValue($array, $column, $value, $remain = false)
    {
        $foundKey = array_search($value, array_column($array, $column));
        if ($remain) {
            unset($array[$foundKey]);
            return $array;
        }
        return $array[$foundKey];
    }
}
