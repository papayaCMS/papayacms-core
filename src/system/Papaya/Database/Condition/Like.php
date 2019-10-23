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

class Like extends Element {
  private $_value;

  /**
   * Like constructor.
   *
   * @param Group $parent
   * @param string $field
   * @param null $value
   */
  public function __construct(Group $parent, $field = '', $value = NULL) {
    $this->_value = $value;
    parent::__construct($parent, $field, $value);
  }

  /**
   * @param bool $silent
   * @return string
   */
  public function getSql($silent = FALSE) {
    $values = \is_array($this->_value) ? $this->_value : [$this->_value];
    $likeValues = [];
    $inValues = [];
    foreach ($values as $value) {
      $hasWildcards = \preg_match('([*?])', $value);
      if ($hasWildcards) {
        $likeValues[] = \str_replace(['%', '*', '?'], ['%%', '%', '_'], $value);
      } else {
        $inValues[] = (string)$value;
      }
    }
    try {
      $fields = $this->getField();
      if (!\is_array($fields)) {
        $fields = [$fields];
      }
      $conditions = [];
      foreach ($fields as $field) {
        if (\count($inValues) > 0) {
          $conditions[] = $this->getDatabaseAccess()->getSQLCondition(
            [
              $this->mapFieldName($field) => $inValues
            ],
            NULL,
            '='
          );
        }
        if (\count($likeValues) > 0) {
          $conditions[] = $this->getDatabaseAccess()->getSQLCondition(
            [
              $this->mapFieldName($field) => $likeValues
            ],
            NULL,
            'LIKE'
          );
        }
      }
      return ' ('.\implode(' OR ', $conditions).') ';
    } catch (\LogicException $e) {
      if (!$silent) {
        throw $e;
      }
      return '';
    }
  }
}
