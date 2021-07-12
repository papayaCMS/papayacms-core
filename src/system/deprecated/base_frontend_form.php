<?php
/**
* Frontend form service class.
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Frontend
*/

/**
* This class extends the existing base_dialog class to enable frontend captioning.
* Automatic backendtranslation is turned off as well as the richtexteditor.
* A new method called applyCaptions which recieves key-value-pairs defining
* fieldNames to their resolution can be called to translate all field's captions
* before generating form-XML.
*
* @package Papaya
* @subpackage Frontend
*/
class base_frontend_form extends base_dialog {

  /**
  * Translation of captions active/inactive
  * To prevent captions from being translated automatically,
  * set this variable to FALSE. Because most callers rely on
  * automatic translation, it is turned on by default.
  * @var boolean
  */
  var $translate = FALSE;

  /**
  * Sets captions for each identified Field in the form.
  *
  * This method gets an associated array with key-value pairs defining
  * captions for identified fields within this form. Their captions will
  * be replaced with those defined as argument. Because of this, it is
  * possible to define language-dependent field captions within the backend
  * and apply them while creating forms to be used in the frontend.
  *
  * @param array $captions ($fieldName => $fieldCaption, ...)
  */
  function applyCaptions($captions) {
    if (is_array($captions)) {
      foreach ($captions as $fieldId => $caption) {
        if (isset($this->fields[$fieldId]) && is_array($this->fields[$fieldId])) {
          $this->fields[$fieldId][0] = $caption;
        }
      }
    }
  }

  /**
  * overloaded add button method - frontend texts use content for titles - so no
  * direct translation possible.
  *
  * @param string $buttonName name of the parameter
  * @param string $buttonTitle value/caption of the button
  * @param string $buttonType button type
  * @param string $buttonAlign button alignment (left, right, center)
  * @access public
  * @return void
  */
  function addButton($buttonName, $buttonTitle, $buttonType = 'button', $buttonAlign = 'left') {
    parent::addButton($buttonName, $buttonTitle, TRUE, $buttonType, $buttonAlign);
  }
}
