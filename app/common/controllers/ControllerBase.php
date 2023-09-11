<?php

namespace Dcore\ControllerBase;

use Dcore\Library\ContractLibrary;
use Dcore\Library\Helper;
use Dcore\Library\Mailer;
use Dcore\Models\Registry;
use Exception;
use Gumlet\ImageResize;
use MongoDB;
use Phalcon\Escaper;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
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
    const ROLE_MEMBER = 0;
    const ROLE_REPORTER = 2;

    public $postData;
    public $getData;
    public $jsonData;
    /** @var Registry */
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
                ##check chỉ lấy file theo key
                if ($key != null) {
                    $fileUpload = [];
                    foreach ($uploads as $upload) {
                        if ($upload->getKey() == $key) {
                            $fileUpload[] = $upload;
                        }
                    }
                } else {
                    $fileUpload = $uploads;
                }
                if (empty($fileUpload)) {
                    return null;
                }
                $data = [];
                foreach ($fileUpload as $upload) {
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

    public function postFileItem($key, $index = null, $fullPath = false)
    {
        try {
            if ($this->request->hasFiles() == true) {

                if (!strlen($index)) {
                    $listIndex = array_keys($_FILES["$key"]['name']);
                } else {
                    $listIndex = [$index];
                }

                $listDataReturn = [];
                foreach ($listIndex as $index) {
                    $fileUploadName = $_FILES["$key"]['name'][$index];
                    if (!strlen($fileUploadName)) return null;
                    $targetDir = $this->config->media->public_dir;
                    $folder = "uploads/" . date("Y/m/d");
                    $listAllow = [
                        "jpg",
                        "jpeg",
                        "png",
                        "txt",
                    ];
                    $fileParts = strtolower(pathinfo($fileUploadName, PATHINFO_EXTENSION));
                    $folderName = '/general/';
                    if (in_array($fileParts, [
                        'jpg',
                        'jpeg',
                        'gif',
                        'png',
                    ])) {
                        $folderName = '/picture/';
                    }
                    if (in_array($fileParts, [
                        'mp3',
                        'mp4',
                        'avi',
                        'mkv',
                    ])) {
                        $folderName = '/video/';
                    }
                    if (in_array($fileParts, ['srt'])) {
                        $folderName = '/sub/';
                    }
                    if (!file_exists($targetDir . $folder . $folderName)) mkdir($targetDir . $folder . $folderName, 0777, true);
                    $targetFile = $folder . $folderName . basename(md5(strtotime("now") . uniqid() . rand(0, 9999)) . "_" . Helper::removeTitle($fileUploadName));
                    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                    if (file_exists($targetFile)) return null;
                    if ($_FILES["$key"]['size'][$index] <= 0) return null;
                    if (!in_array($imageFileType, $listAllow)) return null;
                    move_uploaded_file($_FILES["$key"]['tmp_name'][$index], $targetDir . $targetFile);
                    if ($fullPath == true) $listDataReturn[] = $targetDir . $targetFile;
                    else $listDataReturn[] = $targetFile;
                }

                return $listDataReturn;

            } else {
                return null;
            }

        } catch (Exception $exception) {
            return null;
        }

    }

    public function setBreadcrumb($breadcrumb)
    {
        $this->view->breadcrumb = $breadcrumb;
    }

    public function renderTemplate($controller, $action, $data = null)
    {
        $view = $this->view;
        return $view->getRender($controller, $action, $data, function ($view) {
            $view->setRenderLevel(View::LEVEL_LAYOUT);
        });
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

    public function removeAuth()
    {
        $this->session->remove(self::AUTHENTICATE_SESSION_NAME);
    }

    public function setKeyActive($key, $subKey = null)
    {
        $this->view->active_menu = $key;
        $this->view->active_sub_menu = $subKey;
    }

    public function checkCaptcha($redirectLink)
    {
        $recaptcha = $this->verifyCaptcha($this->request->getPost("g-recaptcha-response"));
        if (empty($recaptcha->success) || $recaptcha->success != 1) {
            $this->flash->error("You must verify the captcha");
            header("Location: " . $redirectLink);
            die;
        }
    }

    public function verifyCaptcha($captcha)
    {
        $secret = $this->config->google->recaptcha->secret;

        $fields = [
            'secret' => $secret,
            'response' => $captcha,
            'remoteip' => $_SERVER['REMOTE_ADDR']
            //'remoteip' => 'localhost'
        ];
        $ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $rs = curl_exec($ch);
        $response = json_decode($rs);
        curl_close($ch);
        return $response;
    }

    public function checkCSRFToken($redirectLink = '')
    {
        if (empty($redirectLink)) {
            $redirectLink = $this->getCurrentURL();
        }
        if (!$this->security->checkToken()) {
            $this->flash->error("Invalid token");
            return $this->response->redirect($redirectLink);
        }
    }

    public function createCSRFToken()
    {
        $this->view->setVars([
            'wtokenKey' => $this->security->getTokenKey(),
            'wtoken' => $this->security->getToken(),
        ]);
    }

    public function verifyCSRFToken($redirectLink = '')
    {
        if (empty($redirectLink)) {
            $redirectLink = $this->getCurrentURL();
        }
        if (!$this->security->checkToken()) {
            $this->flash->error("Something went wrong. CSRF passmode invalid");
            header("Location: " . $redirectLink);
            die;
        }
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

    public function verifyCSFRMethod2($userId, $tokenPostData, $redirect)
    {
        $token_key = $this->config->cache->prefix . "token:" . $userId;
        $token = $this->redis->lpop($token_key);
        if (empty($token) || $token !== $tokenPostData) {
            if ($redirect != null) {
                $this->flash->error("Something went wrong. CSRF passmode invalid");
                header("Location: " . $redirect);
                die;
            }
            return false;
        }
        return true;
    }

    protected function getCurrentURL()
    {
        return $this->request->getURI();
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

    public function sendMailByTemplate($to, $subject, $template, $data, $optional = [])
    {
        $bufferedContent = $this->renderTemplate('email', $template, $data);
        Mailer::send($to, $subject, $bufferedContent, $optional);
    }

    public function showDebug()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
    }

    public function backUrl()
    {
        return $this->response->redirect($this->request->getHTTPReferer());
    }

    public function isLimitLegacy($key)
    {
        $throttler = $this->getDI()->get('throttler_legacy');
        $rateLimit = $throttler->consume($key);
        return $rateLimit->isLimited();
    }

    public function isLimitRequest($key)
    {
        $throttler = $this->getDI()->get('throttler');
        $rateLimit = $throttler->consume($key);
        return $rateLimit->isLimited();
    }

    public function returnBackRefURL($flashType = null, $flashMessage = "", $urlRedirect = null)
    {
        if ($flashType != null) $this->flash->message($flashType, $flashMessage);
        if ($urlRedirect != -1 && $urlRedirect != null) return $this->response->redirect($urlRedirect);
        if ($urlRedirect == null) return $this->response->redirect($this->request->getHTTPReferer());
    }

    public function getControllerActionName()
    {
        $controllerName = $this->dispatcher->getControllerName();
        $actionName = $this->dispatcher->getActionName();
        return "$controllerName/$actionName";
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