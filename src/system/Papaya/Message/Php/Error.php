<?php
/**
* Papaya Message Php Error, message object representing an php error
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
* @version $Id: Error.php 34236 2010-05-14 16:21:00Z weinert $
*/

/**
* Papaya Message Php Error, message object representing an php error
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessagePhpError
  extends PapayaMessagePhp {

  /**
  * Create object and set values from a captured error
  *
  * @param integer $severity
  * @param string $message
  * @param mixed $variableContext
  */
  public function __construct($severity, $message, $variableContext = NULL) {
    parent::__construct();
    $this->setSeverity($severity);
    $this->_message = $message;
    $this
      ->_context
      ->append(new PapayaMessageContextBacktrace(2))
      ->append(new PapayaMessageContextVariable($variableContext));
  }
}