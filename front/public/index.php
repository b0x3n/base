<?php


    session_start();


    
/**********************************************************
 * 
 *  Include the back/core/helpers.php script, this will
 *  provide functions like __defifndef and __buildpath.
 * 
 */

    define('PATH_ROOT', $_SERVER['DOCUMENT_ROOT']);
    define('PATH_SEP', DIRECTORY_SEPARATOR);

    chdir(PATH_ROOT);


    include(
        str_replace(
            '/', PATH_SEP, "back/core/helpers.php"
        )
    );


/**********************************************************
 * 
 *  Run the bootstrap.php script, this configures and
 *  initialises the application.
 * 
 */
    __defifndef('PATH_BOOTSTRAP', __buildpath(Array(
        PATH_ROOT, "back", "boot", "bootstrap.php"
    )));

    if (! is_file(PATH_BOOTSTRAP)) {
        echo "Fatal error - couldn\'t find bootstrap.php<br>";
        exit(1);
    }

    include(PATH_BOOTSTRAP);


    exit(0);

    