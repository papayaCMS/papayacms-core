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

namespace Papaya\Iterator\Tree;

/**
 * An iterator that attaches details from a second array or Traversable to the first.
 *
 * If an identifier is provided the key of the main array is one or more specified values
 * from the detail array elements joined by '|'.
 *
 * Example:
 *
 * $identifier = array('group', 'subgroup');
 * $main = array('foo|bar' => 'Group foo/bar', 'details' => '...');
 * $detail = array(array('group' => 'foo', 'subgroup' => 'bar', 'details' => '...'));
 *
 * If no identifier is provided the key of the details array should contain a list of all children
 * for the same key in the main array.
 * Example:
 *
 * $identifier = NULL;
 * $main = array('foo' => 'Group foo');
 * $detail = array('foo' => array('element one', 'elementTwo'));
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Details
  extends \IteratorIterator
  implements \RecursiveIterator {
  /**
   * @var array|\Traversable
   */
  private $_list;

  /**
   * @var array
   */
  private $_tree;

  /**
   * @var array
   */
  private $_identifier;

  /**
   * @param array|\Traversable $main
   * @param array|\Traversable $details
   * @param string|array|null $identifier
   */
  public function __construct($main, $details, $identifier = NULL) {
    parent::__construct(new \Papaya\Iterator\TraversableIterator($main));
    $this->setDetails($details, $identifier);
  }

  /**
   * Store details and identifier definition, reset the internal tree so it gets compiled on next
   * read access
   *
   * @param array|\Traversable $details
   * @param string|array|null $identifier
   */
  public function setDetails($details, $identifier = NULL) {
    \Papaya\Utility\Constraints::assertArrayOrTraversable($details);
    $this->_list = $details;
    $this->_identifier = isset($identifier) ? \Papaya\Utility\Arrays::ensure($identifier) : NULL;
    $this->_tree = NULL;
  }

  /**
   * Get the details grouped by their identifiers.
   *
   * @return array
   */
  private function getDetails() {
    if (!isset($this->_tree)) {
      $this->_tree = [];
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
      $result = [];
      foreach (\Papaya\Utility\Arrays::ensure($this->_identifier) as $property) {
        $result[] = \Papaya\Utility\Arrays::get($element, $property, '');
      }
      return \implode('|', $result);
    } else {
      return $key;
    }
  }

  /**
   * @see \RecursiveIterator::hasChildren()
   */
  public function hasChildren() {
    $details = $this->getDetails();
    return isset($details[$this->key()]);
  }

  /**
   * @see \RecursiveIterator::getChildren()
   */
  public function getChildren() {
    $details = $this->getDetails();
    return new \Papaya\Iterator\Tree\Items($details[$this->key()]);
  }
}
