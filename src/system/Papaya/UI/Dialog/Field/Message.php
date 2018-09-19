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
namespace Papaya\UI\Dialog\Field;

/**
 * A field that output a message inside the dialog
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Message extends Information {
  const SEVERITY_INFO = \Papaya\Message::SEVERITY_INFO;

  const SEVERITY_WARNING = \Papaya\Message::SEVERITY_WARNING;

  const SEVERITY_ERROR = \Papaya\Message::SEVERITY_ERROR;

  /**
   * Message image
   *
   * @var string[]
   */
  private static $_images = [
    \Papaya\Message::SEVERITY_INFO => 'status-dialog-information',
    \Papaya\Message::SEVERITY_WARNING => 'status-dialog-warning',
    \Papaya\Message::SEVERITY_ERROR => 'status-dialog-error'
  ];

  /**
   * Create object and assign needed values
   *
   * @param \Papaya\UI\Text|string $severity
   * @param string|\Papaya\UI\Text $message
   *
   * @internal param string $image
   */
  public function __construct($severity, $message) {
    $severityKey = isset(self::$_images[$severity]) ? $severity : \Papaya\Message::SEVERITY_INFO;
    parent::__construct($message, self::$_images[$severityKey]);
  }
}
