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
* This object loads module options different conditions.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentModuleOptions extends PapayaDatabaseRecords {

  protected $_fields = array(
    'guid' => 'module_guid',
    'name' => 'moduleoption_name',
    'value' => 'moduleoption_value',
    'type' => 'moduleoption_type'
  );

  protected $_tableName = \PapayaContentTables::MODULE_OPTIONS;

  /**
  * Record is identified by module guid and option name
  *
  * @var array
  */
  protected $_identifierProperties = array(
    'guid', 'name'
  );


  /**
  * Add a callback to the mapping to be used after mapping
  *
  * @return \PapayaDatabaseInterfaceMapping
  */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onAfterMapping = array(
      $this, 'callbackConvertValueByType'
    );
    return $mapping;
  }

  /**
  * The callback read the type field, and converts the value field depending on it.
  *
  * @param object $context
  * @param integer $mode
  * @param array $values
  * @param array $record
  * @return array
  */
  public function callbackConvertValueByType($context, $mode, $values, $record) {
    $mapValue = (isset($values['type']) && isset($values['value']));
    if ($mode == \PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD) {
      $result = $record;
      if ($mapValue) {
        switch ($values['type']) {
        case 'array' :
          $result['moduleoption_value'] = \PapayaUtilStringXml::serializeArray($values['value']);
          break;
        default :
          $result['moduleoption_value'] = (string)$values['value'];
        }
      }
    } else {
      $result = $values;
      if ($mapValue) {
        switch ($values['type']) {
        case 'array' :
          if (empty($values['value'])) {
            $result['value'] = array();
          } elseif (substr($values['value'], 0, 1) == '<') {
            $result['value'] = \PapayaUtilStringXml::unserializeArray($values['value']);
          } else {
            $result['value'] = @unserialize($values['value']);
          }
          break;
        default :
          $result['value'] = (string)$values['value'];
        }
      }
    }
    return $result;
  }
}
