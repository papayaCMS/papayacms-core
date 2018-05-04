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
* An PluginEditor implementation that build a dialog based on an array of field definitions
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPluginEditorFields extends PapayaAdministrationPluginEditorDialog {

  private $_fields = array();

  private $_builder = NULL;

  /**
   * Create the object store plugin instance and field definition
   *
   * @param \PapayaPluginEditableContent $content
   * @param array $fields
   */
  public function __construct(\PapayaPluginEditableContent $content, array $fields) {
    parent::__construct($content);
    \PapayaUtilConstraints::assertArrayOrTraversable($fields);
    $this->_fields = $fields;
  }

  /**
   * Create a dialog isntance and initialize it.
   *
   * @return \PapayaUiDialog
   */
  protected function createDialog() {
    $dialog = parent::createDialog();
    $dialog->fields = $this->builder()->getFields();
    return $dialog;
  }

  /**
   * Getter/Setter for a dialog field builder. It maps the field definitions to profiles
   * and uses a factory to create the field instances.
   *
   * @param \PapayaUiDialogFieldBuilderArray $builder
   * @return \PapayaUiDialogFieldBuilderArray
   */
  public function builder(\PapayaUiDialogFieldBuilderArray $builder = NULL) {
    if (isset($builder)) {
      $this->_builder = $builder;
    } elseif (NULL === $this->_builder) {
      $this->_builder = new \PapayaUiDialogFieldBuilderArray($this, $this->_fields);
    }
    return $this->_builder;
  }
}
