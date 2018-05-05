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
* Papaya database exception main class
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseException extends \PapayaException {

  /**
  * Severity information
  *
  * @var integer
  */
  const SEVERITY_INFO = 1;

  /**
  * Severity warning
  *
  * @var integer
  */
  const SEVERITY_WARNING = 2;

  /**
  * Severity error
  *
  * @var integer
  */
  const SEVERITY_ERROR = 3;

  /**
  * Severtiy of this exception
  *
  * @var integer
  */
  private $_severity = self::SEVERITY_ERROR;

  /**
  * Create expeiton an store values
  *
  * @param string $message
  * @param integer $code
  * @param integer|NULL $severity
  */
  public function __construct($message, $code = 0, $severity = NULL) {
    parent::__construct($message, $code);
    if (isset($severity)) {
      $this->_severity = $severity;
    }
  }

  /**
  * Get exception severity
  */
  public function getSeverity() {
    return $this->_severity;
  }
}
