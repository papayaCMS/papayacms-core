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
 *
 * @link http://www.papaya-cms.com/
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
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
    'match' => 'match',
    'match-boolean' => 'matchBoolean',
    'match-contains' => 'matchContains'
  ];

  /**
   * @param \Papaya\Database\Interfaces\Access|\Papaya\Database\Access $parent
   * @param \Papaya\Database\Interfaces\Mapping $mapping
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($parent, \Papaya\Database\Interfaces\Mapping $mapping = NULL) {
    if ($parent instanceof \Papaya\Database\Interfaces\Access) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceof \Papaya\Database\Access) {
      $this->_databaseAccess = $parent;
    } else {
      throw new \InvalidArgumentException(
        \sprintf('Invalid parent class %s in %s', \get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
  }

  public function fromArray($filter) {
    $group = new \Papaya\Database\Condition\Group($this->_databaseAccess, $this->_mapping, 'AND');
    $this->appendConditions($group, $filter);
    return $group;
  }

  private function appendConditions(\Papaya\Database\Condition\Group $group, $filter, $limit = 42) {
    foreach ($filter as $key => $value) {
      if (\preg_match('((?<type>[\w-]+):(?<fields>.*))', $key, $match)) {
        $condition = \strtolower($match['type']);
        $field = FALSE !== \strpos($match['fields'], ',') ? \explode(',', $match['fields']) : $match['fields'];
      } else {
        $definition = \explode(',', $key);
        $field = \Papaya\Utility\Arrays::get($definition, 0, '');
        $condition = \strtolower(\Papaya\Utility\Arrays::get($definition, 1, 'equal'));
      }
      if ('and' == $condition && \is_array($value)) {
        $this->appendConditions($group->logicalAnd(), $value, $limit - 1);
      } elseif ('or' == $condition && \is_array($value)) {
        $this->appendConditions($group->logicalOr(), $value, $limit - 1);
      } elseif ('not' == $condition && \is_array($value)) {
        $this->appendConditions($group->logicalNot(), $value, $limit - 1);
      } elseif (isset($this->_functions[$condition])) {
        \call_user_func([$group, $this->_functions[$condition]], $field, $value);
      }
    }
  }
}
