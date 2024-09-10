<?php


/*!The Messages model class
 * 
 */


namespace App\Models;


use App\Traits\Single;


//  Default message stack keys - these are always
//  created - if no messages exist from a previous
//  request these will point to empty arrays in
//  _messages.
//
    define('MESSAGES_ERROR',    'MESSAGES_ERROR');
    define('MESSAGES_NOTIFY',   'MESSAGES_NOTIFY');


//  Any key in the session that matches this pattern
//  will re stored in _messages and cleared from
//  the session.
//
    define('MESSAGES_MATCH',    '/^MESSAGES_[a-zA-Z0-9_]*$/');


Class Messages
{

    use                 Single;

    private static      $_instance = false;
    private             $_messages;


/*!The __construct method.
 *
 *  This method will populate the _messages with
 *  any $_SESSION[] key matching the MESSAGES_MATCH
 *  pattern.
 *
 *  Anything copied from the $_SESSIOn will be
 *  unset in the session. $_SESSION messages tell
 *  us about the current request - the _messages
 *  pass messages from the previous request.
 *
 */
private function __construct()
    {
        $this->_messages = Array();

    //  Default stacks - these are always created,
    //  even if no messages exist.
    //
        $this->_messages[MESSAGES_ERROR] = Array();
        $this->_messages[MESSAGES_NOTIFY] = Array();

    //  Any $_SESSION key that matches MESSAGES_MATCH
    //  will be added to the _messages member and
    //  cleared in the $_SESSION.
    //
        if (isset($_SESSION))
        {
            foreach ($_SESSION as $key=>$value)
            {
                if (preg_match(MESSAGES_MATCH, $key, $match) == 1)
                {
                    $this->_messages[$key] = $value;
                    unset($_SESSION[$key]);
                }
            }
        }

        $_SESSION[MESSAGES_ERROR] = Array();
        $_SESSION[MESSAGES_NOTIFY] = Array();
    }


/*!The _pushMessage() method.
 *
 *  Push a new message to the specific stack_key in
 *  the $_SESSION.
 * 
 *  If the specified stackKey doesn't exist, it is
 *  created and the message added as element 1 in the
 *  stack array.
 * 
 *  If the stackKey is unspecified, it will default
 *  to MESSAGES_ERROR.
 * 
 * 
 * @param string message
 *  The message to be pushed to the message stack.
 * 
 * @param string stackKey
 *  The key of the $_SESSION[] stack the message is to
 *  be written to. 
 *
 */
public function _pushMessage($stackKey = MESSAGES_ERROR, $message)
    {
        if (! isset($_SESSION[$stackKey]))
            $_SESSION[$stackKey] = Array();

        array_push($_SESSION[$stackKey], $message);
    }


/*!The _popMessage method.
 *
 *  Pops and returns a message from one of the local
 *  _message stacks.
 * 
 *  As suggested - each _messages[] stack is just an
 *  array that messages are pushed onto and popped off
 *  of, when we pop the message the last message added
 *  to that message stack is removed from the stack
 *  and returned.
 * 
 *  If the stack is empty or stackKey refers to a stack
 *  that doesn't exist, false is retured.
 * 
 *  If no stackKey is specified, then it will default
 *  to MESSAGES_ERROR.
 * 
 * 
 * @param string stackKey
 *  The stack key identifier.
 * 
 * 
 * @retval bool | string
 *  Returns false if the specified stackKey doesn't
 *  exist in _messages or the stack is empty, otherwise
 *  returns the last message from the specified stack.
 * 
 */
public function _popMessage($stackKey = MESSAGES_ERROR)
    {
        if (! isset($this->_messages[$stackKey]))
            return false;

        if (count($this->_messages[$stackKey]) < 1)
            return false;

        return array_pop($this->_messages[$stackKey]);
    }


/*!The _getAll method.
 *
 *  If the stackKy parameter is unset, then the entire
 *  _messages member is returned.
 * 
 *  Otherwise, the specified message stack is returned,
 *  if the specified stackKey doesn't exist in _messages
 *  then false is returned.
 * 
 *  NOTE - that this does not pop or clear anything from
 *  the local _messages stacks.
 * 
 * 
 * @param string stackKey
 *  The key of the _messages[] stack to return.
 * 
 * 
 * @retval bool | array
 *  If stackKey is specified and does not exist in the
 *  _messages member of the class then false is returned.
 * 
 *  Otherwise the requested _messages stack (array) is
 *  returned.
 * 
 */
public function _getAll($stackKey = false)
    {
        if ($stackKey === false)
            return $this->_messages;

        if (! isset($this->_messages[$stackKey]))
            return false;

        return $this->_messages[$stackKey];
    }

}
