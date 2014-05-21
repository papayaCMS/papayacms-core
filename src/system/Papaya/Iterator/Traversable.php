<?php
/**
* An iterator that converts any traversable into an iterator. Not unlike IteratorIterator but
* with a lazy initialization.
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
* @version $Id: Traversable.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* An iterator that converts any traversable into an iterator. Not unlike IteratorIterator but
* with a lazy initialization.
*
* The iterator for the traversable is fetched/create on data access not directly
* in the constructor.
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorTraversable implements OuterIterator {

  private $_traversable = NULL;
  private $_iterator = NULL;

  /**
  * Store the traversable to a member variable.
  *
  * @param Traversable|array $traversable
  */
  public function __construct($traversable) {
    PapayaUtilConstraints::assertArrayOrTraversable($traversable);
    $this->_traversable = $traversable;
  }

  /**
  * Fetches/Creates an iterator for the stored traversable. This memtod will be called
  * by the methods of the iterator interface. The methods use the $useCached argument
  * to reuse an already fetched/created iterator.
  *
  * @param boolean $useCached
  * @return Iterator
  */
  public function getIteratorForTraversable($useCached = FALSE) {
    if (!$useCached || is_null($this->_iterator)) {
      if ($this->_traversable instanceof Iterator) {
        $this->_iterator = $this->_traversable;
      } elseif ($this->_traversable instanceof IteratorAggregate) {
        $this->_iterator = $this->_traversable->getIterator();
      } elseif (is_array($this->_traversable)) {
        $this->_iterator = new ArrayIterator($this->_traversable);
      } else {
        $this->_iterator = new IteratorIterator($this->_traversable);
      }
    }
    return $this->_iterator;
  }

  /**
  * Return the original stored traversable or array, like in IteratorIterator
  */
  public function getInnerIterator() {
    return $this->_traversable;
  }

  /**
  * Return current element
  *
  * @return mixed
  */
  public function current() {
    return $this->getIteratorForTraversable(TRUE)->current();
  }

  /**
  * Return current key
  *
  * @return mixed
  */
  public function key() {
    return $this->getIteratorForTraversable(TRUE)->key();
  }

  /**
  * Fetch next element
  */
  public function next() {
    $this->getIteratorForTraversable(TRUE)->next();
  }

  /**
  * Rewind iterator to first element
  */
  public function rewind() {
    $this->getIteratorForTraversable(TRUE)->rewind();
  }

  /**
  * Valid if the current element is valid.
  *
  * @return boolean
  */
  public function valid() {
    return $this->getIteratorForTraversable(TRUE)->valid();
  }
}