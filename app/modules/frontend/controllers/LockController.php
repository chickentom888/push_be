<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\ExcelHelper;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class LockController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        parent::initialize($param);
        $this->checkLogin();
        $this->getConnectedWallet();
    }

    /**
     * @throws Exception
     */
    public function indexAction()
    {
        $collection = $this->mongo->selectCollection('lock_histories');
        $dataGet = $this->getData;
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listContractType = $this->listContractType();
        $listWithdrawnStatus = ContractLibrary::getWithdrawStatusName();
        $withdrawnStatus = ContractLibrary::WITHDRAWN;
        $notWithdrawnStatus = ContractLibrary::NOT_WITHDRAW;

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }
        if (strlen($dataGet['withdraw_status'])) {
            $conditions['withdraw_status'] = isset($listWithdrawnStatus[$dataGet['withdraw_status']]) ? intval($dataGet['withdraw_status']) : null;
        }
        if (strlen($dataGet['address'])) {
            $conditions['$or'] = [
                ['address_lock' => $dataGet['address']],
            ];
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address'])) {
                $filterAddress = $coinInstance->toCheckSumAddress($dataGet['address']);
                array_push(
                    $conditions['$or'],
                    ['from' => $filterAddress],
                    ['to' => $filterAddress],
                );
            }
        }

        if (strlen($dataGet['address_token'])) {
            $coinInstance = Adapter::getInstance(BinanceWeb3::MAIN_CURRENCY);
            if ($coinInstance->validAddress($dataGet['address_token'])) {
                $tokenAddress = $coinInstance->toCheckSumAddress($dataGet['address_token']);
                $conditions['token_address'] = $tokenAddress;
            }
        }

        if (strlen($dataGet['hash'])) {
            $conditions['hash'] = $dataGet['hash'];
        }

        if ($dataGet['export']) {
            $listData = $collection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $this->exportExcel($listData);
        }

        $limit = 20;
        $p = $dataGet['p'] ?? 1;
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;
        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];


        $count = $collection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $collection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'listPlatform',
            'listNetwork', 'listContractType', 'listWithdrawnStatus', 'withdrawnStatus', 'notWithdrawnStatus'));
    }

    protected function exportExcel($listData)
    {
        $tokenCollection = $this->mongo->selectCollection('tokens');
        $fileName = 'Lock_' . date("Ymd_his") .'.xlsx';

        $title = 'Report Lock';
        $headerColumn = ['Hash', 'Time', 'Unlock Time', 'Platform', 'Network', 'Address lock',
            'Address withdraw', 'Real token amount', 'Base fee', 'Token fee amount', 'Token Name', 'Token symbol'];
        $columnHeader = 1;
        $rowHeader = 2;
        $dataRow = 3;

        $maxColumn = Coordinate::stringFromColumnIndex(count($headerColumn));
        $spreadsheet = ExcelHelper::initAndSetStyleHeader($maxColumn, $title);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headerColumn as $headerValue) {
            $sheet->setCellValueByColumnAndRow($columnHeader, $rowHeader, $headerValue);
            $columnHeader++;
        }

        $fieldKeys = ['hash', 'created_at', 'unlock_time', 'platform', 'network', 'address_lock',
            'address_withdraw', 'real_token_amount', 'base_fee_amount', 'token_fee_amount', 'token_name', 'token_symbol'];
        $listToken = [];
        foreach ($listData as $element) {
            $columnField = 1;
            if (!isset($listToken[$element['token_address']])) {
                $token = $tokenCollection->findOne(['address' => $element['token_address']]);
                $listToken[$element['token_address']] = $token;
            }

            foreach ($fieldKeys as $field) {
                switch ($field) {
                    case 'created_at':
                    case 'unlock_time':
                        $element[$field] = date('d/m/Y H:i:s', $element[$field]);
                        break;
                    case 'token_name':
                        $element[$field] = $listToken[$element['token_address']]['name'] ?? '';
                        break;
                    case 'token_symbol':
                        $element[$field] = $listToken[$element['token_address']]['symbol'] ?? '';
                        break;

                }
                $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                $columnField++;
            }
            $dataRow++;
        }
        ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
        exit();
    }

    /**
     * @return string[]
     */
    protected static function listContractType(): array
    {
        return [
            ContractLibrary::TOKEN_MINTED => 'Token minted',
            ContractLibrary::PRESALE_SETTING => 'Presale setting',
            ContractLibrary::PRESALE_GENERATOR => 'Presale generator',
            ContractLibrary::PRESALE_FACTORY => 'Presale factory',
            ContractLibrary::MINT_TOKEN_SETTING => 'Mint token setting',
            ContractLibrary::MINT_TOKEN_GENERATOR => 'Mint token generator',
            ContractLibrary::MINT_TOKEN_FACTORY => 'Mint token factory',
            ContractLibrary::AIRDROP_SETTING => 'Airdrop setting',
            ContractLibrary::AIRDROP_CONTRACT => 'Airdrop contract',
            ContractLibrary::DEX_FACTORY => 'Dex factory',
        ];
    }
}
