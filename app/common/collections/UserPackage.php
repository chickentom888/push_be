<?php

namespace Dcore\Collections;

use Dcore\Library\Helper;
use MongoDB\BSON\ObjectId;
use MongoDB\Database;
use Phalcon\Di;

class UserPackage extends BaseCollection
{

    public function initialize()
    {
    }

    public static function getUserPackageById($id)
    {
        if (!$id) {
            return [];
        }
        if (!Helper::isObjectIdMongo($id)) {
            return [];
        }
        $id = new ObjectId($id);
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userPackageCollection = $mongo->selectCollection('user_package');
        return $userPackageCollection->findOne(['_id' => $id]);
    }

    public static function getUserPackageHistoryById($id)
    {
        if (!$id) {
            return [];
        }
        if (!Helper::isObjectIdMongo($id)) {
            return [];
        }
        $id = new ObjectId($id);
        /** @var  Database $mongo */
        $mongo = Di::getDefault()->getShared('mongo');
        $userPackageHistoryCollection = $mongo->selectCollection('user_package_history');
        return $userPackageHistoryCollection->findOne(['_id' => $id]);
    }
}
