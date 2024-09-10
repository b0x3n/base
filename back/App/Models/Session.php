<?php


/*!The Session model class
 * 
 */


namespace App\Models;


use App\Traits\Single;


//  Key definitions used in the $_SESSION and in the
//  array returned by the getAll() method of the
//  Session class.
//
    define('SESSION_USER_ID',       '__session_user_id');
    define('SESSION_USER_STATUS',   '__session_user_status');
    define('SESSION_USER_NAME',     '__session_user_name');

//  Default Guest/logged out user credentials.
//
    define('SESSION_ID_GUEST',      -1);
    define('SESSION_USER_GUEST',    'Guest');

//  User status codes - SESSION_STATUS_NULL is used
//  for non-logged in (Guest) accounts.
//
    define('SESSION_STATUS_NULL',   false);
    define('SESSION_STATUS_LOCKED', 1);
    define('SESSION_STATUS_OK',     2);


Class Session
{

    use                 Single;

    private static      $_instance = false;

    private             $_id;
    private             $_status;
    private             $_username;


/**********************************************************
 * 
 *  Public methods.
 * 
 */


/*!The __construct method.
 *
 *  Does nothing other than refresh the local _id,
 *  _status and _username members of the object.
 * 
 */
public function __construct()
    {
        $this->__refresh();
    }


/*!The _getId() method.
 *
 *  Returns the _id of the current user.
 *
 * @retval int
 * 
 */
public function _getId()
    {
        $this->__refresh();
        return $this->_id;
    }


/*!The _getStatus() method.
 *
 *  Returns the _status of the current user.
 *
 * @retval int
 * 
 */
public function _getStatus()
    {
        $this->__refresh();
        return $this->_status;
    }


/*!The _getUsername() method.
 *
 *  Returns the _username of the current user.
 *
 * @retval string
 * 
 */    
public function _getUsername()
    {
        $this->__refresh();
        return $this->_username;
    }


/*!The _getAll() method.
 *
 *  Returns the _id, _status and _username as
 *  a keyed array.
 * 
 * @retval array
 * 
 */
public function _getAll()
    {
        $this->__refresh();

        return Array(
            SESSION_USER_ID => $this->_id,
            SESSION_USER_NAME => $this->_username,
            SESSION_USER_STATUS => $this->_status,
        );
    }


/*!The loggedIn() method.
 *
 *  Returns true if the useris currently logged in,
 *  returns false, otherwise.
 * 
 * @retval boolean
 * 
 */
public function _loggedIn()
    {
        $this->__refresh();

        if ($this->_id === SESSION_ID_GUEST)
            return false;
        
        return true;
    }


/*!The loggedIn() method.
 *
 *  Returns true if the useris currently logged out,
 *  returns false, otherwise.
 * 
 * @retval boolean
 * 
 */
public function _loggedOut()
    {
        $this->__refresh();

        if ($this->_id !== SESSION_ID_GUEST)
            return false;
        
        return true;
    }


/**********************************************************
 * 
 *  Private methods.
 * 
 */


/*!The __refresh method.
 *
 *  This method should ge called first by any method that
 *  returns either or all of _id, _status and _username.
 * 
 *  It will sync the Session object members with the
 *  current $_SESSION.
 * 
 *  It any of:
 * 
 *      $_SESSION[SESSION_USER_ID]
 *      $_SESSION[SESSION_USER_STATUS]
 *      $_SESSION[SESSION_USER_NAME]
 * 
 *  Are unset, the session is cleared and the user is
 *  automatically logged out (reverts to Guest)
 * 
 */
private function __refresh()
    {
        if (
            (! isset($_SESSION[SESSION_USER_ID])) ||
            (! isset($_SESSION[SESSION_USER_STATUS])) ||
            (! isset($_SESSION[SESSION_USER_NAME]))
        )
        {
            $_SESSION[SESSION_USER_ID] = SESSION_ID_GUEST;
            $_SESSION[SESSION_USER_STATUS] = SESSION_STATUS_NULL;
            $_SESSION[SESSION_USER_NAME] = SESSION_USER_GUEST;
        }

        $this->_id = $_SESSION[SESSION_USER_ID];
        $this->_status = $_SESSION[SESSION_USER_STATUS];
        $this->_username = $_SESSION[SESSION_USER_NAME];
    }

}
