<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Exception;

class AuthorizeController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->view->setMainView("auth");
        parent::initialize();
    }

    public function loginAction()
    {
        if ($this->request->isPost()) {
            try {
                $dataPost = $this->postData;
                $password = $dataPost['password'];
                $username = trim($dataPost['username']);
                if (!$username) {
                    return $this->returnBackRefURL('error', 'Invalid username');
                }

                $userCollection = $this->mongo->selectCollection('user');
                $user = $userCollection->findOne(['username' => $dataPost['username']]);
                if ($user) {
                    if ($user['status'] != BaseCollection::STATUS_ACTIVE) {
                        return $this->returnBackRefURL('error', 'Your account is inactive');
                    }
                    $checkPass = $this->security->checkHash($password, $user['password']);
                    if (!$checkPass) {
                        return $this->returnBackRefURL('error', 'Invalid password');
                    }
                    $this->setAuth($user);
                    return $this->response->redirect("/index");
                } else {
                    return $this->returnBackRefURL('error', 'Login failed');
                }
            } catch (Exception $e) {
                return $this->returnBackRefURL('error', $e->getMessage());
            }
        }
    }

    public function logoutAction()
    {
        $this->session->destroy();
        session_destroy();
        $this->resetAllCookie();
        return $this->response->redirect("/authorize/login");
    }
}
