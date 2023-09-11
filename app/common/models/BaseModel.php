<?php

namespace Dcore\Models;

use Dcore\Library\Helper;
use Exception;
use Phalcon\Mvc\Model;

/**
 * Class BaseModel
 * @package Dcore\Models
 * @property string token_key
 * @property string to_address
 * @property string from_address
 * @property string hash
 * @property int blockchain_status
 * @property string coin_key
 * @property MongoDB\Database mongo
 */
class BaseModel extends Model
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_UN_CONFIRMED = 3;

    /**
     * map_object
     * @param $arr mixed
     * @return mixed
     */
    public function mapObject($arr)
    {
        try {
            if (is_object($arr)) {
                $arr = (array)$arr;
            }
            foreach ($arr as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
            return $this;
        } catch (Exception $e) {
            print_r($e->getMessage());
            die;
        }
    }

    public function getNextSequence($name)
    {
        $ary = $name::find(
            [
                'limit' => 1,
                'order' => 'id DESC'
            ]
        );
        if (empty($ary->toArray())) $count = 1;
        else {
            $count = intval($ary[0]->id);
            $count++;
        }
        return $count;
    }

    public function getAvatarUrl()
    {
        return Helper::checkImage($this->avatar);
    }
}
