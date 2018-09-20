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
namespace Papaya\Content\Link;

use Papaya\Content;
use Papaya\Database;

/**
 * This object loads link type data into a list.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Types extends Database\Records\Lazy {
  protected $_fields = [
    'id' => 'linktype_id',
    'name' => 'linktype_name',
    'is_visible' => 'linktype_is_visible',
    'class' => 'linktype_class',
    'target' => 'linktype_target',
    'is_popup' => 'linktype_popup',
    'popup_options' => 'linktype_popup_config'
  ];

  protected $_tableName = Content\Tables::PAGE_LINK_TYPES;

  protected $_identifierProperties = ['id'];

  protected $_orderByFields = [
    'name' => Database\Interfaces\Order::ASCENDING
  ];

  /**
   * Here are some default link types that does not need to be stored in the database,
   * they are added to the result before using it.
   *
   * @return \Iterator
   */
  protected function getResultIterator() {
    return new \Papaya\Iterator\Union(
      new \ArrayIterator(
        [
          [
            'id' => 1,
            'name' => 'visible',
            'is_visible' => TRUE,
            'class' => '',
            'target' => '_self',
            'is_popup' => FALSE,
            'popup_options' => []
          ],
          [
            'id' => 2,
            'name' => 'hidden',
            'is_visible' => FALSE,
            'class' => '',
            'target' => '_self',
            'is_popup' => FALSE,
            'popup_options' => []
          ]
        ]
      ),
      parent::getResultIterator()
    );
  }

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
