<?php


/**********************************************************
 * 
 *  back/App/Traits/Single.php
 * 
 */


namespace App\Traits;


Trait Single
{


/**********************************************************
 * 
 */
public static function _getInstance()
    {
        $_class_name = get_class();

        if ($_class_name::$_instance === false)
            $_class_name::$_instance = new $_class_name;

        return self::$_instance;
    }


/**********************************************************
 * 
 */
public static function __callStatic($method, $params)
    {
        $_instance = self::_getInstance();

        return call_user_func_array(
            array($_instance, "_" . $method),
            $params
        );
    }

}
