<?php


namespace App\Traits;


use App\Traits\Errors;


    __defifndef('PATH_LOGS', __buildpath(Array(
        PATH_ROOT, "logs"
    )));

    __defifndef('PATH_ERRORLOG', __buildpath(Array(
        PATH_LOGS, "errors"
    )));


Trait Log
{

public function logError($error_message)
    {
        if (($_stream = fopen(PATH_ERRORLOG, "a")) === false)
            return "Error writing to log file " . PATH_ERRORLOG;

        fwrite($_stream, date('d/m/Y h:i:sa') . " " . $_SERVER['REMOTE_ADDR'] . " " . $error_message . PHP_EOL);
        fclose($_stream);
        
        return true;
    }

}

