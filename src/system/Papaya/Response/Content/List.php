<?php
/**
 * Iterator the argument and output it.
 *
 * @copyright 2016 by papaya Software GmbH - All rights reserved.
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
 * @subpackage Response
 * @version $Id: File.php 39115 2014-02-05 15:47:37Z weinert $
 */

/**
 * Iterator the argument and output it.
 *
 * @package Papaya-Library
 * @subpackage Response
 */
class PapayaResponseContentList implements PapayaResponseContent {

  /**
   * string content buffer
   * @var Traversable
   */
  private $_traversable;

  private $_lineEnd = "\n";

  /**
   * @param Traversable $traversable
   * @param string $lineEnd
   */
  public function __construct(Traversable $traversable, $lineEnd = "\n") {
    $this->_traversable = $traversable;
    $this->_lineEnd = $lineEnd;
  }

  /**
   * Return content length for the http header
   *
   * @return integer
   */
  public function length() {
    return -1;
  }

  /**
   * Output string content to standard output
   *
   * @return string
   */
  public function output() {
    foreach ($this->_traversable as $line) {
      echo $line.$this->_lineEnd;
      flush();
    }
  }
}