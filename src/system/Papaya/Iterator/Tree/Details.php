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
* An iterator that attaches details from a second array or Traversable to the first.
*
* If an identifer is provided the key of the main array is one or more specified values
* from the detail array elements joined by '|'.
*
* Example:
*
* $identifer = array('group', 'subgroup');
* $main = array('foo|bar' => 'Group foo/bar', 'details' => '...');
* $detail = array(array('group' => 'foo', 'subgroup' => 'bar', 'details' => '...'));
*
* If no identifer is provided the key of the details array should contain a list of all childrend
* for the same key in the main array.
* Example:
*
* $identifer = NULL;
* $main = array('foo' => 'Group foo');
* $detail = array('foo' => array('element one', 'elementTwo'));
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorTreeDetails
  extends IteratorIterator
  implements RecursiveIterator {

  /**
   * @var array|Traversable
   */
  private $_list = NULL;

  /**
   * @var array
   */
  private $_tree = NULL;
  /**
   *
   * @var array
   */
  private $_identifier = NULL;

  /**
   * @param array|Traversable $main
   * @param array|Traversable $details
   * @param string|array|NULL $identifier
   */
  public function __construct($main, $details, $identifier = NULL) {
    parent::__construct(new \PapayaIteratorTraversable($main));
    $this->setDetails($details, $identifier);
  }

  /**
   * Store details and identifer definition, reset the internal tree so it gets compiled on next
   * read access
   *
   * @param array|Traversable $details
   * @param string|array|NULL $identifier
   */
  public function setDetails($details, $identifier = NULL) {
    PapayaUtilConstraints::assertArrayOrTraversable($details);
    $this->_list = $details;
    $this->_identifier = isset($identifier) ? PapayaUtilArray::ensure($identifier) : NULL;
    $this->_tree = NULL;
  }

  /**
   * Get the details grouped by their identifiers.
   *
   * @return array
   */
  private function getDetails() {
    if (!isset($this->_tree)) {
      $this->_tree = array();
      foreach ($this->_list as $id => $element) {
        $identifier = $this->getIdentifier($element, $id);
        $this->_tree[$identifier][$id] = $element;
      }
    }
    return $this->_tree;
  }

  /**
   * For each element in in the identifer definition, try to fetch a value from the
   * element array. Join all values using the "|" character.
   *
   * @param array $element
   * @param mixed $key
   * @return string
   */
  protected function getIdentifier($element, $key) {
    if (isset($this->_identifier)) {
      $result = array();
      foreach (PapayaUtilArray::ensure($this->_identifier) as $property) {
        $result[] = PapayaUtilArray::get($element, $property, '');
      }
      return implode('|', $result);
    } else {
      return $key;
    }
  }

  /**
   * @see RecursiveIterator::hasChildren()
   */
  public function hasChildren() {
    $details = $this->getDetails();
    return isset($details[$this->key()]);
  }

  /**
   * @see RecursiveIterator::getChildren()
   */
  public function getChildren() {
    $details = $this->getDetails();
    return new \PapayaIteratorTreeItems($details[$this->key()]);
  }
}
