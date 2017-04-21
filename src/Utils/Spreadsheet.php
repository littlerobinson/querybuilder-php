<?php

namespace Littlerobinson\QueryBuilder\Utils;

/**
 * Class Spreadsheet
 * @package Littlerobinson\QueryBuilder\Utils
 * @see https://github.com/PHPOffice/PhpSpreadsheet
 */
class Spreadsheet
{
    private $columns;
    private $data;
    private $helper;
    private $spreadsheet;
    private $creator;
    private $lastModifiedBy;
    private $title;
    private $subject;
    private $description;
    private $keywords;
    private $category;
    private $sheetTitle;
    private $activeSheetIndex;

    /**
     * Spreadsheet constructor.
     * @param array $columns
     * @param array $data
     */
    public function __construct(array $columns = [], array $data = [])
    {
        $this->columns          = $columns;
        $this->data             = $data;
        $this->creator          = '';
        $this->lastModifiedBy   = '';
        $this->title            = '';
        $this->subject          = '';
        $this->description      = '';
        $this->keywords         = '';
        $this->category         = '';
        $this->sheetTitle       = '';
        $this->activeSheetIndex = 0;

        $this->helper = new \PhpOffice\PhpSpreadsheet\Helper\Sample();
        if ($this->helper->isCli()) {
            echo 'This should only be run from a Web Browser' . PHP_EOL;
            return;
        }

        // Create new Spreadsheet object
        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    }

    /**
     * Set properties method
     */
    private function setProperties()
    {
        /// Set document properties
        $this->spreadsheet->getProperties()->setCreator('Eductive Group')
            ->setLastModifiedBy($this->getLastModifiedBy())
            ->setTitle($this->getTitle())
            ->setSubject($this->getSubject())
            ->setDescription($this->getDescription())
            ->setKeywords($this->getKeywords());
    }

    /**
     * Generate document method
     * @param string $fileType
     * @param string $fileName
     */
    public function generate(string $fileType, string $fileName)
    {
        switch ($fileType) {
            case 'Excel5':
                $this->generateExcel5($fileName);
                break;
            case 'PDF':
                $this->generatePDF($fileName);
                break;
            default:
                $this->generateExcel5($fileName);
        }
    }

    /**
     * Method for generating XLS document
     * @param $fileName
     */
    private function generateExcel5($fileName)
    {
        /// Set document properties
        $this->setProperties();

        $worksheet = $this->spreadsheet->setActiveSheetIndex(0);

        $lastColumn = $worksheet->getHighestColumn();
        $lastRow    = $worksheet->getHighestRow();

        /// Add columns
        foreach ($this->columns as $key => $column) {
            $cell = $worksheet->getCell($lastColumn . $lastRow);
            $cell->setValue($column->label);
            $lastColumn++;
        }

        $lastColumn = 'A';
        $lastRow    = 2;

        $obj = new \ArrayObject($this->data);
        $it  = $obj->getIterator();
        while ($it->valid()) {
            $currentData = $it->current();
            /// Loop on columns
            foreach ($currentData as $data) {
                $cell = $worksheet->getCell($lastColumn . $lastRow);
                $cell->setValue($data);
                $lastColumn++;
            }
            $lastColumn = 'A';
            $lastRow++;
            $it->next();
        }

        /// Rename worksheet
        $this->spreadsheet->getActiveSheet()->setTitle('Extraction');

        /// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $this->spreadsheet->setActiveSheetIndex(0);

        /// Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');
        /// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        /// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->spreadsheet, 'Excel5');
        $writer->save('php://output');
        exit;
    }


    /*----------------------------------------------------------------------------------------------------------------*/
    /*----------------------------------------------- ACCESSORS ------------------------------------------------------*/
    /*----------------------------------------------------------------------------------------------------------------*/

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * @param mixed $fileType
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }

    /**
     * @return string
     */
    public function getCreator(): string
    {
        return $this->creator;
    }

    /**
     * @param string $creator
     */
    public function setCreator(string $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return string
     */
    public function getLastModifiedBy(): string
    {
        return $this->lastModifiedBy;
    }

    /**
     * @param string $lastModifiedBy
     */
    public function setLastModifiedBy(string $lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getSheetTitle(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @param string $sheetTitle
     */
    public function setSheetTitle(string $sheetTitle)
    {
        $this->sheetTitle = $sheetTitle;
    }

    /**
     * @return int
     */
    public function getActiveSheetIndex(): int
    {
        return $this->activeSheetIndex;
    }

    /**
     * @param int $activeSheetIndex
     */
    public function setActiveSheetIndex(int $activeSheetIndex)
    {
        $this->activeSheetIndex = $activeSheetIndex;
    }
}