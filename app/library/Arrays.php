<?php

namespace Dcore\Library;

use Closure;

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
     * @param array $array
     * @param       $key
     * @param       $id_key
     *
     * @return bool
     */
    static function popObjectByKey(&$array, $key, $id_key)
    {
        foreach ($array as $k => $item) {
            if ($item->$id_key && $item->$id_key == $key) {
                unset($array[$k]);
                return $item;
            }
        }

        return false;
    }

    /**
     * @param       $before
     * @param array $arr
     * @param       $after
     *
     * @return array
     */
    public static function appendElement($before, array $arr, $after = null)
    {
        return array_map(function ($item) use ($before, $after) {
            return "$before$item$after";
        }, $arr);
    }

    public static function removeValues(&$array, $del_val)
    {
        if (!is_array($del_val)) {
            $del_val = [$del_val];
        }
        foreach ($del_val as $rm_val) {
            if (($key = array_search($rm_val, $array)) !== false) {
                unset($array[$key]);
            }
        }
    }

    public static function removeNull(&$array)
    {
        foreach ($array as $k => $v) {
            if ($v === null) unset($array[$k]);
        }
        return $array;
    }

    /**
     * Convert a table to an array associate with prefix
     *
     * @param array $table
     * @param array|null $prefix
     * @param array|null $masters
     *
     * @return array
     */
    public static function flatTable(array &$table, array $prefix = null, array $masters = null)
    {

        $final_res = [];

        if (static::isSeq($prefix)) {
            foreach ($prefix as $pv) {
                $prefix[$pv] = $pv;
            }
        }

        foreach ($table as $row) {

            $res = [];

            array_walk($row, function ($v, $k) use (&$res, $prefix) {

                $outer = true;
                foreach ($prefix as $kp => $vp) {
                    if (strpos($k, $kp) === 0) {
                        $res[$vp][substr($k, strlen($kp))] = $v;
                        $outer = false;
                        break;
                    }
                }

                if ($outer) {
                    $res[$k] = $v;
                }

            });
            foreach ($masters as $master) {
                $res[$master] && $res = array_merge($res, $res[$master]);
                unset($res[$master]);
            }
            $final_res[] = $res;
        }

        return $final_res;
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public static function isSeq(&$array)
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @param array $data
     * @param       $master
     *
     * @return array
     */
    public static function arrayCumulative(array &$data, $master)
    {
        $master = preg_split('/\./', $master);

        $res = [];

        foreach ($data as $dk => $row) {
            $key_obj = $row[$master[0]];
            if (is_array($key_obj)) {
                $id = $key_obj[$master[1]];
            } else {
                $id = $key_obj;
            }

            if ($stored_row = $res[$id]) {
                foreach ($row as $rk => $rv) {
                    if (is_array($rv) && $rk != $master[0]) {
                        $stored_row[$rk] == null && $stored_row[$rk] = [];
                        !in_array($rv, $stored_row[$rk]) && $stored_row[$rk][] = $rv; // Add item into stored row if not existed
                    }
                }
                $res[$id] = $stored_row;

            } else {
                foreach ($row as $rk => $rv) {
                    if (is_array($rv) && $rk != $master[0]) {
                        $row[$rk] = [$rv]; // Init base array
                    }
                }

                $res[$id] = $row;
            }
        }

        return array_values($res);
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
     * @param array $data
     * @param array $assoc
     *
     * @return array
     */
    public static function convertAssoc($data, array $assoc)
    {
        $res = [];
        foreach ($data as $k => $v) {
            if (isset($assoc[$k])) {
                $res[$assoc[$k]] = $v;
            }
        }
        return $res;
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

    /**
     * @param array $array
     * @param Closure $func
     * @return mixed
     */
    public static function searchFirst(array $array, Closure $func)
    {
        foreach ($array as $k => $v) {
            $res = $func($v);
            if ($res === true) {
                return $k;
            }
        }
        return null;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public static function contains_array($data)
    {
        foreach ($data as $value) {
            if (is_array($value)) {
                return true;
            }
        }
        return false;
    }

    public static function fullDiff($array_1, $array_2)
    {
        return array_merge(array_diff($array_1, $array_2), array_diff($array_2, $array_1));
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

    public static function resortArray($data_array, $array_key, $property)
    {
        $hdata = [];
        foreach ($array_key as $key => $val) {
            foreach ($data_array as $item) {
                if ($item["$property"] == $val) {
                    $hdata[] = $item;
                    unset($item);
                }
            }
        }

        return $hdata;
    }

    public static function arrayColumn($array, $column)
    {
        return array_values(array_unique(array_column($array, $column)));
    }

    public static function arrayUnique($array)
    {
        return array_values(array_unique($array));
    }

    /**
     * @param      $arr
     * @param bool $get_key
     * @param bool $pop
     *
     * @return array|mixed
     */
    public static function getLastElement(&$arr, $get_key = false, $pop = false)
    {
        $key = $last_key = key(array_slice($arr, -1, 1, true));

        $res = $get_key
            ? [$key => $arr[$key]]
            : $arr[$key];

        if ($pop) {
            unset($arr[$key]);
        }

        return $res;
    }

    /**
     * @param $big
     * @param $small
     *
     * @return bool
     */
    public static function containAll($big, $small)
    {
        if ($big === null || $small === null) {
            return false;
        }
        return count(array_intersect($small, $big)) == count($small);
    }

    static function getChildest(&$array)
    {
        if ($array == null) {
            return null;

        } else {
            $ikey = array_keys($array)[0];
            $child = $array[$ikey];

            if ($child == null || !is_array($child)) {
                return $array;

            } else {
                return self::getChildest($child);
            }
        }
    }

    public static function reAssignIdMongo($array)
    {
        $new_array = [];
        if (is_array($array)) {
            foreach ($array as $array_key => $item) {
                $new_array[$item['_id']['$oid']] = $item;
            }
        }
        return $new_array;
    }

    public static function getRandomElement($array, $n = 1)
    {
        $randKeys = array_rand($array, $n);
        $listData = [];
        foreach ($randKeys as $item) {
            $listData[] = $array[$item];
        }
        return $listData;
    }

    public static function getObjectByField(&$object, array $fields)
    {
        if (is_object($object)) {
            $object = (array)$object;
        }
        foreach ($object as $k => $f) {
            if (!in_array($k, $fields)) {
                unset($object[$k]);
            }
        }
    }

    public static function assignValueToArray($array, $value)
    {
        $data = [];
        foreach ($array as $item) {
            $data[$item] = $value;
        }
        return $data;
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
