<?php


/**********************************************************
 * 
 *  back/core/helpers.php
 * 
 *  Some simple but fairly useful functions.
 * 
 */


/*!the __defifndef core function.
 */
function __defifndef($key, $value)
    {
        if (! defined($key))
            define($key, $value);
    }


/*!The __buildpath core function.
 */
function __buildpath($path_array)
    {
        $_path = false;

        foreach ($path_array as $path_string)
        {
            if ($_path === false)
                $_path = $path_string;
            else
                $_path .= PATH_SEP . $path_string;
        }

        return $_path;
    }


    __defifndef('PATH_CORE', __buildpath(Array(
        PATH_ROOT, "back", "core"
    )));


/*!The __loadcore core function.
 */
function __loadcore($script_name, $path = PATH_CORE)
    {
        $_path = __buildpath(Array(
            $path, $script_name
        ));

        if (! is_file($_path)) {
            echo "Fatal error - couldn\'t open core script $_path<br>";
            exit(1);
        }

        include($_path);
    }

