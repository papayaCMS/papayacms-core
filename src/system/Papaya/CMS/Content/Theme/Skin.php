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
namespace Papaya\CMS\Content\Theme;

use Papaya\CMS\Content;
use Papaya\Database;
use Papaya\Utility;
use Papaya\XML;

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
class Skin extends Database\Record {
  /**
   * Map field names to more convenient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'id' => 'themeset_id',
    'title' => 'themeset_title',
    'theme' => 'theme_name',
    'values' => 'themeset_values'
  ];

  /**
   * Table containing view informations
   *
   * @var string
   */
  protected $_tableName = Content\Tables::THEME_SKINS;

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
   *
   * @return mixed
   */
  public function mapFieldToProperty(
    /* @noinspection PhpUnusedParameterInspection */
    $context, $property, $field, $value
  ) {
    if ('values' === $property) {
      return Utility\Text\XML::unserializeArray((string)$value);
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
   *
   * @return mixed
   */
  public function mapPropertyToField(
    /* @noinspection PhpUnusedParameterInspection */
    $context, $property, $field, $value
  ) {
    if ('values' === $property) {
      return Utility\Text\XML::serializeArray((array)$value);
    }
    return $value;
  }

  /**
   * Return the values as a xml document
   *
   * @param Content\Structure $definition
   *
   * @return \Papaya\XML\Document
   */
  public function getValuesXML(Content\Structure $definition) {
    return $definition->getXMLDocument(isset($this->values) ? $this->values : []);
  }

  /**
   * Loads the values from a xml document
   *
   * @param Content\Structure $definition
   * @param XML\Element $values
   */
  public function setValuesXML(Content\Structure $definition, XML\Element $values) {
    $this['values'] = $definition->getArray($values);
  }
}
