<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Logger
{

    public function log($type, $text = '', $timestamp = true)
    {
        try {
            $logPrint = env('LOG_PRINT_ENABLE') ?? 1;
            if ($logPrint == 1) {
                if(gettype($text) == 'array'){
                    $text = json_encode($text);
                }
                if ($timestamp) {
                    $datetime = date("d-m-Y H:i:s");
                    $text = "$datetime, $type: $text \r\n\r\n";
                } else {
                    $text = "$type\r\n\r\n";
                }
                Log::info($text);
            }
        } catch (\Exception $e) {
            Log::info("log exception");
        }

    }
}
