<?php

namespace Dcore\Modules\Cli\Tasks;

use Dcore\Library\Helper;

class SwapTask extends TaskBase
{

    public function initialize($param = [])
    {
        parent::initialize($param);
    }

    public function minuteAction()
    {
        $this->extendedAction();
        $this->cmcAction();
        $this->coingeckoAction();
    }

    public function extendedAction()
    {
        $contents = Helper::curlGetFileContents("https://tokens.pancakeswap.finance/pancakeswap-extended.json");
        $contents = json_decode($contents, true);
        $contents['name'] = "Pushswap Extended";
        $contents['logoURI'] = "https://pushswap.org/logo.png";
        $contents['keywords'][0] = 'pushswap';
        $contents = json_encode($contents);
        $folder = BASE_PATH . DIRECTORY_SEPARATOR . "swap_coin";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $fileName = $folder . DIRECTORY_SEPARATOR . "extended.json";
        file_put_contents($fileName, $contents);
    }

    public function cmcAction()
    {
        $contents = Helper::curlGetFileContents("https://tokens.pancakeswap.finance/cmc.json");
        $folder = BASE_PATH . DIRECTORY_SEPARATOR . "swap_coin";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $fileName = $folder . DIRECTORY_SEPARATOR . "cmc.json";
        file_put_contents($fileName, $contents);
    }

    public function coingeckoAction()
    {
        $contents = Helper::curlGetFileContents("https://tokens.pancakeswap.finance/coingecko.json");
        $folder = BASE_PATH . DIRECTORY_SEPARATOR . "swap_coin";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $fileName = $folder . DIRECTORY_SEPARATOR . "coingecko.json";
        file_put_contents($fileName, $contents);
    }
}