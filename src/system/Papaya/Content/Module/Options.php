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
namespace Papaya\Content\Module;

/**
 * This object loads module options different conditions.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Options extends \Papaya\Database\Records {
  protected $_fields = [
    'guid' => 'module_guid',
    'name' => 'moduleoption_name',
    'value' => 'moduleoption_value',
    'type' => 'moduleoption_type'
  ];

  protected $_tableName = \Papaya\Content\Tables::MODULE_OPTIONS;

  /**
   * Record is identified by module guid and option name
   *
   * @var array
   */
  protected $_identifierProperties = [
    'guid', 'name'
  ];

  /**
   * Add a callback to the mapping to be used after mapping
   *
   * @return \Papaya\Database\Interfaces\Mapping
   */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onAfterMapping = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $mode, $values, $record
    ) {
      $mapValue = isset($values['type'], $values['value']);
      if (\Papaya\Database\Record\Mapping::PROPERTY_TO_FIELD === $mode) {
        $result = $record;
        if ($mapValue) {
          if ('array' === $values['type']) {
            $result['moduleoption_value'] = \Papaya\Utility\Text\XML::serializeArray($values['value']);
          } else {
            $result['moduleoption_value'] = (string)$values['value'];
          }
        }
      } else {
        $result = $values;
        if ($mapValue) {
          if ('array' === $values['type']) {
            if (empty($values['value'])) {
              $result['value'] = [];
            } elseif (0 === \strpos($values['value'], '<')) {
              $result['value'] = \Papaya\Utility\Text\XML::unserializeArray($values['value']);
            } else {
              $result['value'] = @\unserialize($values['value']);
            }
          } else {
            $result['value'] = (string)$values['value'];
          }
        }
      }
      return $result;
    };
    return $mapping;
  }
}
