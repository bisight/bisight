<?php

namespace BiSight\Core\ResultSetRenderer;

use BiSight\Core\Driver\ResultSetInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use BiSight\Core\Utils\ExpressionUtils;
use PHPExcel;
use PHPExcel_IOFactory;

class ExcelResultSetRenderer
{
    public function render(ResultSetInterface $res, $offset = 0, $limit = null)
    {
        
        $excel = new PHPExcel();
        
        $setname = 'export';

        $properties = $excel->getProperties();
        $properties->setCreator("BiSight Portal");
        $properties->setLastModifiedBy("BiSight Portal");
        $properties->setTitle($setname);
        $properties->setSubject($setname);
        //$properties->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        //$properties->setKeywords("office 2007 openxml php");
        //$properties->setCategory("Test result file");

        $sheet = $excel->setActiveSheetIndex(0);

        $columns = $res->getColumns();

        $i = 0;
        foreach ($columns as $column) {
            $sheet->setCellValueByColumnAndRow($i, 1, $column->getLabel());
            $i++;
        }

        $rowIndex = 2;
        while ($row = $res->getRow()) {
            $i = 0;
            foreach ($row as $key => $value) {
                $sheet->setCellValueByColumnAndRow($i, $rowIndex, $value);
                $i++;
            }
            $rowIndex++;
        }
        $sheet->setTitle($setname);
        return $excel;
    }
}
