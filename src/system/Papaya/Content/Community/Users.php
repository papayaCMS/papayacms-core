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

namespace Papaya\Content\Community;

/**
 * Provide data encapsulation for the  surfer user records.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Users extends \Papaya\Database\Records {
  protected $_fields = [
    'id' => 'surfer_id',
    'group_id' => 'surfergroup_id',
    'handle' => 'surfer_handle',
    'caption' => '',
    'givenname' => 'surfer_givenname',
    'surname' => 'surfer_surname',
    'email' => 'surfer_email',
    'status' => 'surfer_status'
  ];

  protected $_orderByFields = [
    'surfer_surname' => \Papaya\Database\Interfaces\Order::ASCENDING,
    'surfer_givenname' => \Papaya\Database\Interfaces\Order::ASCENDING,
    'surfer_email' => \Papaya\Database\Interfaces\Order::ASCENDING
  ];

  protected $_tableName = \Papaya\Content\Tables::COMMUNITY_USER;

  /**
   * If a filter element is provided this is used to search fulltext on all surfers.
   *
   * @param array $filter
   * @param string $prefix
   * @return string
   */
  public function _compileCondition($filter, $prefix = ' WHERE ') {
    if (!isset($filter['filter'])) {
      return parent::_compileCondition($filter, $prefix);
    } else {
      $search = "'%".$this->getDatabaseAccess()->escapeString($filter['filter'])."%'";
      $condition = \sprintf(
        '(surfer_givenname LIKE %1$s OR surfer_surname LIKE %1$s OR surfer_email LIKE %1$s)',
        $search
      );
      return $prefix.$condition.parent::_compileCondition($filter, ' AND ');
    }
  }

  /**
   * attach the callback to the mapping object, so we can modify the properties
   *
   * @return \Papaya\Database\Record\Mapping
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onAfterMappingFieldsToProperties = [
      $this, 'callbackAfterMappingFieldsToProperties'
    ];
    return $mapping;
  }

  /**
   * adds a caption tho the record properties containing the name or email.
   *
   * @param object $context
   * @param array $values
   * @return array
   */
  public function callbackAfterMappingFieldsToProperties($context, $values) {
    $caption = '';
    if (!empty($values['surname'])) {
      $caption = $values['surname'].', '.
        (!empty($values['givenname']) ? $values['givenname'] : '?');
    } elseif (!empty($values['email'])) {
      $caption = $values['email'];
    }
    $values['caption'] = $caption;
    return $values;
  }
}
