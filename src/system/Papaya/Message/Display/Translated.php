<?php
/**
* A language specific message displayed to the user.
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
* @version $Id: Translated.php 35569 2011-03-28 15:34:26Z weinert $
*/

/**
* A language specific message displayed to the user.
*
* The given message is translated to the UI language before displayed to the user.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageDisplayTranslated extends PapayaMessageDisplay {

  /**
  * Initialize object, convert message into translation object
  *
  * @param integer $type
  * @param string $message
  * @param array $parameters message parameters
  */
  public function __construct($type, $message, array $parameters = array()) {
    parent::__construct($type, new PapayaUiStringTranslated($message, $parameters));
  }
}