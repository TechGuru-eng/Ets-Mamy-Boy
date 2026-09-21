<?php

namespace App\Helpers;

class ExportHelper {
    public static function downloadCsv(string $filename, array $headers, array $data): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
        
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
