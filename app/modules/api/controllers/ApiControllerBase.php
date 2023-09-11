<?php

namespace Dcore\Modules\Api\Controllers;

use Dcore\Library\Helper;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use MongoDB\BSON\ObjectId;
use Phalcon\Escaper;
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
