<?php


/**********************************************************
 * 
 *  back/App/Controllers/BaseController.php
 * 
 */


namespace App\Controllers;


use App\Models\Request;
use App\Models\Session;
use App\Models\Messages;


use App\Controllers\Route;
use App\Controllers\Render;

use \PDO;


use App\Traits\Errors;


    __defifndef('PATH_PULIC', __buildpath(Array(PATH_ROOT, "front", "public")));
    __defifndef('PATH_ROUTES', __buildpath(Array(PATH_ROOT, "front", "routes")));
    __defifndef('PATH_VIEWS', __buildpath(Array(PATH_ROOT, "front", "views")));


Class BaseController
{

    use                 Errors;

    protected           $_request;
    protected           $_session;
    protected           $_messages;
    protected           $_route;

    protected           $_actions;

    private             $_error_message;


public function __construct()
    {
    }


public function handleRequest()
    {
        $this->_request = new Request();

        $this->_session = Session::_getInstance();
        $this->_messages = Messages::_getInstance();
        $this->_route = Route::_getInstance();

    //  The begin method of the _route object should
    //  return a list of one or more actions.
    //
    //  See back/App/Controllers/Route.php
    //
        $_valid = $this->_route->begin($this->_request);

        if ($this->_route->isError())
            return $this->_setError($this->_route->isError());

        if ($_valid === false)
            return Render::renderPage("Pages/404.php", Array(), 404);
  
        if ($this->isError())
            return;

    //  Fine - the ctions are banked and the __executeActions
    //  method is called.
    //
        $this->_actions = $_valid;

        return $this->__executeActions();
    }


private function __executeActions()
    {
        foreach ($this->_actions[ROUTE_ACTIONS] as $index=>$action)
        {
        //  An action can be either an anonymous function,
        //  or a reference to a controller:method or just
        //  controller.
        //
            if (is_callable($action))
                $_status = call_user_func_array(
                    $action,
                    $this->_actions[ROUTE_PARAMS][$index]
                );
            else
                $_status = $this->__execController($index);
        }

        $this->_setError(Render::_getInstance()->isError());
    }


protected function __execController($index)
    {
        $_class_params = explode(':', $this->_actions[ROUTE_ACTIONS][$index]);

        if (count($_class_params) < 1)
            return $this->_setError("Route {$batched[BATCH_ROUTE][$index]} has no action?<br>");

        $_instance = new $_class_params[0]();

        if (count($_class_params) == 1)
        {
            return call_user_func_array(
                Array($_instance, '__init'),
                $this->_actions[ROUTE_PARAMS][$index]        
            );
        }
        else
        {
            return call_user_func_array(
                Array($_instance, $_class_params[1]),    
                $this->_actions[ROUTE_PARAMS][$index]        
            );
        }

    }

}

