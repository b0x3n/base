<?php


/**********************************************************
 * 
 *  back/App/Traits/Errors.php
 * 
 */


namespace App\Traits;


use App\Traits\Log;


Trait Errors
{

    use                 Log;

/**********************************************************
 * 
 *  _setError()
 * 
 *  Used to set the $_error_message member of a class,
 *  this can be handled using the isError() method
 *  (below).
 * 
 *  Always returns false - this way we can set the
 *  error message and return false as an indicator in
 *  a single line:
 * 
 *      return $this->_setError("Something bad happened");
 * 
 */
public function _setError($error_message = false)
    {
        if ($error_message === false)
            $this->_error_message = false;
        else {
            if ($this->_error_message === false)
                $this->_error_message = "";

            $this->_error_message .= $error_message;
        }
        
        return false;
    }


/**********************************************************
 * 
 *  isError()
 * 
 *  Returns the $_error_message string from the class.
 * 
 *  If no errors are recorded this will be false which
 *  indicates success.
 * 
 *  If $report_error is sset to true then this method
 *  will echo any $_errmr_messages.
 * 
 *  This allows quick handling of error messages:
 * 
 *      if ($_service->isError(true)) exit(1);
 * 
 */
public function isError($report_error = false)
    {
        if ($this->_error_message !== false && trim($this->_error_message) !== "")
        {
            $this->logError($this->_error_message);

            if ($report_error !== false)
                echo $this->_error_message . "<br>";
        }

        return $this->_error_message;
    }
    
}
