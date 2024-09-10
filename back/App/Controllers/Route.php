<?php


/**********************************************************
 * 
 *  back/App/Controllers/Router.php
 * 
 */


namespace App\Controllers;


use App\Traits\Errors;
use App\Traits\Single;


    __defifndef('PATH_ROUTES', __buildpath(Array(PATH_ROOT, "front", "routes")));
    __defifndef('ROUTES_EXT', '.routes.php');


    define('ROUTE_ROUTES',      '__route_routes');
    define('ROUTE_ACTIONS',     '__route_actions');
    define('ROUTE_PARAMS',      '__route_params');


Class Route
{

    use                 Errors;
    use                 Single;

    private static      $_instance = false;

    protected           $_route;
    protected           $_action;
    protected           $_params;

    protected           $_request;

    private             $_error_message;


public function __construct()
    {
        $this->_route = Array();
        $this->_action = Array();
        $this->_params = Array();
    }


public function begin($_request)
    {
        $this->_request = $_request;

        if ($this->__loadRoutes() === false)
            return false;

        if (count($this->_action) < 1)
            return false;
            
        return Array(
            ROUTE_ROUTES => $this->_route,
            ROUTE_ACTIONS => $this->_action,
            ROUTE_PARAMS => $this->_params
        );
    }


private function __loadRoutes()
    {
        if (! is_dir(PATH_ROUTES))
            return $this->_setError("Fatal error - routes directory " . PATH_ROUTES .  " not found");

        if (function_exists('_route_files'))
            return $this->__loadRouteList();
        else
            return $this->__loadAllRoutes();
    }


private function __loadRouteList()
    {
        foreach(_route_files() as $route_file)
        {
            $_path = __buildpath(Array(PATH_ROUTES, $route_file));

            if (! is_file($_path))
                return $this->_setError("Error loading routes file $_path");
            
            include($_path);
        }

        return true;
    }


private function __loadAllRoutes()
    {
        if (($_dir = opendir(PATH_ROUTES)) === false)
            return $this->_setError("Error opening routes directory " . PATH_ROUTES);
        
        while($_entry = readdir($_dir))
        {
            if (substr($_entry, 0, 1) == ".")
                continue;
            
            $_ext = substr($_entry, (strlen($_entry) - strlen(ROUTES_EXT)));

            if ($_ext != ROUTES_EXT)
                continue;

            include(__buildpath(Array(PATH_ROUTES, $_entry)));
        }

        closedir($_dir);

        return true;
    }


public function _get($route, $action)
    {
        if ($this->_request->getMethod() !== 'GET')
            return false;

        $_params = $this->__isValidRoute($route);

        if (is_array($_params))
        {
            $this->__addRoute($route, $action, $_params);
            return true;
        }

        return false;
    }


public function _post($route, $action)
    {
        if ($this->_request->getMethod() !== 'POST')
            return false;

        $_params = $this->__isValidRoute($route);

        if (is_array($_params))
        {
            $this->__addRoute($route, $action, $_params);
            return true;
        }

        return false;
    }


public function __isValidRoute($route)
    {
        $_url_params = $this->_request->getParams();
        
        $_route_params = preg_split(
            '/\//',
            filter_var($route, FILTER_SANITIZE_URL),
            null,
            PREG_SPLIT_NO_EMPTY
        );

        if (count($_route_params) != count($_url_params))
            return false;

        $_params = Array();

        foreach ($_url_params as $index=>$param)
        {
            if (substr($_route_params[$index], 0, 1) == '$')
                $_params[substr($_route_params[$index], 1)] = $param;
            else
            {
                if ($_route_params[$index] !== $param)
                    return false;
            }
        }

        return $_params;
    }


private function __addRoute($route, $action, $params)
    {
        array_push($this->_route, $route);
        array_push($this->_action, $action);
        array_push($this->_params, $params);
    }

}

