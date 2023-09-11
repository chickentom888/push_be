<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Library\Helper;
use Dcore\Library\Mailer;
use Dcore\Models\Users;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use MongoDB\BSON\ObjectId;
use Phalcon\Escaper;
use Phalcon\Mvc\View;
use Redis;
use UnexpectedValueException;

/**
 * Class ApiControllerBase
 * @package Dcore\Modules\Api\Controllers
 * @property Redis redis
 */
class ApiControllerBase extends ExtendedControllerBase
{
    public $credential;
    public $token;
    public $jsonData;
    public $getData;
    public $postData;

    public function initialize($param = null)
    {
        $this->disableView();
        $this->escapeData($this->request->get(), $this->getData);
        $this->escapeData($this->request->getPost(), $this->postData);
        $this->escapeData($this->request->getJsonRawBody(true), $this->jsonData);
        try {
            $this->authenticate();
        } catch (Exception $ex) {
            echo json_encode(['status' => 401, 'message' => $ex->getMessage()]);
            die;
        }
    }

    public function escapeData($data, &$var)
    {
        if (!$data) {
            return;
        }
        $escaper = new Escaper();
        $escaper->setEncoding("utf-8");
        $escaper->setHtmlQuoteType(ENT_XHTML);
        foreach ($data as $key => $value) {
            $valueEscape = $escaper->escapeHtml($value);
            if (is_array($value) || is_object($value) || is_int($value)) {
                $valueEscape = $value;
            }
            $var[$key] = $valueEscape;
        }
    }

    public function _($text, ...$param)
    {
        $language = $this->getLang();
        if (file_exists(APP_PATH . '/i18n/' . $language . '.php')) {
            $messages = require APP_PATH . '/i18n/' . $language . '.php';
        } else {
            $messages = require APP_PATH . '/i18n/vi.php';
        }
        if (empty($param)) {
            if (isset($messages[$text])) {
                return $messages[$text];
            }
            return $text;
        } else {
            if (!isset($messages[$text])) {
                return $text;
            }
            try {
                return printf($text, $param);
            } catch (Exception $e) {
                return $text;
            }
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function authenticate()
    {

        if ($this->checkWhiteListRequestAccess()) {
            return true;
        }
        $this->credential = null;
        $token = $this->request->getHeader('Authorization');
        if (!$token) {
            $headers = getallheaders();
            $token = (!empty($headers['Authorization']) || !empty($headers['authorization'])) ? $headers['Authorization'] : $this->request->get('token');
        }
        $token = str_replace('Bearer ', '', $token);
        $this->processToken($token);

    }

    /**
     * @throws Exception
     */
    public function getUserInfo()
    {
        $id = new ObjectId($this->credential->_id);
        $user = $this->mongo->selectCollection('user_connect')->findOne(['_id' => $id]);
        if (empty($user)) {
            throw new Exception($this->_("User not found"));
        }
        return $user;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function checkWhiteListRequestAccess()
    {
        $controller = str_replace('-', '_', $this->dispatcher->getControllerName());
        $controller = Helper::snakeToCamel($controller);
        $whiteListController = ['token', 'registry', 'platform', 'slide', 'lock', 'configAddress', 'sale', 'presale', 'pool', 'swap'];
        if (in_array($controller, $whiteListController)) {
            return true;
        }
        $action = str_replace('-', '_', $this->dispatcher->getActionName());
        $action = Helper::snakeToCamel($action);
        $fullCA = "$controller/$action";
        $whiteListCA = [
            'authorize/login',
            'authorize/register',
            'index/selectedAddress',
            'index/listNetwork',
            'index/listPlatform',
            'index/statistic',
            'index/registry',
            'index/mainToken',
            'index/totalValueLock',
            'presale/detail',
            'presale/getList',
            'presale/listPurchased',
            'presale/getUserLog',
            'presale/getBuyLog',
            'presale/listUserRegisterZeroRound',
            'presale/whitelistAddress',
            'presale/checkValidWhitelist',
            'lottery/getList',
            'lottery/detail',
            'lottery/detailLatest',
            'lottery/getLatestLotteryFinished',
            'lotteryTicket/getWinTicketOfLatestLotteryFinished',
            'lotteryTicket/getTopBuyerLastRound',
            'lottery/summary',
        ];
        if (in_array($fullCA, $whiteListCA)) {
            return true;
        }
        return false;
    }

    /**
     * @param $token
     * @throws Exception
     */
    protected function processToken($token)
    {
//        $clientIp = $this->getClientIp();
        $dataDecode = $this->decodeToken($token);
//        if ($clientIp != $dataDecode->client_ip) {
//            throw new Exception($this->_("Invalid authenticate."));
//        }
        $dataUser = $dataDecode->data;
        if (empty($dataUser)) {
            throw new Exception($this->_("Payload data not found"));
        }
        $this->credential = $dataUser;
        $this->token = $token;
    }

    private function disableView()
    {
        $this->view->disable();
        header("Content-Type:application/json;charset=utf-8");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 1000");
        header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
        header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");
    }

    public function showDebug()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
    }

    public function createJWTToken($data)
    {
        $token = [
            "iat" => time(),
            "nbf" => time(),
            "data" => $data,
            "client_ip" => $this->getClientIp()
        ];
        return JWT::encode($token, $this->config->jwt);
    }

    public function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } elseif (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    /**
     * @param $token
     * @return object
     * @throws Exception
     */
    public function decodeToken($token)
    {
        $config = $this->getDI()->get("config");
        try {
            return JWT::decode($token, $config->jwt, ['HS256']);
        } catch (ExpiredException $e) {
            throw new Exception($this->_("Session expired."));
        } catch (SignatureInvalidException|UnexpectedValueException|BeforeValidException|Exception $e) {
//            throw new Exception($e->getMessage());
            throw new Exception($this->_("Invalid authenticate."));
        }
    }

    /**
     * @param Users $user
     * @return array
     */
    public function genUserInfoResponse(Users $user): array
    {
        $inviter = $user->Inviter;
        $inviterName = "---";
        if (!empty($inviter)) $inviterName = $inviter->getNameDisplay();
        return [
            "id" => $user->id,
            "username" => $user->username,
            "unique_code" => $user->unique_code,
            "email" => $user->email,
            "phone" => $user->phone,
            "avatar" => $user->getAvatarUrl(),
            "fullname" => $user->fullname,
            "display_name" => $user->getNameDisplay(),
            "sponsor_name" => $inviterName,
            "dob" => $user->dob > 0 ? date("d-m-Y", $user->dob) : null,
            "gender" => $user->gender,
            "enabled_twofa" => $user->enabled_twofa,
            "role" => $user->role,
            'address' => $user->address,
            'eth_address' => $user->getAddressByCoin('eth'),
            'eth_balance' => $user->eth_balance,
            'usdt_address' => $user->getAddressByCoin('usdt'),
            'usdt_balance' => $user->usdt_balance,
            'usdt_trc20_address' => $user->getAddressByCoin('usdt_trc20'),
            'coin_address' => $user->getAddressByCoin('coin'),
            'coin_balance' => number_format($user->coin_balance, 4),
            'ico_balance' => number_format($user->ico_balance, 4),
            'fake_view' => 0,
        ];
    }

    public function sendMailByTemplate($to, $subject, $template, $data, $optional = [])
    {
        $bufferedContent = $this->renderTemplate('email', $template, $data);
        Mailer::send($to, $subject, $bufferedContent, $optional);
    }

    public function renderTemplate($controller, $action, $data = null)
    {
        $view = $this->view;
        $content = $view->getRender($controller, $action, $data, function ($view) {
            $view->setRenderLevel(View::LEVEL_LAYOUT);
        });
        return $content;
    }

    public function getFileUploads($key = null)
    {
        $config = $this->config;
        try {
            if ($this->request->hasFiles() == true) {
                $pathUpload = $config->media->public_dir;
                $filePath = "uploads/" . date("Y/m/d/");
                $fullPath = $pathUpload . $filePath;
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
                $uploads = $this->request->getUploadedFiles();
                $data = [];
                foreach ($uploads as $upload) {
                    $filename = md5(uniqid(rand(), true)) . '-' . strtolower($upload->getname());
                    $path = $fullPath . $filename;
                    if ($upload->moveTo($path)) {
                        if (in_array(strtolower($upload->getType()), ['jpg', 'jpeg', 'png'])) {
                            if (@is_array(getimagesize($path))) {
                                $image = true;
                            } else {
                                $image = false;
                            }
                            if (!$image) {
                                unlink($path);
                            } else {
                                $image = new ImageResize($path);
                                if ($image->getSourceWidth() > 600) {
                                    $image->resizeToWidth(600);
                                    $image->save($path);
                                }
                            }
                        }
                        $data[] = $filePath . $filename;
                    }
                }
                return !empty($key) ? $data[0] : $data;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param $order
     * @param $by
     * @return array
     */
    protected function sort($order, $by)
    {
        $listOrder = explode(',', strtolower($order));
        $listBy = explode(',', strtolower($by));
        $sort = [];
        foreach ($listOrder as $key => $item) {
            $by = isset($listBy[$key]) && trim($listBy[$key]) == 'asc' ? 1 : -1;
            $sort[trim($item)] = $by;
        }

        return $sort;
    }
}
