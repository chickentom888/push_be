<?php

namespace Dcore\Library;

use CURLFile;
use Dcore\Collections\BaseCollection;
use Dcore\Collections\UserPackage;
use Dcore\Collections\Users;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use DCrypto\Networks\TronWeb3;
use Exception;
use MongoDB\BSON\ObjectId;
use Phalcon\Http\Request;
use stdClass;

class Helper
{

    public static function arrayKeysMulti(array $array)
    {
        $keys = [];
        foreach ($array as $key => $value) {
            $keys[] = $key;
            if (!empty($value)) {
                $keys = array_merge($keys, self::arrayKeysMulti($value));
            }
        }
        return $keys;
    }

    public static function checkImage($avatar)
    {
        global $config;
        $domain = $config['site']['link'];
        $imageDefault = '/custom/images/avatar-default.jpg';
        if (empty($avatar) || !isset($avatar)) {
            return $imageDefault;
        }

        if (strpos($avatar, 'https') !== false) {
            return $avatar;
        }

        if (strpos($avatar, 'uploads') !== false || strpos($avatar, 'frontend') !== false) {
            $avatarReturn = $domain . $avatar;
        } else {
            $avatarReturn = $avatar;
        }
        return $avatarReturn;
    }

    public static function parseNumber($number, $decPoint = null)
    {
        if (empty($decPoint)) {
            $locale = localeconv();
            $decPoint = $locale['decimal_point'];
        }
        return floatval(str_replace($decPoint, '.', preg_replace('/[^\d' . preg_quote($decPoint) . ']/', '', $number)));
    }

    public static function pageLink($para_need_remove, $suffixctr = null)
    {
        $request = new Request;
        $pa = $request->getQuery();
        $controller = $pa['_url'];
        unset($pa['_url']);
        ##Remove Item
        $s = explode(',', $para_need_remove);
        foreach ($s as $item) {
            unset($pa["$item"]);
        }
        ## Append Querystring
        $str = '';
        foreach ($pa as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $sitem) {
                    $hs .= $key . '[]=' . $sitem . '&';
                }
                $str .= $hs;
            } else {
                $str .= $key . '=' . $val . '&';
            }
        }
        if ($suffixctr == null) {
            $link = $controller . "?" . $str;
        } else {
            $link = $suffixctr . "?" . $str;
        }
        $link = rtrim($link, "&");
        return $link;
    }

    public static function pagingInfo($rowCount, $limit, $page, $pagelimit = 3)
    {
        if ($page <= 1) {
            $page = 1;
        }
        $totalPage = ceil($rowCount / $limit);
        $startPaging = $page - $pagelimit;
        if ($startPaging <= 1) {
            $startPaging = 1;
        }
        $endPaging = $page + $pagelimit;
        if ($endPaging >= $totalPage) {
            $endPaging = $totalPage;
        }
        if ($endPaging <= $startPaging) {
            $endPaging = $startPaging = 1;
        }
        return [
            "row_count" => $rowCount,
            "range_page" => range($startPaging, $endPaging),
            "total_page" => $totalPage,
            "page" => $page,
            "current_link" => Helper::pageLink("p"),
            "max_page" => $pagelimit,
            "limit" => $limit
        ];
    }

    public static function br2nl($input)
    {
        return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
    }

    public static function nl2br2($string)
    {
        $string = str_replace([
            "\r\n",
            "\r",
            "\n"
        ], "<br />", $string);
        return $string;
    }

    public static function xss_clean($data)
    {
        if (is_array($data)) {
            $data = array_map([
                self,
                "xss_process"
            ], $data);
        } else {
            $data = self::xss_process($data);
        }
        return $data;
    }

    private function xss_process($data)
    {
        // Fix &entity\n;
        $data = str_replace([
            '&amp;',
            '&lt;',
            '&gt;'
        ], [
            '&amp;amp;',
            '&amp;lt;',
            '&amp;gt;'
        ], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        // we are done...
        return $data;
    }

    public static function Cleanurl($text)
    {
        $text = str_replace('-', ' ', $text);
        $text = str_replace(' -', ' ', $text);
        $text = str_replace([
            '&apos;',
            '&quot;'
        ], '', $text);
        $text = preg_replace('/[^a-zA-Z0-9_ -,.]/s', '', $text);
        $text = trim($text);
        $stripped = preg_replace([
            '/\s{2,}/',
            '/[\t\n]/'
        ], ' ', $text);
        $text = strtolower($stripped);
        $text = str_replace(',', ' ', $text);
        $code_entities_match = [
            ' ',
            '--',
            '&quot;',
            '!',
            '@',
            '#',
            '$',
            '%',
            '^',
            '&',
            '*',
            '(',
            ')',
            '_',
            '+',
            '{',
            '}',
            '|',
            ':',
            '"',
            '<',
            '>',
            '?',
            '[',
            ']',
            '\\',
            ';',
            "'",
            ',',
            '.',
            '/',
            '*',
            '+',
            '~',
            '`',
            '='
        ];
        $code_entities_replace = [
            '-',
            '-',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '-',
            '-',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];
        $text = str_replace($code_entities_match, $code_entities_replace, $text);
        return $text;
    }

    public static function khongdau($text)
    {
        $marTViet = [
            "à",
            "á",
            "ạ",
            "ả",
            "ã",
            "â",
            "ầ",
            "ấ",
            "ậ",
            "ẩ",
            "ẫ",
            "ă",
            "ằ",
            "ắ",
            "ặ",
            "ẳ",
            "ẵ",
            "è",
            "é",
            "ẹ",
            "ẻ",
            "ẽ",
            "ê",
            "ề",
            "ế",
            "ệ",
            "ể",
            "ễ",
            "ì",
            "í",
            "ị",
            "ỉ",
            "ĩ",
            "ò",
            "ó",
            "ọ",
            "ỏ",
            "õ",
            "ô",
            "ồ",
            "ố",
            "ộ",
            "ổ",
            "ỗ",
            "ơ",
            "ờ",
            "ớ",
            "ợ",
            "ở",
            "ỡ",
            "ù",
            "ú",
            "ụ",
            "ủ",
            "ũ",
            "ư",
            "ừ",
            "ứ",
            "ự",
            "ử",
            "ữ",
            "ỳ",
            "ý",
            "ỵ",
            "ỷ",
            "ỹ",
            "đ",
            "À",
            "Á",
            "Ạ",
            "Ả",
            "Ã",
            "Â",
            "Ầ",
            "Ấ",
            "Ậ",
            "Ẩ",
            "Ẫ",
            "Ă",
            "Ằ",
            "Ắ",
            "Ặ",
            "Ẳ",
            "Ẵ",
            "È",
            "É",
            "Ẹ",
            "Ẻ",
            "Ẽ",
            "Ê",
            "Ề",
            "Ế",
            "Ệ",
            "Ể",
            "Ễ",
            "Ì",
            "Í",
            "Ị",
            "Ỉ",
            "Ĩ",
            "Ò",
            "Ó",
            "Ọ",
            "Ỏ",
            "Õ",
            "Ô",
            "Ồ",
            "Ố",
            "Ộ",
            "Ổ",
            "Ỗ",
            "Ơ",
            "Ờ",
            "Ớ",
            "Ợ",
            "Ở",
            "Ỡ",
            "Ù",
            "Ú",
            "Ụ",
            "Ủ",
            "Ũ",
            "Ư",
            "Ừ",
            "Ứ",
            "Ự",
            "Ử",
            "Ữ",
            "Ỳ",
            "Ý",
            "Ỵ",
            "Ỷ",
            "Ỹ",
            "Đ"
        ];
        $marKoDau = [
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "i",
            "i",
            "i",
            "i",
            "i",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "y",
            "y",
            "y",
            "y",
            "y",
            "d",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "I",
            "I",
            "I",
            "I",
            "I",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "Y",
            "Y",
            "Y",
            "Y",
            "Y",
            "D"
        ];
        $str = str_replace($marTViet, $marKoDau, $text);
        return $str;

    }

    public static function startsWith($haystack, $needle) // full String - param
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public static function endsWith($haystack, $needle) //full String - param
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    public static function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
                } else {
                    if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
                    } else {
                        if (isset($_SERVER['HTTP_FORWARDED'])) {
                            $ipaddress = $_SERVER['HTTP_FORWARDED'];
                        } else {
                            if ($_SERVER['REMOTE_ADDR']) {
                                $ipaddress = $_SERVER['REMOTE_ADDR'];
                            } else {
                                $ipaddress = 'UNKNOWN';
                            }
                        }
                    }
                }
            }
        }
        return $ipaddress;
    }

    public static function subWords($string, $start, $length)
    {
        $arrstring = explode(" ", $string); //convert string to array
        if (count($arrstring) > $length) {
            $arrsubstring = array_slice($arrstring, $start, $length); //return array with start position and number of word
            $result = implode(" ", $arrsubstring) . "...";// return n word after sub
        } else {
            $result = $string;
        }
        return $result;
    }

    public static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public static function removeTitle($string, $keyReplace = '-')
    {
        $string = Helper::RemoveSign($string);
        //neu muon de co dau
        $string = trim(preg_replace("/[^A-Za-z0-9.]/i", " ", $string)); // khong dau
        $string = str_replace(" ", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace($keyReplace, "-", $string);
        $string = strtolower($string);
        return $string;
    }

    public static function RemoveSign($str)
    {
        $coDau = [
            "à",
            "á",
            "ạ",
            "ả",
            "ã",
            "â",
            "ầ",
            "ấ",
            "ậ",
            "ẩ",
            "ẫ",
            "ă",
            "ằ",
            "ắ",
            "ặ",
            "ẳ",
            "ẵ",
            "è",
            "é",
            "ẹ",
            "ẻ",
            "ẽ",
            "ê",
            "ề",
            "ế",
            "ệ",
            "ể",
            "ễ",
            "ì",
            "í",
            "ị",
            "ỉ",
            "ĩ",
            "ò",
            "ó",
            "ọ",
            "ỏ",
            "õ",
            "ô",
            "ồ",
            "ố",
            "ộ",
            "ổ",
            "ỗ",
            "ơ",
            "ờ",
            "ớ",
            "ợ",
            "ở",
            "ỡ",
            "ù",
            "ú",
            "ụ",
            "ủ",
            "ũ",
            "ư",
            "ừ",
            "ứ",
            "ự",
            "ử",
            "ữ",
            "ỳ",
            "ý",
            "ỵ",
            "ỷ",
            "ỹ",
            "đ",
            "À",
            "Á",
            "Ạ",
            "Ả",
            "Ã",
            "Â",
            "Ầ",
            "Ấ",
            "Ậ",
            "Ẩ",
            "Ẫ",
            "Ă",
            "Ằ",
            "Ắ",
            "Ặ",
            "Ẳ",
            "Ẵ",
            "È",
            "É",
            "Ẹ",
            "Ẻ",
            "Ẽ",
            "Ê",
            "Ề",
            "Ế",
            "Ệ",
            "Ể",
            "Ễ",
            "Ì",
            "Í",
            "Ị",
            "Ỉ",
            "Ĩ",
            "Ò",
            "Ó",
            "Ọ",
            "Ỏ",
            "Õ",
            "Ô",
            "Ồ",
            "Ố",
            "Ộ",
            "Ổ",
            "Ỗ",
            "Ơ",
            "Ờ",
            "Ớ",
            "Ợ",
            "Ở",
            "Ỡ",
            "Ù",
            "Ú",
            "Ụ",
            "Ủ",
            "Ũ",
            "Ư",
            "Ừ",
            "Ứ",
            "Ự",
            "Ử",
            "Ữ",
            "Ỳ",
            "Ý",
            "Ỵ",
            "Ỷ",
            "Ỹ",
            "Đ",
            "ê",
            "ù",
            "à"
        ];
        $khongDau = [
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "i",
            "i",
            "i",
            "i",
            "i",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "y",
            "y",
            "y",
            "y",
            "y",
            "d",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "I",
            "I",
            "I",
            "I",
            "I",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "Y",
            "Y",
            "Y",
            "Y",
            "Y",
            "D",
            "e",
            "u",
            "a"
        ];
        return str_replace($coDau, $khongDau, $str);
    }

    public static function get_first_char($string)
    {
        $words = explode(" ", self::khongdau($string));
        $acronym = "";
        foreach ($words as $w) {
            $acronym .= strtoupper($w[0]);
        }
        return $acronym;
    }

    public static function sendTelegramMsg($string, $chartId = null)
    {
        global $config;
        $telegram = new Telegram($config->telegram->token);
        if (!strlen($chartId)) {
            $chartId = $config->telegram->main_channel;
        }
        $content = ['chat_id' => $chartId, 'text' => $string];
        return $telegram->sendMessage($content);
    }

    public static function send_telegram_file($file, $caption, $chartId = null)
    {
        global $config;
        $telegram = new Telegram($config->telegram->token);
        if (!strlen($chartId)) {
            $chartId = $config->telegram->main_channel;
        }
        return $telegram->sendDocument([
            'caption' => $caption,
            'chat_id' => $chartId,
            'document' => new CURLFile($file)
        ]);

    }

    public static function sendTelegramMsgMonitor($string, $chartId = null)
    {
        $env = $_ENV['ENV'];
        global $config;
        $label = $config->site->label;
        $string = $label . " - $env". PHP_EOL . $string;
        $telegram = new Telegram($config->telegram->monitor_token);
        if (!strlen($chartId)) {
            $chartId = $config->telegram->monitor_channel;
        }
        $content = ['chat_id' => $chartId, 'text' => $string];
        return $telegram->sendMessage($content);
    }

    public static function sendTelegramMsgLogin($string, $chartId = null)
    {
        global $config;
        $telegram = new Telegram($config->telegram->monitor_token);
        if (empty($chartId)) {
            $chartId = $config->telegram->main_channel;
        }
        $content = ['chat_id' => $chartId, 'text' => $string];
        return $telegram->sendMessage($content);
    }

    public static function responseDataFromDBO($data, $paging, $dGet, $optional = null)
    {
        $o = new stdClass();
        $o->data = $data;
        $o->paging = $paging;
        $o->dGet = $dGet;
        $o->optional = $optional;
        return $o;
    }

    public static function validateEmail($email = '')
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    public static function validatePhoneNumber($phone = '')
    {
        $pattern = '/^[0-9]{10}+$/';
        if (!preg_match($pattern, $phone)) {
            return false;
        }
        return true;
    }

    public static function validateId($id)
    {
        if (preg_match("/^[0-9]*$/", $id)) {
            return true;
        }
        return false;
    }

    public static function isHaveSpecialCharacter($string)
    {
        $pattern = '/[^a-z0-9A-Z .,\- ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀẾỂưăạảấầẩẫậắằẳẵặẹẻẽềếểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ]/u';
        if (preg_match($pattern, $string)) {
            return true;
        }
        return false;
    }

    public static function onlyNumberAndDot($string)
    {
        $pattern = '/[^0-9.]/u';
        if (preg_match($pattern, $string)) {
            return false;
        }
        return true;
    }

    public static function decodeEscapeStr($str = '')
    {
        return html_entity_decode($str);
    }

    public static function minutesAgo($start)
    {
        $diff = time() - $start;
        return ceil($diff / 60);
    }

    public static function encryptpassword($str)
    {
        return md5(md5($str));
    }

    public static function debug()
    {
        $vars = func_get_args();
        foreach ($vars as $var) {
            echo "<pre>";
            print_r($var);
            echo "</pre>";
        }
        die;
    }

    public static function getPresaleStatusText($presale)
    {
        $status = self::getPresaleStatus($presale);
        $text = 'Pending';
        if ($status == ContractLibrary::PRESALE_STATUS_PENDING) {
            $text = 'Pending';
        }
        if ($status == ContractLibrary::PRESALE_STATUS_ACTIVE) {
            $text = 'Active';
        }
        if ($status == ContractLibrary::PRESALE_STATUS_SUCCESS) {
            $text = 'Success';
        }
        if ($status == ContractLibrary::PRESALE_STATUS_FAILED) {
            $text = 'Failed';
        }
        return $text;
    }

    public static function getPresaleStatus($presale)
    {
        if ($presale['current_status'] == ContractLibrary::PRESALE_STATUS_FAILED) {
            return ContractLibrary::PRESALE_STATUS_FAILED;
        }

        if ($presale['current_status'] == ContractLibrary::PRESALE_STATUS_SUCCESS) {
            return ContractLibrary::PRESALE_STATUS_SUCCESS;
        }

        if ($presale['total_base_collected'] >= $presale['hard_cap']) {
            return ContractLibrary::PRESALE_STATUS_SUCCESS;
        }

        if (time() >= $presale['end_time'] && $presale['total_base_collected'] >= $presale['soft_cap']) {
            return ContractLibrary::PRESALE_STATUS_SUCCESS;
        }

        if (time() >= $presale['start_time'] && time() <= $presale['end_time']) {
            return ContractLibrary::PRESALE_STATUS_ACTIVE;
        }

        return ContractLibrary::PRESALE_STATUS_PENDING;


    }

    public static function getPoolRoundName($pool)
    {
        $now = time();
        $text = 'Awaiting Start';
        switch ($pool['current_status']) {
            case ContractLibrary::PRESALE_STATUS_FAILED:
                $text = 'Failed';
                break;
            case ContractLibrary::PRESALE_STATUS_SUCCESS:
                $text = 'Success';
                break;
            case ContractLibrary::PRESALE_STATUS_ACTIVE:
                $text = 'Active';
                break;
            case ContractLibrary::PRESALE_STATUS_PENDING:
                if ($pool['active_auction_round']) {
                    $text = 'Auction Round';
                    if ($now < $pool['auction_round']['start_time']) {
                        $text = 'Waiting To Auction';
                    }
                    if ($pool['auction_round']['end_time'] < $now && $now < $pool['start_time']) {
                        $text = 'Awaiting Start';
                    }
                }
                if ($pool['active_zero_round']) {
                    $text = 'Zero Round';
                    if ($now < $pool['start_time'] && $now > $pool['zero_round']['finish_at']) {
                        $text = 'Awaiting Start';
                    }
                }
                break;
        }

        return $text;
    }

    /**
     * @param $pool
     * @return int
     */
    public static function getPoolRoundDefine($pool)
    {
        $now = time();
        $define = -1;

        if ($pool['current_round'] == ContractLibrary::PRESALE_STATUS_PENDING && $pool['current_status'] == ContractLibrary::PRESALE_STATUS_PENDING) {
            $define = 0;
        }
        if ($pool['current_round'] == ContractLibrary::POOL_BURNING_ROUND && $pool['current_status'] == ContractLibrary::PRESALE_STATUS_PENDING) {
            $define = 3;
        }
        if ($pool['current_round'] == ContractLibrary::PRESALE_STATUS_ACTIVE && $pool['current_status'] == ContractLibrary::PRESALE_STATUS_ACTIVE) {
            $define = 5;
        }
        if ($pool['current_round'] == ContractLibrary::PRESALE_STATUS_SUCCESS && $pool['current_status'] == ContractLibrary::PRESALE_STATUS_ACTIVE) {
            $define = 61;
            if ($pool['active_first_round']) {
                $define = 62;
            }
        }
        if ($pool['current_status'] == ContractLibrary::PRESALE_STATUS_SUCCESS) {
            if (in_array($pool['current_round'], [ContractLibrary::AWAITING_START, ContractLibrary::PRESALE_STATUS_ACTIVE, ContractLibrary::PRESALE_STATUS_SUCCESS])) {
                $define = 7;
                if ($pool['is_active_claim']) {
                    $define = 8;
                }
            }
        }
        if ($pool['current_status'] == ContractLibrary::PRESALE_STATUS_FAILED) {
            $define = 9;
        }
        if ($pool['current_round'] == ContractLibrary::AWAITING_START && $pool['current_status'] == ContractLibrary::PRESALE_STATUS_PENDING) {
            if ($pool['active_auction_round']) {
                if ($now < $pool['auction_round']['start_time']) {
                    $define = 2;
                }
                if ($pool['auction_round']['end_time'] < $now && $now < $pool['start_time']) {
                    $define = 42;
                    if ($pool['active_first_round']) {
                        $define = 41;
                    }
                }
            } else {
                $define = 14;
                if ($pool['active_zero_round'] && $now > $pool['zero_round']['finish_at']) {
                    $define = 12;
                    if ($pool['active_first_round']) {
                        $define = 11;
                    }
                } else if ($pool['active_first_round']) {
                    $define = 13;
                }
            }
        }

        return $define;
    }

    /**
     * @throws Exception
     */
    public static function getLinkAddress($address, $platform = null, $network = null)
    {
        $platform == null && $platform = BinanceWeb3::PLATFORM;
        if ($_ENV['ENV'] == 'sandbox') {
            $network == null && $network = ContractLibrary::TEST_NETWORK;
        } else {
            $network == null && $network = ContractLibrary::MAIN_NETWORK;
        }
        $mainCurrency = Adapter::getMainCurrency($platform);
        $instance = Adapter::getInstance($mainCurrency, $network);
        return $instance->explorer_link['address'] . $address;
    }

    /**
     * @throws Exception
     */
    public static function getLinkTx($hash, $platform = null, $network = null)
    {
        $platform == null && $platform = BinanceWeb3::PLATFORM;
        if ($_ENV['ENV'] == 'sandbox') {
            $network == null && $network = ContractLibrary::TEST_NETWORK;
        } else {
            $network == null && $network = ContractLibrary::MAIN_NETWORK;
        }
        $mainCurrency = Adapter::getMainCurrency($platform);
        $instance = Adapter::getInstance($mainCurrency, $network);
        return $instance->explorer_link['transaction'] . $hash;
    }

    /**
     * @param $presale
     * @return int
     */
    public static function getCurrentRound($presale)
    {
        $time = time();
        $round = -1;
        if ($time < $presale['start_time']) {
            $round = -1;
            if ($presale['active_zero_round'] && $time <= $presale['zero_round']['finish_at']) {
                $round = 0;
            }
        } else if ($time <= $presale['end_time']) {
            $round = 2;
            if ($presale['active_first_round']) {
                if ($time < ($presale['start_time'] + $presale['first_round_length'])) {
                    $round = 1;
                }
            }
        }

        return $round;
    }

    public static function getBlockTaskStatusText($status)
    {
        $text = 'Not Scan';
        if ($status == BlockTaskLibrary::STATUS_NOT_PROCESS) {
            $text = 'Not Scan';
        } else if ($status == BlockTaskLibrary::STATUS_PROCESSING) {
            $text = 'Scanning';
        } else if ($status == BlockTaskLibrary::STATUS_PROCESSED) {
            $text = 'Scanned';
        } else if ($status == BlockTaskLibrary::STATUS_PROCESSING_TX) {
            $text = 'Processing Tx';
        } else if ($status == BlockTaskLibrary::STATUS_PROCESSED_TX) {
            $text = 'Processed Tx';
        }
        return $text;

    }

    public static function getUrl($url)
    {
        if (!$url) {
            return '';
        }
        $parseUrl = parse_url($url);
        return $parseUrl['scheme'] . '://' . $parseUrl['host'] . $parseUrl['path'];
    }

    public static function getTypeByPlatform($platform)
    {
        $type = 'ERC20';
        switch ($platform) {
            case BinanceWeb3::PLATFORM:
                $type = 'BEP20';
                break;
            case TronWeb3::PLATFORM:
                $type = 'TRC20';
                break;
        }

        return $type;
    }

    public static function reverseNumber($num)
    {
        $x = 0;
        while (floor($num)) {
            $mod = $num % 10;
            $x = $x * 10 + $mod;
            $num = $num / 10;
        }
        return $x;
    }

    public static function getRealLotteryNumber($num)
    {
        $realNumber = substr(strval($num), 1);
        return strrev($realNumber);
    }

    public static function calculateBracket($finalNumber, $userNumber)
    {

        $bracketCalculator = [
            0 => 1,
            1 => 11,
            2 => 111,
            3 => 1111,
            4 => 11111,
            5 => 111111,
        ];
        $bracket = -1;
        for ($i = 0; $i < 6; $i++) {
            $transformedFinalNumber = $bracketCalculator[$i] + ($finalNumber % pow(10, $i + 1));
            $transformedUserNumber = $bracketCalculator[$i] + ($userNumber % pow(10, $i + 1));
            if ($transformedFinalNumber == $transformedUserNumber) {
                $bracket = $i;
            } else {
                break;
            }
        }
        return $bracket;
    }

    public static function getStakingInterestPercent($amount)
    {
        if (1000 <= $amount && $amount <= 9999) {
            return 6;
        }
        if (10000 <= $amount && $amount <= 49999) {
            return 8;
        }
        if (50000 <= $amount && $amount <= 99999) {
            return 10;
        }
        if (100000 <= $amount && $amount <= 249999) {
            return 12;
        }
        if (250000 <= $amount && $amount <= 499999) {
            return 14;
        }
        if (500000 <= $amount) {
            return 18;
        }
        return 0;
    }

    public static function isObjectIdMongo($value)
    {
        if (empty($value)) {
            return false;
        }
        if ($value instanceof ObjectId) {
            return true;
        }

        try {
            new ObjectId($value);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    public static function randomString($n = 10)
    {
        $seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, $n) as $k) $rand .= $seed[$k];
        return $rand;
    }

    public static function getTeamBonusPercent($littleAmount)
    {
        return 8;
        $percent = 6;
        if ($littleAmount > 1800000) {
            $percent = 8;
        }
        if ($littleAmount > 3500000) {
            $percent = 10;
        }
        return $percent;
    }

    public static function makeShortString($str, $startChars = 5, $endChars = 5, $separator = '.', $remainingChars = 3)
    {
        $length = strlen($str);
        if (!$length) {
            return '';
        }
        if (!$remainingChars) {
            $remainingChars = $length - $startChars - $endChars;
        }

        if ($remainingChars <= 0) {
            // If there are no characters left for the middle portion, just return the original string
            return $str;
        } else {
            // Otherwise, construct the shortened string
            $start = substr($str, 0, $startChars);
            $end = substr($str, -$endChars);
            $middle = str_repeat($separator, $remainingChars);
            return $start . $middle . $end;
        }
    }

    public static function getUserConnectById($userConnectId)
    {
        return Users::getUserById($userConnectId);
    }

    public static function getUserPackage($id)
    {
        return UserPackage::getUserPackageById($id);
    }

    public static function getUserPackageHistory($id)
    {
        return UserPackage::getUserPackageHistoryById($id);
    }

    public static function camelToSnake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    public static function snakeToCamel($input)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    public static function getWithdrawStatusText($status)
    {
        $text = '';
        if ($status == BaseCollection::STATUS_PENDING) {
            $text = 'Pending';
        } else if ($status == BaseCollection::STATUS_APPROVE) {
            $text = 'Approve';
        } else if ($status == BaseCollection::STATUS_REJECT) {
            $text = 'Reject';
        }
        return $text;
    }

    public static function getWithdrawStatusClass($status)
    {
        $class = '';
        if ($status == BaseCollection::STATUS_PENDING) {
            $class = 'warning';
        } else if ($status == BaseCollection::STATUS_APPROVE) {
            $class = 'success';
        } else if ($status == BaseCollection::STATUS_REJECT) {
            $class = 'danger';
        }
        return $class;
    }

    public static function getWithdrawBlcStatusText($status)
    {
        $text = '';
        if ($status == BaseCollection::STATUS_PENDING) {
            $text = 'Pending';
        } else if ($status == BaseCollection::STATUS_APPROVE) {
            $text = 'Success';
        } else if ($status == BaseCollection::STATUS_REJECT) {
            $text = 'Failed';
        }
        return $text;
    }

    public static function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function numberFormat($numVal, $afterPoint = 2, $minAfterPoint = 0, $thousandSep = ",", $decPoint = ".")
    {
        // Same as number_format() but without unnecessary zeros.
        $ret = number_format($numVal, $afterPoint, $decPoint, $thousandSep);
        if ($afterPoint != $minAfterPoint) {
            while (($afterPoint > $minAfterPoint) && (substr($ret, -1) == "0")) {
                // $minAfterPoint!=$minAfterPoint and number ends with a '0'
                // Remove '0' from end of string and set $afterPoint=$afterPoint-1
                $ret = substr($ret, 0, -1);
                $afterPoint = $afterPoint - 1;
            }
        }
        if (substr($ret, -1) == $decPoint) {
            $ret = substr($ret, 0, -1);
        }
        return $ret;
    }

    public static function curlGetFileContents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }
}
