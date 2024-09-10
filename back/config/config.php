<?php


/**********************************************************
 * 
 *  back/config/config.php
 * 
 */


    define('APP_NAME',      'bitraq');
    define('APP_TITLE',     'bitraq');

    define('APP_MODE',      APP_MODE_DEVELOPMENT);


//  Specify a list of config files to be included.
//
//  If this function doesn't exist, then the __configure
//  core function will attempt to include all files in
//  the PATH_CONFIG that have a CONFIG_EXT (.config.php)
//  extension. See:
//
//      back/core/configure.php
//
//  For more info.
//
if (! function_exists('_config_files'))
{

function _config_files()
    {
        return Array(
            "db.config.php",
            "routes.config.php",
            "smtp.config.php"
        );
    }

}