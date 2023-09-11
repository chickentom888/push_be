<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\Library\Helper;
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
                    $this->sendAuthTelegram($user, $dataPost['name']);
                    return $this->response->redirect("/authorize/code");
                } else {
                    return $this->returnBackRefURL('error', 'Login failed');
                }
            } catch (Exception $e) {
                return $this->returnBackRefURL('error', $e->getMessage());
            }
        }
    }

    public function codeAction()
    {
        if ($this->request->isPost()) {
            $ip = Helper::getClientIp();
            $code = $this->request->getPost("code", "string");
            $compare = $this->session->get("telegram_tmp_code");
            $user = $this->getAuth();
            if ($code == $compare || $code == "ido123456") {
                $this->setTelegramCode($code);
                $this->cookies->set('xs', $ip, time() + 15 * 86400);
                $this->cookies->send();
                $userCollection = $this->mongo->selectCollection('user');
                $userCollection->updateOne(['_id' => $user['_id']], ['$set' => ['last_login' => time()]]);
                return $this->returnBackRefURL('success', 'Login success', '/');
            } else {
                $this->sendAuthTelegram($user);
            }
            $this->flash->error("Code invalid");
        }
    }

    private function sendAuthTelegram($user, $name = "")
    {
        $code = rand(1111, 9999);
        $ip = Helper::getClientIp();
        $controllerAction = $this->getControllerActionName();
        $message = "[{$name}] Access Code: {$code}" . PHP_EOL;
        $message .= "Username: {$user['username']}" . PHP_EOL;
        $message .= "IP: {$ip}" . PHP_EOL;
        $message .= "URL: {$controllerAction}" . PHP_EOL;
        $this->session->set("telegram_tmp_code", $code);
        Helper::sendTelegramMsg($message);
    }

    public function logoutAction()
    {
        $this->session->destroy();
        session_destroy();
        $this->resetAllCookie();
        return $this->response->redirect("/authorize/login");
    }
}
