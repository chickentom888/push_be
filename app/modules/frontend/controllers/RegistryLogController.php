<?php

namespace Dcore\Modules\Frontend\Controllers;

use Dcore\Library\ContractLibrary;
use Dcore\Library\ExcelHelper;
use Dcore\Library\Helper;
use DCrypto\Adapter;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RegistryLogController extends ExtendedControllerBase
{

    public function initialize($param = null)
    {
        parent::initialize($param);
        $this->checkLogin();
    }

    /**
     * @throws Exception
     */
    public function presaleSettingAction()
    {
        $type = ContractLibrary::PRESALE_SETTING;
        $page_title = 'Presale setting history';
        $this->exportView($type, $page_title, 'exportExcelPresaleSetting');
    }

    /**
     * @throws Exception
     */
    public function saleSettingAction()
    {
        $type = ContractLibrary::SALE_SETTING;
        $page_title = 'Sale setting history';
        $this->exportView($type, $page_title, 'exportExcelSaleSetting');
    }

    /**
     * @throws Exception
     */
    public function airdropSettingAction()
    {
        $type = ContractLibrary::AIRDROP_SETTING;
        $page_title = 'Airdrop setting history';
        $this->exportView($type, $page_title, 'exportExcelAirdropSetting');
    }

    /**
     * @throws Exception
     */
    public function mintTokenSettingAction()
    {
        $type = ContractLibrary::MINT_TOKEN_SETTING;
        $page_title = 'Mint token setting history';
        $this->exportView($type, $page_title, 'exportExcelMintTokenSetting');
    }

    /**
     * @throws Exception
     */
    public function lockSettingAction()
    {
        $type = ContractLibrary::LOCK_SETTING;
        $page_title = 'Lock setting history';
        $this->exportView($type, $page_title, 'exportExcelLockSetting');
    }


    protected function exportView($logType, $pageTitle, $functionExportName)
    {
        $type = $logType;
        $page_title = $pageTitle;
        $registryLogCollection = $this->mongo->selectCollection('registry_log');
        $dataGet = $this->getData;
        $listPlatform = Adapter::listPlatform();
        $listNetwork = Adapter::listNetwork();
        $listContractType = $this->listContractType();

        $conditions = [];
        if ($type) {
            $conditions['type'] = $type;
        }
        if (strlen($dataGet['platform'])) {
            $conditions['platform'] = $dataGet['platform'];
        }
        if (strlen($dataGet['network'])) {
            $conditions['network'] = $dataGet['network'];
        }

        if ($dataGet['export']) {
            $listData = $registryLogCollection->find($conditions, ['sort' => ['_id' => -1]]);
            !empty($listData) && $listData = $listData->toArray();
            $this->{$functionExportName}($listData);
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

        $count = $registryLogCollection->countDocuments($conditions);
        $pagingInfo = Helper::paginginfo($count, $limit, $p);
        $listData = $registryLogCollection->find($conditions, $options);
        !empty($listData) && $listData = $listData->toArray();
        $this->view->setVars(compact('listData', 'pagingInfo', 'dataGet', 'listPlatform',
            'listNetwork', 'listContractType', 'page_title'));
    }

    protected function exportExcelPresaleSetting($listData)
    {
        $fileName = 'Presale_setting_history_' . date("Ymd_his") .'.xlsx';

        $title = 'Presale setting history';
        $headerColumn = ['Platform', 'Network', 'Old value', 'Value', 'Time',];
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

        $fieldKeys = ['platform', 'network', 'old_value', 'value', 'created_at'];
        foreach ($listData as $element) {
            $columnField = 1;
            foreach ($fieldKeys as $field) {
                switch ($field) {
                    case 'created_at':
                        $element[$field] = date('d/m/Y H:i:s', $element['created_at']);
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                        break;
                    case 'old_value':
                    case 'value':
                        $cellValue = "Base fee percent: {$element[$field]['base_fee_percent']}\n";
                        $cellValue .= "Base fee address: {$element[$field]['base_fee_address']}\n";
                        $cellValue .= "Token fee percent: {$element[$field]['token_fee_percent']}\n";
                        $cellValue .= "Token fee address: {$element[$field]['token_fee_address']}\n";
                        $cellValue .= "Creation fee: {$element[$field]['creation_fee']}\n";
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $cellValue);
                        $sheet->getStyleByColumnAndRow($columnField, $dataRow)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                }
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

    protected function exportExcelSaleSetting($listData)
    {
        $fileName = 'Sale_setting_history_' . date("Ymd_his") .'.xlsx';

        $title = 'Sale setting history';
        $headerColumn = ['Platform', 'Network', 'Old value', 'Value', 'Time',];
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

        $fieldKeys = ['platform', 'network', 'old_value', 'value', 'created_at'];
        foreach ($listData as $element) {
            $columnField = 1;
            foreach ($fieldKeys as $field) {
                switch ($field) {
                    case 'created_at':
                        $element[$field] = date('d/m/Y H:i:s', $element['created_at']);
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                        break;
                    case 'old_value':
                    case 'value':
                        $cellValue = "Base fee percent: {$element[$field]['base_fee_percent']}\n";
                        $cellValue .= "Base fee address: {$element[$field]['base_fee_address']}\n";
                        $cellValue .= "Token fee percent: {$element[$field]['token_fee_percent']}\n";
                        $cellValue .= "Token fee address: {$element[$field]['token_fee_address']}\n";
                        $cellValue .= "Creation fee: {$element[$field]['creation_fee']}\n";
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $cellValue);
                        $sheet->getStyleByColumnAndRow($columnField, $dataRow)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                }
                $columnField++;
            }
            $dataRow++;
        }

        ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
        exit();
    }

    protected function exportExcelLockSetting($listData)
    {
        $fileName = 'lock_setting_history_' . date("Ymd_his") .'.xlsx';

        $title = 'Lock setting history';
        $headerColumn = ['Platform', 'Network', 'Old value', 'Value', 'Time',];
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

        $fieldKeys = ['platform', 'network', 'old_value', 'value', 'created_at'];
        foreach ($listData as $element) {
            $columnField = 1;
            foreach ($fieldKeys as $field) {
                switch ($field) {
                    case 'created_at':
                        $element[$field] = date('d/m/Y H:i:s', $element['created_at']);
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                        break;
                    case 'old_value':
                    case 'value':
                        $cellValue = "Base fee: {$element[$field]['base_fee']}\n";
                        $cellValue .= "Token fee percent: {$element[$field]['token_fee_percent']}\n";
                        $cellValue .= "Fee address: {$element[$field]['address_fee']}\n";
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $cellValue);
                        $sheet->getStyleByColumnAndRow($columnField, $dataRow)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                }
                $columnField++;
            }
            $dataRow++;
        }

        ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
        exit();
    }

    protected function exportExcelAirdropSetting($listData)
    {
        $fileName = 'airdrop_setting_history_' . date("Ymd_his") .'.xlsx';

        $title = 'Airdrop setting history';
        $headerColumn = ['Platform', 'Network', 'Old value', 'Value', 'Time',];
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

        $fieldKeys = ['platform', 'network', 'old_value', 'value', 'created_at'];
        foreach ($listData as $element) {
            $columnField = 1;
            foreach ($fieldKeys as $field) {
                switch ($field) {
                    case 'created_at':
                        $element[$field] = date('d/m/Y H:i:s', $element['created_at']);
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                        break;
                    case 'old_value':
                    case 'value':
                        $cellValue = "Fee Amount: {$element[$field]['fee_amount']}\n";
                        $cellValue .= "Fee address: {$element[$field]['fee_address']}\n";
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $cellValue);
                        $sheet->getStyleByColumnAndRow($columnField, $dataRow)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                }
                $columnField++;
            }
            $dataRow++;
        }

        ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
        exit();
    }

    protected function exportExcelMintTokenSetting($listData)
    {
        $fileName = 'mint_token_setting_history_' . date("Ymd_his") .'.xlsx';

        $title = 'Mint token setting history';
        $headerColumn = ['Platform', 'Network', 'Old value', 'Value', 'Time',];
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

        $fieldKeys = ['platform', 'network', 'old_value', 'value', 'created_at'];
        foreach ($listData as $element) {
            $columnField = 1;
            foreach ($fieldKeys as $field) {
                switch ($field) {
                    case 'created_at':
                        $element[$field] = date('d/m/Y H:i:s', $element['created_at']);
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                        break;
                    case 'old_value':
                    case 'value':
                        $cellValue = "Creation fee: {$element[$field]['creation_fee']}\n";
                        $cellValue .= "Total supply fee percent: {$element[$field]['total_supply_fee_percent']}\n";
                        $cellValue .= "Token fee address: {$element[$field]['token_fee_address']}\n";
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $cellValue);
                        $sheet->getStyleByColumnAndRow($columnField, $dataRow)->getAlignment()->setWrapText(true);
                        break;
                    default:
                        $sheet->setCellValueByColumnAndRow($columnField, $dataRow, $element[$field]);
                }
                $columnField++;
            }
            $dataRow++;
        }

        ExcelHelper::sendFileToBrowser($spreadsheet, $fileName);
        exit();
    }
}
