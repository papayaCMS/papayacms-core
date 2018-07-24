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

namespace Papaya\Administration\Theme\Editor\Changes\Set;

/**
 * Import theme set values from an uploaded file
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Import
  extends \PapayaUiControlCommandDialog {

  /**
   * @var \Papaya\Content\Theme\Set
   */
  private $_themeSet;
  /**
   * @var \PapayaThemeHandler
   */
  private $_themeHandler;

  public function __construct(\Papaya\Content\Theme\Set $themeSet, \PapayaThemeHandler $themeHandler) {
    $this->_themeSet = $themeSet;
    $this->_themeHandler = $themeHandler;
  }

  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see \PapayaUiControlCommandDialog::createDialog()
   * @return \PapayaUiDialog
   */
  public function createDialog() {
    $setId = $this->parameters()->get('set_id', 0);
    $dialog = parent::createDialog();
    $dialog->caption = new \PapayaUiStringTranslated('Import');
    $dialog->setEncoding('multipart/form-data');
    $dialog->parameterGroup($this->parameterGroup());
    $dialog->parameters($this->parameters());
    $dialog->hiddenFields()->merge(
      array(
        'cmd' => 'set_import',
        'theme' => $this->parameters()->get('theme', ''),
        'set_id' => $setId
      )
    );
    $dialog->fields[] = $uploadField = new \PapayaUiDialogFieldFileTemporary(
      new \PapayaUiStringTranslated('File'), 'values/file'
    );
    $uploadField->setMandatory(TRUE);
    if ($setId > 0) {
      $dialog->fields[] = $field = new \PapayaUiDialogFieldSelectRadio(
        new \PapayaUiStringTranslated('Replace current set.'),
        'values/confirm_replace',
        array(
          TRUE => new \PapayaUiStringTranslated('Yes'),
          FALSE => new \PapayaUiStringTranslated('No')
        )
      );
      $field->setDefaultValue(FALSE);
    }
    $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(
      new \PapayaUiStringTranslated('Upload')
    );
    $this->callbacks()->onExecuteSuccessful = array($this, 'onValidationSuccess');
    $this->callbacks()->onExecuteSuccessful->context = $uploadField;
    return $dialog;
  }

  /**
   * @param \PapayaUiDialogFieldFileTemporary $uploadField
   * @return bool
   * @throws \PapayaXmlException
   */
  public function onValidationSuccess(\PapayaUiDialogFieldFileTemporary $uploadField) {
    $theme = $this->parameters()->get('theme', '');
    if (!empty($theme)) {
      $file = $uploadField->file();
      $errors = new \PapayaXmlErrors();
      try {
        $errors->activate();
        $dom = new \PapayaXmlDocument();
        $dom->load($file['temporary']);
        if ($dom->documentElement) {
          /** @var \PapayaXmlElement $documentElement */
          $documentElement = $dom->documentElement;
          $setId = $this->parameters()->get('set_id', 0);
          if ($setId > 0 && $this->parameters()->get('values/confirm_replace')) {
            if ($this->_themeSet->load($setId)) {
              $this->_themeSet->setValuesXml(
                $this->_themeHandler->getDefinition($theme),
                $documentElement
              );
            }
          } else {
            $this->_themeSet->assign(
              array(
                'title' => new \PapayaUiStringTranslated('* Imported Set'),
                'theme' => $theme
              )
            );
            $this->_themeSet->setValuesXml(
              $this->_themeHandler->getDefinition($theme),
              $documentElement
            );
          }
          if ($this->_themeSet->save()) {
            $this->papaya()->messages->dispatch(
              new \PapayaMessageDisplayTranslated(
                \PapayaMessage::SEVERITY_INFO,
                'Values imported.'
              )
            );
            return TRUE;
          }
        }
        //@codeCoverageIgnoreStart
      } catch (\PapayaXmlException $e) {
        $errors->emit();
      }
      //@codeCoverageIgnoreEnd
      $errors->deactivate();
    }
    return FALSE;
  }
}
