<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\ControllerBase\ControllerBase;
use Dcore\Library\Module;
use DCrypto\Adapter;

class ExtendedControllerBase extends ControllerBase
{

    public function initialize($param = null)
    {
        global $config;
        $siteCoinTicker = $config->site->coin_ticker;
        $siteCoinKey = $config->site->coin_key;
        $listMenu = Module::listSidebar($this);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listWallet = BaseCollection::listWallet();
        $listBalanceLog = BaseCollection::listBalanceLog();
        $listBonusLog = BaseCollection::listBonusLog();
        $listBranch = BaseCollection::listBranch();
        $leftBranch = BaseCollection::BRANCH_LEFT;
        $this->view->setVars(compact('listMenu', 'listPlatform', 'listNetwork', 'siteCoinKey', 'siteCoinTicker', 'listWallet', 'listBalanceLog', 'leftBranch', 'listBranch', 'listBonusLog'));


        if (!empty($param['check-role'])) {
            $auth = $this->getUserInfo();
            if (!in_array($auth['role'], $param['check-role'])) {
                header("Location: /index/index");
                die;
            }

            //TODO lưu ip login vào cookie và check ip request + ip trong cookie xem có trùng nhau ko, nếu ko trùng nhau thì redirect về login
            /*if (in_array(self::ROLE_ADMIN, $param['check-role']) && count($param['check-role']) == 1) {
                if ($auth['role'] == self::ROLE_ADMIN && $auth->id != 1) {
                    header("Location: /authorize/logout");
                    die;
                }
                $byPassSession = $this->redis->get("adminByPass");
                if ($byPassSession < time()) {
                    if ($this->config->site->twofa) {
                        $code = rand(111111, 999999);
                        $ip = \Mcore\Library\Helper::getClientIp();
                        $controllerAction = $this->getControllerActionName();
                        $message = "Username: {$auth->username}" . PHP_EOL;
                        $message .= "IP: {$ip}" . PHP_EOL;
                        $message .= "Access Code: {$code}" . PHP_EOL;
                        $message .= "URL: {$controllerAction}" . PHP_EOL;
//                        Helper::sendTelegramMsg($message);
                        $this->session->set("adminByPassCode", $code);
                        header("Location: /dashboard/code");
                        die;
                    }
                }
            }*/
        }

        parent::initialize($param);
    }

    public $userInfo;

    public function checkLogin()
    {
        $authUser = $this->getAuth();

        if (!$authUser) {
            $this->session->destroy();
            session_destroy();
            $this->resetAllCookie();
            header("Location: /authorize/login");
            exit(0);
        }

        $this->userInfo = $this->getUserInfo();
        $this->view->setVars(['userInfo' => $this->userInfo]);
    }

    public function setAuth($user)
    {
        $this->session->set("user_info", $user);
    }

    public function getAuth()
    {
        return $this->session->get("user_info");
    }

    public function setHeader($title = "", $desc = "", $keyword = "", $label = "")
    {
        !strlen($desc) && $desc = $title;
        !strlen($keyword) && $keyword = $title;
        !strlen($label) && $label = $title;
        $header = [
            'title' => $title,
            'desc' => $desc,
            'keyword' => $keyword,
            'label' => $label,

        ];
        $this->view->setVar('header', $header);
    }

    public function getConnectedWallet()
    {
        $connectedWallet = $this->session->get('connected_wallet');
        $this->view->setVars([
            'connectedAddress' => $connectedWallet['address'],
            'connectedPlatform' => $connectedWallet['platform'],
            'connectedNetwork' => $connectedWallet['network'],
        ]);
    }
}
