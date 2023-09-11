<?php

namespace Dcore\Library;

use CURLFile;
use Dcore\Collections\BaseCollection;
use Dcore\Collections\UserPackage;
use Dcore\Collections\Users;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use MongoDB\BSON\ObjectId;
use Phalcon\Http\Request;

class Helper
{


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
        $string = $label . " - $env" . PHP_EOL . $string;
        $telegram = new Telegram($config->telegram->monitor_token);
        if (!strlen($chartId)) {
            $chartId = $config->telegram->monitor_channel;
        }
        $content = ['chat_id' => $chartId, 'text' => $string];
        return $telegram->sendMessage($content);
    }

    public static function minutesAgo($start)
    {
        $diff = time() - $start;
        return ceil($diff / 60);
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
        }

        return $type;
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
        else return false;
    }
}
