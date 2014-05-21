<?php
/**
* An interface which defines the method needed for user defined session handlers.
* {@see session_set_save_handler}
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Session
* @version $Id: Handler.php 34917 2010-09-29 16:04:55Z weinert $
*/

/**
* An interface which defines the method needed for user defined session handlers.
* {@see session_set_save_handler}
*
* PapayaSessionWrapper::register() allows to register an object that implements this interface
* for the session handling.
*
* @package Papaya-Library
* @subpackage Session
*/
interface PapayaSessionHandler {

  /**
  * Open function, this works like a constructor in classes and is executed when the session
  * is being opened. The open function expects two parameters, where the first is the
  * save path and the second is the session name.
  *
  * @param string $savePath
  * @param string $sessionName
  * @return boolean
  */
  static function open($savePath, $sessionName);

  /**
  * Close function, this works like a destructor in classes and is executed when the
  * session operation is done.
  *
  * @return boolean
  */
  static function close();

  /**
  * Read function must return string value always to make save handler work as expected.
  * Return empty string if there is no data to read. Return values from other handlers
  * are converted to boolean expression. TRUE for success, FALSE for failure.
  *
  * @param string $id
  * @return string
  */
  static function read($id);

  /**
  * Write function that is called when session data is to be saved. This function
  * expects two parameters: an identifier and the data associated with it.
  *
  * @param string $id
  * @param string $sessionData
  * @return boolean
  */
  static function write($id, $sessionData);

  /**
  * The destroy handler, this is executed when a session is destroyed with session_destroy()
  * and takes the session id as its only parameter.
  *
  * @param string $id
  * @return boolean
  */
  static function destroy($id);

  /**
  * The garbage collector, this is executed when the session garbage collector is executed
  * and takes the max session lifetime as its only parameter.
  *
  * @param $maxlifetime
  * @return boolean
  */
  static function gc($maxlifetime);
}