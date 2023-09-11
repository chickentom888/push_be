<?php

namespace Dcore\Library;

use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelHelper
{
    /**
     * @param string $title
     * @param string $maxColumn
     * @return Spreadsheet
     * @throws Exception
     */
    public static function initAndSetStyleHeader($maxColumn, $title = "Report")
    {
        try {
            // <editor-fold desc = "Create new Spreadsheet object">
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getPageSetup()->setFitToHeight(1);
            // </editor-fold>

            // <editor-fold desc = "Set document properties">
            global $config;
            $creator = $config->site->name;
            $spreadsheet->getProperties()->setCreator($creator)
                ->setLastModifiedBy($creator)
                ->setTitle($title)
                ->setSubject($title)
                ->setDescription($title);
            // </editor-fold>

            // <editor-fold desc = "Set style for the name of report">
            $sheet->getRowDimension('1')->setRowHeight(40);
            $sheet->getStyle('A1')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
            $sheet->setCellValue('A1', $title);
            // </editor-fold>

            // <editor-fold desc = "add style to the header">
            $styleArray = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '333333'],
                    ],

                ],
                'fill' => [
                    'type' => Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startcolor' => ['rgb' => '0d0d0d'],
                    'endColor' => ['rgb' => 'f2f2f2'],
                ],
            ];
            $sheet->mergeCells("A1:{$maxColumn}1");
            $sheet->getStyle("A2:{$maxColumn}2")->applyFromArray($styleArray);
            // </editor-fold>

            // <editor-fold desc = "auto fit column to content">
            foreach (range('A', $maxColumn) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // </editor-fold>

            return $spreadsheet;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @param string $fileName
     * @param Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function sendFileToBrowser($spreadsheet, $fileName = "report.xlsx")
    {
        // <editor-fold desc = "Send file to browser">
        $headerFileName = "Content-Disposition: attachment;filename=\"$fileName\"";
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header($headerFileName);
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        // </editor-fold>
    }
}
