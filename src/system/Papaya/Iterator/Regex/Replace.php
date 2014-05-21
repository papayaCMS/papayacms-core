<?php
/**
* This iterator allows convert the values on request using a preg_replace().
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage Iterator
* @version $Id: Replace.php 38786 2013-09-18 08:22:34Z weinert $
*/

/**
* This iterator allows convert the values on request using a preg_replace().
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorRegexReplace extends PapayaIteratorCallback {

  /**
   * @var string
   */
  private $_pattern = '';
  /**
   * @var string
   */
  private $_replacement = '';
  /**
   * @var integer
   */
  private $_limit = -1;

  /**
   * Create object and store properties
   *
   * @param Traversable $iterator
   * @param string $pattern
   * @param string $replacement
   * @param integer $limit
   */
  public function __construct(Traversable $iterator, $pattern, $replacement, $limit = -1) {
    $this->_pattern = $pattern;
    $this->_replacement = $replacement;
    $this->_limit = $limit;
    parent::__construct($iterator, array($this, 'replace'));
  }

  /**
   * Callback method to apply the pattern to the current value before returning it
   *
   * @param string $current
   * @return string
   */
  public function replace($current) {
    return preg_replace($this->_pattern, $this->_replacement, $current, $this->_limit);
  }
}
