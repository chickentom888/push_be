<?php

namespace Dcore\ControllerBase;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Mailer;
use Dcore\Models\Registry;
use Exception;
use Gumlet\ImageResize;
use MongoDB;
use Phalcon\Escaper;
use Phalcon\Mvc\Controller;
use Phalcon\Security;
use Phalcon\Security\Random;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;
use Redis;
use stdClass;

/**
 * Class ControllerBase
 * @package Dcore\ControllerBase
 * @property stdClass config
 * @property MongoDB\Database mongo
 * @property Redis redis
 * @property Security security
 */
class ControllerBase extends Controller
{
    const AUTHENTICATE_SESSION_NAME = "authenticate_admin";
    const ROLE_ADMIN = 1;

    public $postData;
    public $getData;
    public $jsonData;
    public $registry;
    public $defaultNetwork = null;


    public function initialize($param = null)
    {
        $this->escapeData($this->request->get(), $this->getData);
        $this->escapeData($this->request->getPost(), $this->postData);
        $this->escapeData($this->request->getJsonRawBody(true), $this->jsonData);
        $this->defaultNetwork = $_ENV['ENV'] == 'sandbox' ? ContractLibrary::TEST_NETWORK : ContractLibrary::MAIN_NETWORK;
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
            if (is_array($value) || is_object($value) || is_int($value)) {
                $valueEscape = $value;
            } else {
                $valueEscape = $escaper->escapeHtml($value);
            }
            $var[$key] = $valueEscape;
        }
    }

    public function _($text, ...$param)
    {
        $language = $this->getLang();
        if (file_exists(APP_PATH . '/i18n/' . $language . '.php')) {
            $messages = require_once APP_PATH . '/i18n/' . $language . '.php';
        } else {
            $messages = require_once APP_PATH . '/i18n/vi.php';
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

    protected function getTranslation()
    {
        // Ask browser what is the best language
        $language = $this->request->getBestLanguage();
        if ($this->session->has("language")) $language = $this->getLang();
        // $language = "en";
        // Check if we have a translation file for that lang
        if (file_exists(APP_PATH . '/i18n/' . $language . '.php'))
            $messages = require_once APP_PATH . '/i18n/' . $language . '.php';
        else {
            $auth = $this->getAuth();
            if ($auth->role == 1) $messages = require_once APP_PATH . '/i18n/vi.php';
            else $messages = require_once APP_PATH . '/i18n/en.php';
        }

        $interpolator = new InterpolatorFactory();
        $factory = new TranslateFactory($interpolator);

        return $factory->newInstance(
            'array',
            [
                'content' => $messages,
            ]
        );
    }

    public function getLang()
    {
        $lang = $this->session->get("language");
        if (!strlen($lang)) {
            return "vi";
        }
        return $lang;
    }

    public function setDataJson($status, $data, $message = null, $optional = null)
    {
        return $this->response->setJsonContent(['status' => $status, 'data' => $data, 'message' => $message, 'optional' => $optional]);
    }

    public function setAuth($userData)
    {
        $this->session->set(self::AUTHENTICATE_SESSION_NAME, $userData);
    }

    public function getAuth()
    {
        return $this->session->get(self::AUTHENTICATE_SESSION_NAME);
    }

    public function getUserInfo()
    {
        if (!empty($this->getAuth())) {
            $auth = $this->getAuth();
            $userId = $auth['_id'];
            $userInfo = $this->mongo->selectCollection('user')->findOne(['_id' => $userId]);
            $this->view->setVar('userInfo', $userInfo);
            return $userInfo;
        } else return null;
    }

    public function setKeyActive($key, $subKey = null)
    {
        $this->view->active_menu = $key;
        $this->view->active_sub_menu = $subKey;
    }

    public function createCSRFMethod2($userId)
    {
        $token_key = $this->config->cache->prefix . "token:" . $userId;
        $this->redis->del($token_key);
        $random = new Random();
        $token = $random->base58(32);
        $this->redis->rpush($token_key, $token);
        $this->view->tokenCSRF = $token;
    }

    public function setHeader($title = "", $desc = "", $keyword = "")
    {
        !strlen($desc) && $desc = $title;
        !strlen($keyword) && $keyword = $title;
        $header = [
            'title' => $title,
            'desc' => $desc,
            'keyword' => $keyword,
        ];
        $this->view->setVar('header', $header);
    }

    public function returnBackRefURL($flashType = null, $flashMessage = "", $urlRedirect = null)
    {
        if ($flashType != null) $this->flash->message($flashType, $flashMessage);
        if ($urlRedirect != -1 && $urlRedirect != null) return $this->response->redirect($urlRedirect);
        if ($urlRedirect == null) return $this->response->redirect($this->request->getHTTPReferer());
    }

    public function resetAllCookie()
    {
        foreach ($_COOKIE as $key => $value) {
            $cookie = $this->cookies->get($key);
            $cookie && $cookie->delete();
        }
        $cookie = $this->cookies->get('xs');
        $cookie && $cookie->delete();
    }
}