<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ExcelHelper;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use DCrypto\Networks\BinanceWeb3;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TokenMintedController extends ExtendedControllerBase
{
    public function initialize($param = null)
    {
        $this->checkLogin();
        parent::initialize();
        $this->getConnectedWallet();
    }

    /**
     * @throws Exception
     */
    public function indexAction()
    {
        $tokenMintedCollection = $this->mongo->selectCollection('token_minted');
        $limit = 20;
        $dataGet = $this->getData;
        $p = $dataGet['p'];
        if ($p <= 1) $p = 1;
        $cp = ($p - 1) * $limit;

        $conditions = [];
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }

        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if (strlen($dataGet['address'])) {
            $mainCurrency = Adapter::getMainCurrency($dataGet['platform']);
            $coinInstance = Adapter::getInstance($mainCurrency ?? BinanceWeb3::MAIN_CURRENCY);
            $conditions['$or'] = [
                ['contract_address' => $coinInstance->toCheckSumAddress($dataGet['address'])],
                ['user_address' => $coinInstance->toCheckSumAddress($dataGet['address'])],
            ];
        }

        if ($dataGet['export']) {
            $fileName = 'token_minted_' . date("Ymd_his") . '.xlsx';
            $listData = $tokenMintedCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            //</editor-fold>

            $title = 'Report Token minted';
            $headerColumn = ['Hash', 'Time', 'Platform', 'Network', 'Address', 'Token name',
                'Token symbol', 'Supply', 'Version', 'Creation fee', 'Token fee amount'];
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

            $fieldKeys = ['hash', 'created_at', 'platform', 'network', 'contract_address', 'name',
                'symbol', 'total_supply', 'contract_version', 'creation_fee', 'fee_amount'];
            foreach ($listData as $element) {
                $columnField = 1;
                foreach ($fieldKeys as $field) {
                    if ($field == 'created_at') {
                        $element[$field] = date('d/m/Y H:i:s', $element['created_at']);
                    }
                    $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                    $columnField++;
                }
                $dataRow++;
            }

            ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
            exit();
        }

        $options = [
            'skip' => $cp,
            'limit' => $limit,
            'sort' => ['_id' => -1]
        ];

        $listData = $tokenMintedCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $count = $tokenMintedCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $this->view->setVars(compact('listData', 'pagingInfo', 'listPlatform', 'listNetwork', 'dataGet', 'count'));
    }
}
