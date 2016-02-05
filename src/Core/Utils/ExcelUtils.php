<?php

namespace BiSight\Core\Utils;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PHPExcel;
use PHPExcel_IOFactory;
use RuntimeException;

class ExcelUtils
{
    public static function getExcelResponse($excel, $setname, $format)
    {
        switch ($format) {
            case 'xlsx':
                $filename = $setname . '.xlsx';
                $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
                break;
            case 'csv':
                $filename = $setname . '.csv';
                $writer = PHPExcel_IOFactory::createWriter($excel, 'CSV');
                break;
            case 'html':
                $filename = $setname . '.html';
                $writer = PHPExcel_IOFactory::createWriter($excel, 'HTML');
                break;
            default:
                throw new RuntimeException("Unsupported format: " . $format);
        }

        $tmpfile = tempnam('/tmp', 'bisight_download_');
        $writer->save($tmpfile);

        $response = new BinaryFileResponse($tmpfile);

        $d = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Disposition', $d);
        return $response;
    }
}
