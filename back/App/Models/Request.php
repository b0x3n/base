<?php


/*!The Request model class
 */


namespace App\Models;


//  Array keys returned by the getAll() method.
//
    define('REQUEST_URL',           '__request_url');
    define('REQUEST_PARAMS',        '__request_params');
    define('REQUEST_METHOD',        '__request_method');


//  Default _url if $_GET['url'] is unset or empty.
//
    define('REQUEST_URL_DEFAULT',   '/');


Class Request
{

    private             $_url;
    private             $_params;
    private             $_method;


/**********************************************************
 * 
 *  Public methods.
 * 
 */


/*!The __construct method.
 *
 *  Does nothing other than call the _refresh method,
 *  this grabs the $_GET['url'] and creates the _params,
 *  see the comments for the _refresh() method for
 *  more.
 * 
 */
public function __construct()
    {
        $this->_refresh();
    }


/*!The getURL method.
 *
 *  Returns the _url member of the class.
 * 
 * @retval string
 * 
 */
public function getURL()
    {
        return $this->_url;
    }


/*!The getParams method.
 *
 *  Returns the _params member of the class.
 * 
 * @retval array
 * 
 */
public function getParams()
    {
        return $this->_params;
    }


/*!The getMethod method.
 *
 *  Returns the _method member of the class.
 * 
 * @retval string
 * 
 */
public function getMethod()
    {
        return $this->_method;
    }


/*!The getAll method.
 *
 *  Returns the _url, _params and _method members
 *  of the class in an associative array - the
 *  array returns the following keys:
 * 
 *      REQUEST_URL => _url
 *      REQUEST_PARAMS => _params
 *      REQUEST_METHOD => _method
 * 
 * @retval string
 * 
 */
public function getAll()
    {
        return Array(
            REQUEST_URL => $this->_url,
            REQUEST_PARAMS => $this->_params,
            REQUEST_METHOD => $this->_method
        );
    }


/**********************************************************
 * 
 *  Private methods.
 * 
 */


/*!The _refresh method.
 *
 *  First, will set _url to $_GET['url'], if $_GET['url']
 *  is unset or empty - it will set _url to the default
 *  REQUEST__URL_DEFAULT (/).
 * 
 *  Next, it parses the _url into an array of parameters
 *  (_params) by splitting the _url at the / characters.
 * 
 *  Lastly, it sets the _method member to whatever the
 *  valueof $_SERVER['REQUEST_METHOD'] is.
 * 
 */
protected function _refresh()
    {
        if (! isset($_GET['url']) || empty(trim($_GET['url'])))
            $this->_url = REQUEST_URL_DEFAULT;
        else
            $this->_url = $_GET['url'];

        $this->_params = preg_split(
            '/\//', 
            filter_var($this->_url, FILTER_SANITIZE_URL),
            null,
            PREG_SPLIT_NO_EMPTY
        );

        $this->_method = $_SERVER['REQUEST_METHOD'];
    }

}

