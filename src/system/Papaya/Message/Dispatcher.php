<?php
/**
* Papaya Message Dispatcher, interface for message dispatchers
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
* @subpackage Messages
* @version $Id: Dispatcher.php 34065 2010-04-22 12:09:21Z weinert $
*/

/**
* Papaya Message Dispatcher, interface for message dispatchers
*
* Make sure that the dispatcher does not initialize it's resources only if needed,
* It will be created at the start of the script, unused initialzation will slow the script down.
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessageDispatcher {

  /**
  * Dispatch/handle a message
  *
  * @param PapayaMessage $message
  * @return boolean message dispatched
  */
  function dispatch(PapayaMessage $message);
}