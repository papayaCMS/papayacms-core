<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* Interface for protocol messages
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessageLogable extends \PapayaMessage {

  /**
  * log group for user messages (login/logout)
  */
  const GROUP_USER = 1;

  /**
  * log group for content messages (published, deleted, ...)
  */
  const GROUP_CONTENT = 2;

  /**
  * log group for database messages (warnings, errors)
  */
  const GROUP_DATABASE = 3;

  /**
  * log group for cronjob messages
  */
  const GROUP_CRONJOBS = 5;

  /**
  * log group for surfer/community messages
  */
  const GROUP_COMMUNITY = 6;

  /**
  * log group for system messages
  */
  const GROUP_SYSTEM = 7;

  /**
  * log group for module specific messages
  */
  const GROUP_MODULES = 8;

  /**
  * log group for module specific messages
  */
  const GROUP_PHP = 9;

  /**
  * log group for module specific messages
  */
  const GROUP_DEBUG = 10;

  /**
  * Get log group of message
  * @return integer
  */
  function getGroup();

  /**
  * Access to an context group element that allows to append addition details to the message
  *
  * @return \PapayaMessageContextGroup
  */
  function context();
}
