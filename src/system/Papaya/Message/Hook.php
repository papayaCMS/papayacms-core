<?php
/**
* Papaya Message Hook, interface for hooks that that capture php events
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
* @version $Id: Hook.php 37961 2013-01-14 15:04:54Z weinert $
*/

/**
* Papaya Message Hook, interface for hooks that that capture php events
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessageHook {

  /**
  * Activate message hook, make it capture the php events
  */
  function activate();

  /**
  * Dectivate message hook, restoring default behavour
  */
  function deactivate();
}

