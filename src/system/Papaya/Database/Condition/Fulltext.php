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
namespace Papaya\Database\Condition;

use Papaya\Database\Access as DatabaseAccess;
use Papaya\Database\Interfaces\Mapping as DatabaseMapping;
use Papaya\Parser\Search\Text as SearchTextParser;

abstract class Fulltext {
  private $_parent;

  protected $_fields = '';

  protected $_searchFor = '';

  /**
   * Fulltext constructor.
   *
   * @param Group $parent
   * @param string|string[] $fields
   * @param string $searchFor
   */
  public function __construct(
    Group $parent, $fields, $searchFor
  ) {
    $this->_parent = $parent;
    $this->_fields = \is_array($fields) ? $fields : [$fields];
    $this->_searchFor = $searchFor;
  }

  /**
   * @param SearchTextParser $tokens
   * @param array|\Traversable $fields
   *
   * @return string
   */
  abstract protected function getFullTextCondition(SearchTextParser $tokens, array $fields);

  /**
   * @return DatabaseAccess
   */
  public function getDatabaseAccess() {
    return $this->getParent()->getDatabaseAccess();
  }

  /**
   * @return null|DatabaseMapping
   */
  public function getMapping() {
    return ($parent = $this->getParent()) ? $parent->getMapping() : NULL;
  }

  /**
   * @return Group
   */
  public function getParent() {
    return $this->_parent;
  }

  /**
   * @param bool $silent
   * @return string
   */
  public function getSql($silent = FALSE) {
    try {
      $tokens = new SearchTextParser($this->_searchFor);
      return $this->getFullTextCondition($tokens, \array_map([$this, 'mapFieldName'], $this->_fields));
    } catch (\LogicException $e) {
      if (!$silent) {
        throw $e;
      }
      return '';
    }
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->getSql(TRUE);
  }

  /**
   * @param string $name
   * @return false|string
   */
  private function mapFieldName($name) {
    if (empty($name)) {
      throw new \LogicException(
        'Can not generate condition, provided name was empty.'
      );
    }
    if ($mapping = $this->getMapping()) {
      $field = $mapping->getField($name);
    } else {
      $field = $name;
    }
    if (empty($field)) {
      throw new \LogicException(
        \sprintf(
          'Can not generate condition, given name "%s" could not be mapped to a field.',
          $name
        )
      );
    }
    return $field;
  }
}
