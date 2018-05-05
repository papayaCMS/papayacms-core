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

/**
 * Load/save a the theme set main record (contains name and id)
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property string $id
 * @property string $title
 * @property string $theme
 * @property array $values
 */
class PapayaContentThemeSet extends \PapayaDatabaseRecord {

  /**
   * Map field names to more convenient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'id' => 'themeset_id',
    'title' => 'themeset_title',
    'theme' => 'theme_name',
    'values' => 'themeset_values'
  );

  /**
   * Table containing view informations
   *
   * @var string
   */
  protected $_tableName = \PapayaContentTables::THEME_SETS;

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
   *
   */
  public function mapFieldToProperty(
    /** @noinspection PhpUnusedParameterInspection */
    $context, $property, $field, $value
  ) {
    if ('values' === $property) {
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
  public function mapPropertyToField(
    /** @noinspection PhpUnusedParameterInspection */
    $context, $property, $field, $value
  ) {
    if ('values' === $property) {
      return \PapayaUtilStringXml::serializeArray((array)$value);
    }
    return $value;
  }

  /**
   * Return the values as a xml document
   *
   * @param \PapayaContentStructure $definition
   * @return \PapayaXmlDocument
   */
  public function getValuesXml(\PapayaContentStructure $definition) {
    return $definition->getXmlDocument(isset($this->values) ? $this->values : array());
  }

  /**
   * Loads the values from a xml document
   *
   * @param \PapayaContentStructure $definition
   * @param \PapayaXmlElement $values
   */
  public function setValuesXml(\PapayaContentStructure $definition, \PapayaXmlElement $values) {
    $this['values'] = $definition->getArray($values);
  }
}
