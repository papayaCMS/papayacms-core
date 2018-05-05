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
* Simple error collector for dialogs.
*
* Holds a list of errors and allows to iterate them.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogErrors implements \IteratorAggregate, Countable {

  /**
  * Error list
  * @var array
  */
  protected $_errors = array();

  /**
  * add a new error to the list.
  *
  * @param \Exception $exception
  * @param object $source
  */
  public function add(\Exception $exception, $source = NULL) {
    $this->_errors[] = array(
      'exception' => $exception,
      'source' => $source,
    );
  }

  /**
  * clear internal error list.
  */
  public function clear() {
    $this->_errors = array();
  }

  /**
  * Countable interface, return element count.
  *
  * @return integer
  */
  public function count() {
    return count($this->_errors);
  }

  /**
  * IteratorAggregate interface, return ArrayIterator for internal array.
  *
  * @return \ArrayIterator
  */
  public function getIterator() {
    return new \ArrayIterator($this->_errors);
  }

  public function getSourceCaptions() {
    $result = array();
    foreach ($this->_errors as $error) {
      if (isset($error['source']) &&
          ($source = $error['source']) &&
          $source instanceof \PapayaUiDialogField) {
        $caption = $source->getCaption();
        if (!empty($caption)) {
          $result[] = $caption;
        }
      }
    }
    return $result;
  }

}
