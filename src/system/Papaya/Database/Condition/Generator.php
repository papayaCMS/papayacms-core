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

use Papaya\Database\Access;
use Papaya\Database\Accessible;
use Papaya\Database\Interfaces\Mapping;
use Papaya\Utility\Arrays as ArrayUtilities;

class Generator {
  private $_mapping;

  private $_databaseAccess;

  private $_functions = [
    'equal' => 'isEqual',
    'notequal' => 'isNotEqual',
    'greater' => 'isGreaterThan',
    'greaterorequal' => 'isGreaterThanOrEqual',
    'less' => 'isLessThan',
    'lessorequal' => 'isLessThanOrEqual',
    'contains' => 'contains',
    'like' => 'like',
    'match' => 'match',
    'match-boolean' => 'matchBoolean',
    'match-contains' => 'matchContains'
  ];

  /**
   * @param Accessible|Access $parent
   * @param Mapping $mapping
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($parent, Mapping $mapping = NULL) {
    if ($parent instanceof Accessible) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceof Access) {
      $this->_databaseAccess = $parent;
    } else {
      throw new \InvalidArgumentException(
        \sprintf('Invalid parent class %s in %s', \get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
  }

  public function fromArray($filter) {
    $group = new Group($this->_databaseAccess, $this->_mapping, 'AND');
    $this->appendConditions($group, $filter);
    return $group;
  }

  private function appendConditions(Group $group, $filter, $limit = 42) {
    foreach ($filter as $key => $value) {
      $lowercaseKey = strtolower($key);
      if (in_array($lowercaseKey, ['and', 'or', 'not'])) {
        $condition = $lowercaseKey;
      } elseif (\preg_match('((?<type>[\w-]+):(?<fields>.*))', $key, $match)) {
        $condition = \strtolower($match['type']);
        $field = FALSE !== \strpos($match['fields'], ',') ? \explode(',', $match['fields']) : $match['fields'];
      } else {
        $definition = \explode(',', $key);
        $field = ArrayUtilities::get($definition, 0, '');
        $condition = \strtolower(ArrayUtilities::get($definition, 1, 'equal'));
      }
      if ('and' === $condition && \is_array($value)) {
        $this->appendConditions($group->logicalAnd(), $value, $limit - 1);
      } elseif ('or' === $condition && \is_array($value)) {
        $this->appendConditions($group->logicalOr(), $value, $limit - 1);
      } elseif ('not' === $condition && \is_array($value)) {
        $this->appendConditions($group->logicalNot(), $value, $limit - 1);
      } elseif (isset($this->_functions[$condition])) {
        $group->{$this->_functions[$condition]}($field, $value);
      }
    }
  }
}
