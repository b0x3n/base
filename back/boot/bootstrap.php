<?php


/**********************************************************
 * 
 *  back/boot/bootstrap.php
 * 
 */


    __defifndef('APP_MODE_DEVELOPMENT', 0);
    __defifndef('APP_MODE_LIVE',        1);


//  Include the core config file, this may define
//  options that affect other core scripts yet to
//  be included.
//
    __defifndef('PATH_CONFIG', __buildpath(Array(
        PATH_ROOT, "back", "config"
    )));
    __loadcore("config.php", PATH_CONFIG);


//  The core config should define APP_MODE, if not
//  default to APP_MODE_DEVELOPMENT.
//
    __defifndef('APP_MODE', APP_MODE_DEVELOPMENT);


//  If in APP_MODE_DEVELOPMENT, output all warnings
//  and errors.
//
    if (APP_MODE === APP_MODE_DEVELOPMENT) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }


//  Now load the configure.php core script, this is
//  used to load any other config files - see the
//  back/core/configure.php script for more info.
//
    __loadcore("configure.php");


//  The configure.php script provides the __configure
//  core function - this is used to load core scripts
//  from back/config.
//
    __configure();


//  Include the remaining core scripts.
//
    __loadcore("autoload.php");


    $_baseController = new \App\Controllers\BaseController();

    $_baseController->handleRequest();


    