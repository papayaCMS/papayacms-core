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

namespace Papaya\Message\Display;
/**
 * A language specific message displayed to the user.
 *
 * The given message is translated to the UI language before displayed to the user.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Translated extends \Papaya\Message\Display {

  /**
   * Initialize object, convert message into translation object
   *
   * @param integer $type
   * @param string $message
   * @param array $parameters message parameters
   */
  public function __construct($type, $message, array $parameters = array()) {
    parent::__construct($type, new \PapayaUiStringTranslated($message, $parameters));
  }
}
