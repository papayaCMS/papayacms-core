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
namespace Papaya\Message\Dispatcher;

use Papaya\Application;
use Papaya\Message;

/**
 * Papaya Message Dispatcher Template, handle messages to be shown to the user in browser
 *
 * Make sure that the dispatcher does initialize it's resources only if needed,
 * It will be created at the start of the script, unused initialization will slow the system down.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Template
  implements Application\Access, Message\Dispatcher {
  use Application\Access\Aggregation;

  private static $_SEVERITY_STRINGS = [
    Message::SEVERITY_DEBUG => 'debug',
    Message::SEVERITY_INFO => 'info',
    Message::SEVERITY_NOTICE => 'notice',
    Message::SEVERITY_WARNING => 'warning',
    Message::SEVERITY_ERROR => 'error',
    Message::SEVERITY_CRITICAL => 'critical',
    Message::SEVERITY_ALERT => 'alert',
    Message::SEVERITY_EMERGENCY => 'emergency'
  ];

  /**
   * Add message to the output, for now uses the old error system.
   *
   * Only messages that implements \Papaya\Message\Display are used, \all other message are ignored.
   *
   * @param Message $message
   *
   * @return bool
   */
  public function dispatch(Message $message) {
    if (
      $message instanceof Message\Displayable &&
      ($messages = $this->papaya()->messages) &&
      ($template = $messages->getTemplate())
    ) {
      $template->values()->append(
        '/page/messages',
        'message',
        [
          'severity' => self::$_SEVERITY_STRINGS[$message->getSeverity()]
        ],
        $message->getMessage()
      );
      return TRUE;
    }
    return FALSE;
  }
}
