<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Helper;
use MongoDB\BSON\ObjectId;

class UserController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize(['check-role' => [BaseCollection::ROLE_ADMIN]]);
    }

    public function indexAction()
    {
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [
            '_id' => ['$ne' => -1]
        ];

        if (strlen($dataGet['username'])) {
            $conditions['username'] = $dataGet['username'];
        }

        if (strlen($dataGet['role'])) {
            $conditions['role'] = intval($dataGet['role']);
        }

        if (strlen($dataGet['status'])) {
            $conditions['status'] = intval($dataGet['status']);
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];
        $userCollection = $this->mongo->selectCollection('user');

        $listData = $userCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $userCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'count'));
    }

    public function formAction($id = null)
    {
        $userCollection = $this->mongo->selectCollection('user');
        $object = [];
        if ($id) {
            $objId = new ObjectId($id);
            $object = $userCollection->findOne(['_id' => $objId]);
        }

        if ($this->request->isPost()) {
            $dataPost = $this->postData;
            if (!strlen($dataPost['username'])) {
                return $this->returnBackRefURL('error', 'Invalid Username');
            }
            $dataPost['password'] = trim($dataPost['password']);

            $dataUpdate = [];
            $dataUpdate['username'] = $dataPost['username'];
            $conditions = $dataUpdate;
            if (isset($objId)) {
                $conditions['_id'] = ['$ne' => $objId];
            }
            $checkExists = $userCollection->countDocuments($conditions);
            if ($checkExists) {
                return $this->returnBackRefURL('error', 'This username already exists!');
            }

            $dataUpdate['status'] = intval(trim($dataPost['status']));
            $dataUpdate['role'] = intval(trim($dataPost['role']));
            if (strlen($dataPost['password'])) {
                $dataUpdate['password'] = $this->security->hash($dataPost['password']);
            }

            if (isset($objId)) {
                $dataUpdate['updated_at'] = time();
                $userCollection->updateOne(['_id' => $objId], ['$set' => $dataUpdate]);
            } else {
                $dataUpdate['created_at'] = time();
                $userCollection->insertOne($dataUpdate);
            }
            return $this->returnBackRefURL('success', 'Success', '/user');
        }

        $this->view->setVars(compact('object'));
    }

    public function changePasswordAction()
    {
        $userInfo = $this->getUserInfo();
        if ($this->request->isPost()) {
            $userCollection = $this->mongo->selectCollection('user');
            $dataPost = $this->postData;
            $password = $dataPost['password'];
            $checkPass = $this->security->checkHash($password, $userInfo['password']);
            if (!$checkPass) {
                return $this->returnBackRefURL('error', 'Invalid current password');
            }

            if ($dataPost['new_password'] != $dataPost['confirm_password']) {
                return $this->returnBackRefURL('error', 'New password does not match');
            }

            if (strlen($dataPost['new_password']) <= 5) {
                return $this->returnBackRefURL('error', 'Passwords must be longer than 6 characters');
            }
            if (!preg_match("#[A-Z]+#", $dataPost['new_password']) && !preg_match("#[0-9]+#", $dataPost['new_password'])) {
                return $this->returnBackRefURL('error', 'Password must contain at least one number or capital letter.');
            }
            $dataUpdate['password'] = $this->security->hash($dataPost['new_password']);
            $dataUpdate['updated_at'] = time();
            $userCollection->updateOne(['_id' => $userInfo['_id']], ['$set' => $dataUpdate]);
            return $this->returnBackRefURL('success', 'Success');
        }
    }
}
