<?php


/**********************************************************
 * 
 *  back/core/autoload.php
 * 
 */


function __loadmodule($module_name)
    {
        $_module_name = str_replace(
            '\\',
            PATH_SEP,
            $module_name . ".php"
        );

        $_module_path = __buildpath(Array(
            PATH_ROOT, "back", $_module_name
        ));

        require_once($_module_path);
    }


    spl_autoload_register('__loadmodule');

    