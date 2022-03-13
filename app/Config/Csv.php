<?php


namespace App\Config;


class Csv
{
    const DELIMITER = ",";
    const NEW_LINE  = "\r\n";

    static function addRow($csv, $content) {
        $csv .= implode(Csv::DELIMITER, $content);
        $csv .= Csv::NEW_LINE;
        return $csv;
    }

    static function getResponseHeader($filename) {
        return [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
    }
}
