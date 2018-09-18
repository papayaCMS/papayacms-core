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

/**
 * This object loads link type data into a list.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Types extends \Papaya\Database\Records\Lazy {
  protected $_fields = [
    'id' => 'linktype_id',
    'name' => 'linktype_name',
    'is_visisble' => 'linktype_is_visisble',
    'class' => 'linktype_class',
    'target' => 'linktype_target',
    'is_popup' => 'linktype_popup',
    'popup_options' => 'linktype_popup_config'
  ];

  protected $_tableName = 'linktypes';

  protected $_identifierProperties = ['id'];

  protected $_orderByFields = [
    'name' => \Papaya\Database\Interfaces\Order::ASCENDING
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
            'is_visisble' => TRUE,
            'class' => '',
            'target' => '_self',
            'is_popup' => FALSE,
            'popup_options' => []
          ],
          [
            'id' => 2,
            'name' => 'hidden',
            'is_visisble' => FALSE,
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
   * @see \Papaya\Database\Records\Unbuffered::_createMapping()
   *
   * @return \Papaya\Database\Record\Mapping
   */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = [
      $this, 'mapFieldToProperty'
    ];
    $mapping->callbacks()->onMapValueFromPropertyToField = [
      $this, 'mapPropertyToField'
    ];
    return $mapping;
  }

  /**
   * Link options need to be deserialized.
   *
   * @param string $context
   * @param string $property
   * @param string $field
   * @param mixed $value
   * @return mixed
   */
  public function mapFieldToProperty($context, $property, $field, $value) {
    if ('popup_options' == $property) {
      return \Papaya\Utility\Text\XML::unserializeArray((string)$value);
    }
    return $value;
  }

  /**
   * Link options need to be serialized.
   *
   * @param string $context
   * @param string $property
   * @param string $field
   * @param mixed $value
   * @return mixed
   */
  public function mapPropertyToField($context, $property, $field, $value) {
    if ('popup_options' == $property) {
      return \Papaya\Utility\Text\XML::serializeArray((array)$value);
    }
    return $value;
  }
}
