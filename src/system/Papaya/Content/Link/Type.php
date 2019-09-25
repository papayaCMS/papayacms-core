<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Content\Link {

  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Record\Lazy as LazyRecord;

  /**
   * This object loads link type data into a list.
   *
   * @package Papaya-Library
   * @subpackage Content
   */
  class Type extends LazyRecord {

    protected $_fields = [
      'id' => 'linktype_id',
      'name' => 'linktype_name',
      'is_visible' => 'linktype_is_visible',
      'class' => 'linktype_class',
      'target' => 'linktype_target',
      'is_popup' => 'linktype_is_popup',
      'popup_options' => 'linktype_popup_config'
    ];

    protected $_tableName = ContentTables::PAGE_LINK_TYPES;

    protected $_identifierProperties = ['id'];

    /**
     * @return \Papaya\Database\Record\Mapping
     */
    protected function _createMapping() {
      $mapping = parent::_createMapping();
      $mapping->callbacks()->onMapValueFromFieldToProperty = function(
        /** @noinspection PhpUnusedParameterInspection */
        $context, $property, $field, $value
      ) {
        if ('popup_options' === $property) {
          return \Papaya\Utility\Text\XML::unserializeArray((string)$value);
        }
        return $value;
      };
      $mapping->callbacks()->onMapValueFromPropertyToField = function(
        /** @noinspection PhpUnusedParameterInspection */
        $context, $property, $field, $value
      ) {
        if ('popup_options' === $property) {
          return \Papaya\Utility\Text\XML::serializeArray((array)$value);
        }
        return $value;
      };
      return $mapping;
    }
  }
}
