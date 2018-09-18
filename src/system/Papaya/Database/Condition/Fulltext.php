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
abstract class Fulltext {
  private $_parent;

  protected $_fields = '';

  protected $_searchFor = '';

  public function __construct(
    Group $parent, $fields = '', $searchFor
  ) {
    $this->_parent = $parent;
    $this->_fields = \is_array($fields) ? $fields : [$fields];
    $this->_searchFor = $searchFor;
  }

  /**
   * @param \Papaya\Parser\Search\Text $tokens
   * @param array|\Traversable $fields
   * @return mixed
   */
  abstract protected function getFullTextCondition(\Papaya\Parser\Search\Text $tokens, array $fields);

  public function getDatabaseAccess() {
    return $this->getParent()->getDatabaseAccess();
  }

  public function getMapping() {
    return ($parent = $this->getParent()) ? $this->getParent()->getMapping() : NULL;
  }

  public function getParent() {
    return $this->_parent;
  }

  public function getSql($silent = FALSE) {
    try {
      $tokens = new \Papaya\Parser\Search\Text($this->_searchFor);
      return $this->getFullTextCondition($tokens, \array_map([$this, 'mapFieldName'], $this->_fields));
    } catch (\LogicException $e) {
      if (!$silent) {
        throw $e;
      }
      return '';
    }
  }

  public function __toString() {
    $result = $this->getSql(TRUE);
    return $result ? $result : '';
  }

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
