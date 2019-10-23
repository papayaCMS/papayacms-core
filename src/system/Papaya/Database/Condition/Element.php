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

class Element extends Condition {

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
    parent::__construct($parent);
    $this->_field = $field;
    $this->_value = $value;
    if (NULL !== $operator) {
      $this->_operator = $operator;
    }
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
}
