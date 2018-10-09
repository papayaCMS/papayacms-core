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
namespace Papaya\Database\Record\Mapping;

use Papaya\BaseObject;

/**
 * Callbacks that are used by the record mapping object
 *
 * More specific callbacks are called before the unspecific, like "onMapValueFromFieldToProperty()"
 * before "onMapValue".
 *
 * @package Papaya-Library
 * @subpackage Database
 *
 * @property BaseObject\Callback $onBeforeMapping
 * @property BaseObject\Callback $onBeforeMappingFieldsToProperties
 * @property BaseObject\Callback $onBeforeMappingPropertiesToFields
 * @property BaseObject\Callback $onAfterMapping
 * @property BaseObject\Callback $onAfterMappingFieldsToProperties
 * @property BaseObject\Callback $onAfterMappingPropertiesToFields
 * @property BaseObject\Callback $onMapValue
 * @property BaseObject\Callback $onMapValueFromFieldToProperty
 * @property BaseObject\Callback $onMapValueFromPropertyToField
 * @property BaseObject\Callback $onGetFieldForProperty
 * @property BaseObject\Callback $onGetPropertyForField
 *
 * @method array onBeforeMapping(int $direction, array $properties, array $record)
 * @method array onBeforeMappingFieldsToProperties(array $properties, array $record)
 * @method array onBeforeMappingPropertiesToFields(array $properties, array $record)
 * @method array onAfterMapping(int $direction, array $properties, array $record)
 * @method array onAfterMappingFieldsToProperties(array $properties, array $record)
 * @method array onAfterMappingPropertiesToFields(array $properties, array $record)
 * @method mixed onMapValue(int $direction, string $propertyName, string $fieldName, mixed $value)
 * @method mixed onMapValueFromFieldToProperty(string $propertyName, string $fieldName, mixed $value)
 * @method mixed onMapValueFromPropertyToField(string $propertyName, string $fieldName, mixed $value)
 * @method string|null onGetFieldForProperty(string $propertyName, bool $withAlias = TRUE)
 * @method string|null onGetPropertyForField(string $fieldName)
 */
class Callbacks extends BaseObject\Callbacks {
  public function __construct() {
    parent::__construct(
      [
        'onBeforeMapping' => NULL,
        'onBeforeMappingFieldsToProperties' => NULL,
        'onBeforeMappingPropertiesToFields' => NULL,
        'onAfterMapping' => NULL,
        'onAfterMappingFieldsToProperties' => NULL,
        'onAfterMappingPropertiesToFields' => NULL,
        'onMapValue' => NULL,
        'onMapValueFromFieldToProperty' => NULL,
        'onMapValueFromPropertyToField' => NULL,
        'onGetFieldForProperty' => NULL,
        'onGetPropertyForField' => NULL
      ]
    );
  }
}
