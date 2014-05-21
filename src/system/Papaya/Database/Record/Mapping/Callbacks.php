<?php
/**
* Callbacks that are used by the record mapping object
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Callbacks.php 39404 2014-02-27 14:55:43Z weinert $
*/

/**
* Callbacks that are used by the record mapping object
*
* More specific callbacks are called before the unspecific, like "onMapValueFromFieldToProperty()"
* before "onMapValue".
*
* @package Papaya-Library
* @subpackage Database
*
* @property PapayaObjectCallback $onBeforeMapping
* @property PapayaObjectCallback $onBeforeMappingFieldsToProperties
* @property PapayaObjectCallback $onBeforeMappingPropertiesToFields
* @property PapayaObjectCallback $onAfterMapping
* @property PapayaObjectCallback $onAfterMappingFieldsToProperties
* @property PapayaObjectCallback $onAfterMappingPropertiesToFields
* @property PapayaObjectCallback $onMapValue
* @property PapayaObjectCallback $onMapValueFromFieldToProperty
* @property PapayaObjectCallback $onMapValueFromPropertyToField
* @property PapayaObjectCallback $onGetFieldForProperty
* @property PapayaObjectCallback $onGetPropertyForField
* @method array onBeforeMapping
* @method array onBeforeMappingFieldsToProperties
* @method array onBeforeMappingPropertiesToFields
* @method array onAfterMapping
* @method array onAfterMappingFieldsToProperties
* @method array onAfterMappingPropertiesToFields
* @method mixed onMapValue
* @method mixed onMapValueFromFieldToProperty
* @method mixed onMapValueFromPropertyToField
* @method string|NULL onGetFieldForProperty
* @method string|NULL onGetPropertyForField
*/
class PapayaDatabaseRecordMappingCallbacks extends PapayaObjectCallbacks {

  public function __construct() {
    parent::__construct(
      array(
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
      )
    );
  }
}