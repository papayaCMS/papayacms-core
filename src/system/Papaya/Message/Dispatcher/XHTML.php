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

/**
 * Papaya Message Dispatcher XHTML, send out log messages as xhtml (just output to the browser)
 *
 * This will output invalid XML because it closes some tags that could block the output.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class XHTML
  extends \Papaya\Application\BaseObject
  implements \Papaya\Message\Dispatcher {
  /**
   * Options for header formatting (background color, text color, label)
   *
   * @var array
   */
  private static $_MESSAGE_OPTIONS = [
    \Papaya\Message::SEVERITY_DEBUG => [
      '#F0F0F0', '#000', 'Debug'
    ],
    \Papaya\Message::SEVERITY_INFO => [
      '#F0F0F0', '#000060', 'Information'
    ],
    \Papaya\Message::SEVERITY_NOTICE => [
      '#F0F0F0', '#000060', 'Notice'
    ],
    \Papaya\Message::SEVERITY_WARNING => [
      '#FFCC33', '#000000', 'Warning'
    ],
    \Papaya\Message::SEVERITY_ERROR => [
      '#CC0000', '#FFFFFF', 'Error'
    ],
    \Papaya\Message::SEVERITY_CRITICAL => [
      '#CC0000', '#FFFFFF', 'Critical'
    ],
    \Papaya\Message::SEVERITY_ALERT => [
      '#CC0000', '#FFFFFF', 'Alert'
    ],
    \Papaya\Message::SEVERITY_EMERGENCY => [
      '#CC0000', '#FFFFFF', 'Emergency'
    ],
  ];

  /**
   * Output log message to browser using xhtml output
   *
   * @param \Papaya\Message $message
   * @return bool
   */
  public function dispatch(\Papaya\Message $message) {
    if ($message instanceof \Papaya\Message\Logable &&
      $this->allow()) {
      $this->outputClosers();
      print('<div class="debug" style="border: none; margin: 3em; padding: 0; font-size: 1em;">');
      $headerOptions = $this->getHeaderOptionsFromType($message->getSeverity());
      \printf(
        '<h3 style="background-color: %s; color: %s; padding: 0.3em; margin: 0;">%s: %s</h3>',
        \Papaya\Utility\Text\XML::escapeAttribute($headerOptions[0]),
        \Papaya\Utility\Text\XML::escapeAttribute($headerOptions[1]),
        \Papaya\Utility\Text\XML::escape($headerOptions[2]),
        \Papaya\Utility\Text\XML::escape($message->getMessage())
      );
      print($message->context()->asXhtml());
      print('</div>');
    }
    return FALSE;
  }

  /**
   * Check if it is allowed to use the dispatcher
   */
  public function allow() {
    $options = $this->papaya()->options;
    return $options->get('PAPAYA_PROTOCOL_XHTML', $options->get('PAPAYA_DBG_DEVMODE'));
  }

  /**
   * Outputs additional clsoing tags before the message, to make sure that the debug message
   * is visible.
   */
  public function outputClosers() {
    $doOutput = $this
      ->papaya()
      ->options
      ->get('PAPAYA_PROTOCOL_XHTML_OUTPUT_CLOSERS', FALSE);
    if ($doOutput) {
      print('</form></table>');
    }
  }

  /**
   * Get header formating options and a label for the error message
   *
   * @param int $type
   * @return array
   */
  public function getHeaderOptionsFromType($type) {
    if (isset(self::$_MESSAGE_OPTIONS[$type])) {
      return self::$_MESSAGE_OPTIONS[$type];
    }
    return self::$_MESSAGE_OPTIONS[\Papaya\Message::SEVERITY_ERROR];
  }
}
