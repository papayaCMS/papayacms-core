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
* Papaya Message Dispatcher Template, handle messages to be shown to the user in browser
*
* Make sure that the dispatcher does not initialize it's resources only if needed,
* It will be created at the start of the script, unused initialzation will slow the script down.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageDispatcherTemplate
  extends \PapayaObject
  implements \PapayaMessageDispatcher {

  private $severityStrings = array(
    \PapayaMessage::SEVERITY_INFO => 'info',
    \PapayaMessage::SEVERITY_WARNING => 'warning',
    \PapayaMessage::SEVERITY_ERROR => 'error',
    \PapayaMessage::SEVERITY_DEBUG => 'debug'
  );

  /**
  * Add message to the output, for now uses the old error system.
  *
  * Only messages that implements \PapayaMessageDisplay are used, \all other message are ignored.
  *
  * @param \PapayaMessage $message
  * @return boolean
  */
  public function dispatch(\PapayaMessage $message) {
    if ($message instanceof \PapayaMessageDisplayable) {
      if (isset($GLOBALS['PAPAYA_LAYOUT'])) {
        /** @var PapayaTemplate $layout */
        $layout = $GLOBALS['PAPAYA_LAYOUT'];
        $layout->values()->append(
          '/page/messages',
          'message',
          array(
            'severity' => $this->severityStrings[$message->getType()]
          ),
          $message->getMessage()
        );
        return TRUE;
      }
    }
    return FALSE;
  }
}
