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

class PapayaDatabaseConditionGenerator {

  private $_mapping = NULL;
  private $_databaseAccess = NULL;

  private $_functions = array(
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
  );

  /**
   *
   * @param \PapayaDatabaseInterfaceAccess|\PapayaDatabaseAccess $parent
   * @param \PapayaDatabaseInterfaceMapping $mapping
   * @throws \InvalidArgumentException
   */
  public function __construct($parent, \PapayaDatabaseInterfaceMapping $mapping = NULL) {
    if ($parent instanceof \PapayaDatabaseInterfaceAccess) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceof \PapayaDatabaseAccess) {
      $this->_databaseAccess = $parent;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid parent class %s in %s', get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
  }

  public function fromArray($filter) {
    $group = new \PapayaDatabaseConditionGroup($this->_databaseAccess, $this->_mapping, 'AND');
    $this->appendConditions($group, $filter);
    return $group;
  }

  private function appendConditions(\PapayaDatabaseConditionGroup $group, $filter, $limit = 42) {
    foreach ($filter as $key => $value) {
      if (preg_match('((?<type>[\w-]+):(?<fields>.*))', $key, $match)) {
        $condition = strtoLower($match['type']);
        $field = FALSE !== strpos($match['fields'], ',') ? explode(',', $match['fields']) : $match['fields'];
      } else {
        $definition = explode(',', $key);
        $field = \PapayaUtilArray::get($definition, 0, '');
        $condition = strtoLower(\PapayaUtilArray::get($definition, 1, 'equal'));
      }
      if ($condition == 'and' && is_array($value)) {
        $this->appendConditions($group->logicalAnd(), $value, $limit - 1);
      } elseif ($condition == 'or' && is_array($value)) {
        $this->appendConditions($group->logicalOr(), $value, $limit - 1);
      } elseif ($condition == 'not' && is_array($value)) {
        $this->appendConditions($group->logicalNot(), $value, $limit - 1);
      } elseif (isset($this->_functions[$condition])) {
        call_user_func(array($group, $this->_functions[$condition]), $field, $value);
      }
    }
  }
}
