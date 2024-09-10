<?php


/**********************************************************
 * 
 *  back/core/configure.php
 * 
 */


    __defifndef('PATH_CONFIG', __buildpath(Array(
        PATH_ROOT, "config"
    )));


    __defifndef('CONFIG_EXT', '.config.php');


/*!The __configure core function.
 */
function __configure()
    {
        if (! is_dir(PATH_CONFIG)) {
            echo "Fata error - couldn\'t find PATH_CONFIG directory " . PATH_CONFIG . "<br>";
            exit(1);
        }

        if (function_exists('_config_files'))
        //  Load an array of config files returned
        //  by the _config_files function from
        //  CPATH_CONFIG.
        //
            __loadconfiglist();
        else
        //  Load all files in PATH_CONFIG with a
        //  CONFIG_EXT extension.
            __loadconfigs();
    }


/*!The __loadconfiglist core function.
 */
function __loadconfiglist()
    {
        foreach (_config_files() as $config)
        {
            $_config_path = __buildpath(Array(
                PATH_CONFIG, $config
            ));

            if (! is_file($_config_path))
            {
                echo "Fatal error - couldn\'t load config file $_config_path<br>";
                exit(1);
            }

            include($_config_path);
        }
    }


/*!The __loadconfigs core function.
 */
function __loadconfigs()
    {
        if (($_dir = opendir(PATH_CONFIG)) === false)
        {
            echo "Fatal error - couldn\'t open config path " . CONFIG_PATH . "<br>";
            exit(1);
        }

        while ($_entry = readdir($_dir))
        {
            if (substr($_entry, 0, 1) == ".")
                continue;

            $_ext = substr($_entry, (strlen($_entry) - strlen(CONFIG_EXT)));
        
            if ($_ext !== CONFIG_EXT)
                continue;

            include(__buildpath(Array(PATH_CONFIG, $_entry)));
        }

        closedir($_dir);
    }

