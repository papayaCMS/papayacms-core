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

namespace Papaya\Content;

/**
 * This object loads the defined domains for a papaya installation.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Domains extends \Papaya\Database\Records {
  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'id' => 'domain_id',
    'host' => 'domain_hostname',
    'scheme' => 'domain_protocol',
    'language_id' => 'domain_language_id',
    'group_id' => 'domaingroup_id',
    'mode' => 'domain_mode',
    'data' => 'domain_data',
    'options' => 'domain_options'
  ];

  /**
   * Table containing domain information
   *
   * @var string
   */
  protected $_tableName = \Papaya\Content\Tables::DOMAINS;

  protected $_identifierProperties = ['id'];

  /**
   * Attach callbacks for serialized field values
   *
   * @see \Papaya\Database\Record::_createMapping()
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = [
      $this, 'callbackMapValueFromFieldToProperty'
    ];
    return $mapping;
  }

  /**
   * Deserialize path and permissions field values
   *
   * @param object $context
   * @param string $property
   * @param string $field
   * @param string $value
   * @return mixed
   */
  public function callbackMapValueFromFieldToProperty($context, $property, $field, $value) {
    switch ($property) {
      case 'options' :
        return \Papaya\Utility\Text\XML::unserializeArray($value);
    }
    return $value;
  }
}
