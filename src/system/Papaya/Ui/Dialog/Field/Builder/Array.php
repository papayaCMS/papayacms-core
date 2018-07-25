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
* Created dialog fields from an $editFields array. This object is used to allow an easier migration.
*
* The $editFields where used before to create dialogs from array declarations. The array looks like
* this:
*
* array(
*   subtitle,
*   fieldIdentifier => array(
*     caption, valdation, mandatory, fieldType, parameters, hint, defaultValue, alignment
*   )
* );
*
* An string element without a key will be interpreted as an field group.
*
* The alignment property will get ignored. The new field types does not have one.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldBuilderArray {

  /**
   * @var object
   */
  private $_owner = NULL;

  /**
  * Editfields definition, stored for later getFields() call
  * @var array
  */
  private $_editFields = array();

  /**
  * Translate captions and hints?
  * @var boolean
  */
  private $_translatePhrases = array();

  /**
   * @var \PapayaUiDialogFieldFactory
   */
  private $_fieldFactory = NULL;

  /**
   * Map old field type strings to strings that can be expanded to profile class names
   */
  private $_fieldMapping = array(
    'info' => 'message',
    'pageid' => 'input_page',
    'input_counter' => 'input_counted',
    'date' => 'input_date',
    'datetime' => 'input_date_time',
    'geopos' => 'input_geo_position',
    'combo' => 'select',
    'translatedcombo' => 'select_translated',
    'radio' => 'select_radio',
    'yesno' => 'select_boolean',
    'mediafolder' => 'select_media_folder',
    'checkgroup' => 'select_checkboxes',
    'filecombo' => 'select_file',
    'dircombo' => 'select_directory',
    'simplerichtext' => 'richtext_simple',
    'individualrichtext' => 'richtext_individual',
    'mediafile' => 'input_media_file',
    'mediaimage' => 'input_media_image',
    'imagefixed' => 'input_media_image',
    'image' => 'input_media_image_resized'
  );

  /**
   * Map old validation function strings to strings that can be expanded to profile class names
   */
  private $_filterMapping = array(
    'isHtmlColor' => 'isCssColor',
    'isNumUnit' => 'isCssSize',
    'isIPv4Address' => 'isIpAddressV4',
    'isIPv6Address' => 'isIpAddressV6',
    'isNum' => 'isInteger',
    'isFile' => 'isFileName',
    'isPath' => 'isFilePath',
    'isSomeText' => 'isNotEmpty',
    'isHttpX' => 'isUrl',
    'isHttpHost' => 'isUrlHost',
    'isHttp' => 'isUrlHttp',
    'isNoHtml' => 'isNotXml',
    'isGeoPos' => 'isGeoPosition',
    'isHTTPHostOrIPAddress' => 'isServerAddress',
    'isAlpha' => 'isText',
    'isAlphaChar' => 'isText',
    'isAlphaNum' => 'isTextWithNumbers',
    'isAlphaNumChar' => 'isTextWithNumbers'
  );

  /**
  * Create builder object, store field definition and translation mode
  *
  * @param object $owner Owner for callback functions
  * @param array $editFields
  * @param boolean $translatePhrases
  */
  public function __construct($owner, array $editFields, $translatePhrases = FALSE) {
    $this->_owner = $owner;
    $this->_editFields = $editFields;
    \PapayaUtilConstraints::assertBoolean($translatePhrases);
    $this->_translatePhrases = $translatePhrases;
  }

  /**
  * Create fields array from definition
  *
  * @return array
  */
  public function getFields() {
    $fields = array();
    $group = NULL;
    foreach ($this->_editFields as $fieldName => $data) {
      if (is_string($data)) {
        $fields[] = $group = $this->_addGroup($data);
      } else {
        $field = $this->_addField($fieldName, $data);
        if (isset($field)) {
          if (isset($group)) {
            $group->fields()->add($field);
          } else {
            $fields[] = $field;
          }
        }
      }
    }
    return $fields;
  }

  /**
  * Add a field group object, group definitions hav only a caption
  *
  * @param string $caption
  * @return \PapayaUiDialogFieldGroup
  */
  private function _addGroup($caption) {
    $group = new \PapayaUiDialogFieldGroup(
      $this->_createPhrase($caption)
    );
    return $group;
  }

  /**
  * Read the field definition and create a field from it
  *
  * The field definition is a numerical array.
  *
  * @param string $name
  * @param array $data
  * @return \PapayaUiDialogField|NULL
   */
  private function _addField($name, array $data) {
    $type = (string)\PapayaUtilArray::get($data, array('type', 3), 'input');
    if (substr($type, 0, 9) === 'disabled_') {
      $type = substr($type, 9);
      $disabled = TRUE;
    } else {
      $disabled = FALSE;
    }

    $type = \PapayaUtilArray::get($this->_fieldMapping, $type, $type);
    $filter = \PapayaUtilArray::get($data, array('validation', 1), 'isNotEmpty');
    if (is_string($filter) && !empty($filter)) {
      $filter = \PapayaUtilArray::get($this->_filterMapping, $filter, $filter);
    }
    $options = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => $name,
        'caption' => \PapayaUtilArray::get($data, array('caption', 0), ''),
        'validation' => $filter,
        'mandatory' => (bool)\PapayaUtilArray::get($data, array('mandatory', 2), FALSE),
        'parameters' => \PapayaUtilArray::get($data, array('parameters', 4), NULL),
        'url' => \PapayaUtilArray::get($data, array('url', 0), ''),
        'hint' => \PapayaUtilArray::get($data, array('hint', 5), ''),
        'default' => \PapayaUtilArray::get($data, array('default', 6), NULL),
        'disabled' => \PapayaUtilArray::get($data, 'disabled', $disabled),
        'context' => $this->_owner
      )
    );
    return $this->fieldFactory()->getField($type, $options);
  }

  /**
  * If a phrase could need a translation, this method is used to wrap it into an object.
  *
  * @param string $string
  * @return string|\PapayaUiStringTranslated
  */
  private function _createPhrase($string) {
    return $this->_translatePhrases ? new \PapayaUiStringTranslated($string) : $string;
  }

  /**
   * Getter/Setter for the field factory
   *
   * @param \PapayaUiDialogFieldFactory $factory
   * @return \PapayaUiDialogFieldFactory
   */
  public function fieldFactory(\PapayaUiDialogFieldFactory $factory = NULL) {
    if (isset($factory)) {
      $this->_fieldFactory = $factory;
    } elseif (NULL === $this->_fieldFactory) {
      $this->_fieldFactory = new \PapayaUiDialogFieldFactory();
    }
    return $this->_fieldFactory;
  }
}
