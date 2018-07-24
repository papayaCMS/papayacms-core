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
class Types extends \PapayaDatabaseRecordsLazy {

  protected $_fields = array(
    'id' => 'linktype_id',
    'name' => 'linktype_name',
    'is_visisble' => 'linktype_is_visisble',
    'class' => 'linktype_class',
    'target' => 'linktype_target',
    'is_popup' => 'linktype_popup',
    'popup_options' => 'linktype_popup_config'
  );

  protected $_tableName = 'linktypes';

  protected $_identifierProperties = array('id');

  protected $_orderByFields = array(
    'name' => \PapayaDatabaseInterfaceOrder::ASCENDING
  );

  /**
   * Here are some default link types that does not need to be stored in the database,
   * they are added to the result before using it.
   *
   * @return \Iterator
   */
  protected function getResultIterator() {
    return new \PapayaIteratorMultiple(
      new \ArrayIterator(
        array(
          array(
            'id' => 1,
            'name' => 'visible',
            'is_visisble' => TRUE,
            'class' => '',
            'target' => '_self',
            'is_popup' => FALSE,
            'popup_options' => array()
          ),
          array(
            'id' => 2,
            'name' => 'hidden',
            'is_visisble' => FALSE,
            'class' => '',
            'target' => '_self',
            'is_popup' => FALSE,
            'popup_options' => array()
          )
        )
      ),
      parent::getResultIterator()
    );
  }

  /**
   * @see \PapayaDatabaseRecordsUnbuffered::_createMapping()
   *
   * @return \PapayaDatabaseRecordMapping
   */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = array(
      $this, 'mapFieldToProperty'
    );
    $mapping->callbacks()->onMapValueFromPropertyToField = array(
      $this, 'mapPropertyToField'
    );
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
    if ($property == 'popup_options') {
      return \PapayaUtilStringXml::unserializeArray((string)$value);
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
    if ($property == 'popup_options') {
      return \PapayaUtilStringXml::serializeArray((array)$value);
    }
    return $value;
  }
}
