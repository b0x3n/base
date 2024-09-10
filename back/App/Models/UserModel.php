<?php


namespace App\Models;


use App\Models\DataModel;
use App\Controllers\Render;


use \PDO;


Class UserModel extends DataModel
{

    protected           $messages;


public function __construct()
    {
        $this->messages = Messages::getInstance();

        parent::__construct();

        if (isset($this->_tableName) && isset($this->_tableSchema))
        {
            $this->initialiseTable($this->_tableName, $this->_tableSchema);

            if ($this->isError())
                $this->messages->_pushMessage(MESSAGES_ERROR, $this->isError());
        }
    }


public function render($page, $data = Array(), $status = 200)
    {
        $_messages = Messages::getAll();

        foreach ($_messages as $key=>$messages)
            $data[$key] = $messages;

        return Render::renderPage($page, $data, $status);
    }


public function validateInput(&$input, $expected)
    {
        $_messages = $this->messages;

        foreach ($expected as $key=>$value)
        {
            if (! isset($input[$key]) || empty(trim($input[$key]))) {
            //  If it's a required field and the field is
            //  not set, an error message is generated.
            //
                if ($value === true)
                {
                    $_messages->_pushMessage(MESSAGES_ERROR, "Required key $key not present");
                    return false;
                }

            //  Otherwise it's initialised as an empty
            //  string
                else
                    $input[$key] = $value;
            }
            else
                $input[$key] = filter_var($input[$key], FILTER_SANITIZE_STRING);
        }

        return true;
    }


public function generateEmailValidationLink($username)
    {
        $_rndString = $username . "&" . substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1).substr(md5(time()),1);
    }

}

