<?php

namespace FeedzRecoloca\Libs;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class SpreadsheetGenerate
{

    private $reportTile; //nome do relatório
    private $profileCreatorName; //nome de quem gerou o relatório
    private $spreadsheeet; //classe mãe
    private $sheet; //aba ataual de escrita
    private $fileFormat; //formato do arquivo
    private $displayHeader; //mostrar ou não mostrar header adicional de informações
    private $currentLine; //linha atual de escrita
    private $currentColumn; //coluna atual de escrita


    public function __construct(string $fileFomat = 'xlsx', bool $displayHeader = true)
    {
        $this->spreadsheeet = new Spreadsheet();
        $this->sheet = $this->spreadsheeet->getActiveSheet();
        $this->fileFormat = $fileFomat;
        $this->displayHeader = $displayHeader;
        $this->currentLine = 1;
        $this->currentColumn = 'A';
    }

    /**
     * Creates a header of information in spreadsheet
     *
     * @param string $reportTitle
     * @param string $profileName
     * @return SpreadsheetGenerate
     */
    public function addReportHeaderInformations(string $reportTitle, string $profileName): SpreadsheetGenerate
    {
        $this->reportTile = $reportTitle;
        $this->profileCreatorName = $profileName;

        return $this;
    }

    /**
     * Add a headers names by array
     *
     * @param array $columnNames
     * @return SpreadsheetGenerate
     */
    public function addHeaders(array $columnNames): SpreadsheetGenerate
    {
        foreach ($columnNames as $index => $columnName) {
            $this->writeValue($columnName);
        }

        $this->addNewLine();

        return $this;
    }

    /**
     * Write value to a cell
     *
     * @param $value
     * @return SpreadsheetGenerate
     */
    public function writeValue($value,  $type = "string"): SpreadsheetGenerate
    {
        switch ($type) {
            case 'string':
                $type = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
                break;

            case 'numeric':
                $type = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC;
                break;

            case 'formula':
                $type = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA;
                break;
            
            default:
                $type = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
                break;
        }

        $this->sheet->setCellValueExplicit($this->getCoordinate(), $value, $type);
        
        $this->currentColumn++;
        return $this;
    }

    /**
     * Writes a array of values in line and add new line
     *
     * @param arrau $values
     * @return SpreadsheetGenerate
     */
    public function writeValues(array $values): SpreadsheetGenerate
    {
        foreach ($values as $index => $value) {
            $this->writeValue($value);
        }

        $this->addNewLine();

        return $this;
    }

    /**
     * Add new line in Spreadsheet
     */
    public function addNewLine()
    {
        $this->currentLine++;
        $this->currentColumn = 'A';
    }

    /**
     * Return a coordinate XY of a actual cell
     *
     * @return string
     */
    private function getCoordinate()
    {
        return $this->currentColumn . $this->currentLine;
    }

    public function createFile($path = 'php://output')
    {
        if ($this->fileFormat === 'xlsx') {
            return (new Xlsx($this->spreadsheeet))->save($path);
        }

        return (new Xls($this->spreadsheeet))->save($path);
    }


    public function downloadFile($fileName)
    {
        header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
        header("Content-type:   application/x-msexcel; charset=utf-8");
        header("Cache-Control: no-store, no-cache");
        header("Content-Disposition: attachment; filename=$fileName");
        $file = fopen('php://output','w');
        exit;
    }
}