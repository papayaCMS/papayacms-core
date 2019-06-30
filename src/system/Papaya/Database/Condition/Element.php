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

use Papaya\Database;

class Element {
  private $_parent;

  private $_field;

  private $_value;

  protected $_operator = '=';

  /**
   * Element constructor.
   *
   * @param Group $parent
   * @param string $field
   * @param null $value
   * @param null $operator
   */
  public function __construct(
    Group $parent, $field = '', $value = NULL, $operator = NULL
  ) {
    $this->_parent = $parent;
    $this->_field = $field;
    $this->_value = $value;
    if (NULL !== $operator) {
      $this->_operator = $operator;
    }
  }

  /**
   * @return \Papaya\Database\Access
   */
  public function getDatabaseAccess() {
    return $this->getParent()->getDatabaseAccess();
  }

  /**
   * @return null|Database\Interfaces\Mapping
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
   * @return string
   */
  public function getField() {
    return $this->_field;
  }

  /**
   * @param bool $silent
   * @return string
   */
  public function getSql($silent = FALSE) {
    try {
      if (\is_array($this->_field)) {
        $conditions = [];
        foreach ($this->_field as $field) {
          $conditions[] = $this->getDatabaseAccess()->getSQLCondition(
            [
              $this->mapFieldName($field) => $this->_value
            ],
            NULL,
            $this->_operator
          );
        }
        return ' ('.\implode(' AND ', $conditions).') ';
      }
      return $this->getDatabaseAccess()->getSQLCondition(
        [
          $this->mapFieldName($this->_field) => $this->_value
        ],
        NULL,
        $this->_operator
      );
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
    return $this->getSql(TRUE) ?: '';
  }

  /**
   * @param $name
   * @return false|string
   */
  protected function mapFieldName($name) {
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
