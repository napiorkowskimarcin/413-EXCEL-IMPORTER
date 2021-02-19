<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;


class ExcelReader

{
    private $targetDirectory;
    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }
    
    public function read($originalFilename)
    {
        $spreadsheet = IOFactory::load($this->getTargetDirectory('excel_directory') . $originalFilename);  
        $row = $spreadsheet->getActiveSheet()->removeRow(1); 
        return $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    }

     public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}