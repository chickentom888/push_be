<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Collections\BaseCollection;
use Dcore\ControllerBase\ControllerBase;
use Dcore\Library\ContractLibrary;
use Dcore\Library\ExcelHelper;
use Dcore\Library\Helper;
use Dcore\Library\Module;
use DCrypto\Adapter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

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
            header("Location: /authorize/login");
            exit(0);
        }
        $code = $this->getTelegramCode();
        if (empty($code)) {
            header("Location: /authorize/logout");
            exit(0);
        }
        $ip = Helper::getClientIp();
        $ipRememberCookie = $this->cookies->get('xs');
        if ($ip != $ipRememberCookie) {
            header("Location: /authorize/logout");
            exit(0);
        }

        $this->userInfo = $this->getUserInfo();
        $this->view->setVars(['userInfo' => $this->userInfo]);
    }

    public function getTelegramCode()
    {
        return $this->session->get("telegram_code");
    }

    public function setTelegramCode($code)
    {
        $this->session->set("telegram_code", $code);
    }

    public function setAuth($user)
    {
        $this->session->set("user_info", $user);
    }

    public function getAuth()
    {
        return $this->session->get("user_info");
    }

    public function showDebug()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
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

    public function returnBackRefURL($flashType = null, $flashMessage = "", $urlRedirect = null)
    {
        if ($flashType != null) $this->flash->message($flashType, $flashMessage);
        if ($urlRedirect != -1 && $urlRedirect != null) return $this->response->redirect($urlRedirect);
        if ($urlRedirect == null) return $this->response->redirect($this->request->getHTTPReferer());
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

    public function listPresaleStatus()
    {
        return [
            ContractLibrary::PRESALE_STATUS_PENDING => 'Pending',
            ContractLibrary::PRESALE_STATUS_ACTIVE => 'Active',
            ContractLibrary::PRESALE_STATUS_SUCCESS => 'Success',
            ContractLibrary::PRESALE_STATUS_FAILED => 'Failed',
            ContractLibrary::AWAITING_START => 'Awaiting Start',
        ];
    }

    public function listLotteryStatus()
    {
        return [
            ContractLibrary::LOTTERY_STATUS_PENDING => 'Pending',
            ContractLibrary::LOTTERY_STATUS_OPEN => 'Open',
            ContractLibrary::LOTTERY_STATUS_CLOSE => 'Close',
            ContractLibrary::LOTTERY_STATUS_CLAIMABLE => 'Claimable',
        ];
    }

    public function listLotteryCronStatus()
    {
        return [
            ContractLibrary::LOTTERY_CRON_STATUS_PENDING => 'Pending',
            ContractLibrary::LOTTERY_CRON_STATUS_ACTIVE => 'Active',
            ContractLibrary::LOTTERY_CRON_STATUS_SUCCESS => 'Success',
            ContractLibrary::LOTTERY_CRON_STATUS_FAIL => 'Fail',
        ];
    }

    public function listLotteryCronTxStatus()
    {
        return [
            ContractLibrary::TRANSACTION_STATUS_PENDING => 'Pending',
            ContractLibrary::TRANSACTION_STATUS_SUCCESS => 'Success',
            ContractLibrary::TRANSACTION_STATUS_FAIL => 'Fail'
        ];
    }

    /**
     * @throws Exception
     */
    protected function exportDataByField($listData, $projectType, $fieldKeys)
    {
        $date = date('d/m/Y H:i:s');
        $projectType = ucfirst($projectType);
        $fileName = "{$projectType}_$date.xlsx";
        $title = "{$projectType} Export Time: $date";
        $columnHeader = 1;
        $rowHeader = 2;
        $rowCount = 3;

        $arrConvertNumber = [
            'amount',
            'base_fee_amount',
            'sale_token_liquidity_amount',
            'sale_token_fee_amount',
            'base_token_liquidity_amount',
            'token_price',
            'soft_cap',
            'hard_cap',
            'creation_fee',
            'total_token_amount',
            'amount_collected'
        ];
        $arrConvertTime = [
            'start_time',
            'created_at',
            'end_time',
            'success_at',
        ];

        $maxColumn = Coordinate::stringFromColumnIndex(count($fieldKeys));
        $spreadsheet = ExcelHelper::initAndSetStyleHeader($maxColumn, $title);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($fieldKeys as $headerValue) {
            $sheet->setCellValueByColumnAndRow($columnHeader, $rowHeader, $headerValue);
            $columnHeader++;
        }

        foreach ($listData as $data) {
            $columnField = 1;
            foreach ($fieldKeys as $field => $name) {
                if ($field == 'current_status') {
                    $data[$field] = $this->listPresaleStatus()[$data[$field]];
                } elseif (in_array($field, $arrConvertTime)) {
                    if ($field == 'success_at') {
                        $data[$field] = $data['current_status'] == ContractLibrary::PRESALE_STATUS_SUCCESS ? date('d/m/Y H:i:s', $data[$field]) : null;
                    } else {
                        $data[$field] = date('d/m/Y H:i:s', $data[$field]);
                    }
                } elseif (in_array($field, $arrConvertNumber)) {
                    $data[$field] = number_format($data[$field], 8);
                } elseif ($field == 'list_address') {
                    $data[$field] = count($data['list_address']);
                } elseif ($field == 'token_type') {
                    if ($data['platform']) {
                        $data[$field] = Helper::getTypeByPlatform($data['platform']);
                    }
                }

                $sheet->setCellValueByColumnAndRow($columnField, $rowCount, $data[$field]);
                $columnField++;
            }
            $rowCount++;
        }

        ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
        exit();
    }
}
