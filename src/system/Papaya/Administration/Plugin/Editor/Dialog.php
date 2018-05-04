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
class PapayaAdministrationPluginEditorDialog extends PapayaPluginEditor {

  private $_dialog = NULL;

  /**
   * Execute and append the dialog to to the administration interface DOM.
   *
   * @see \PapayaXmlAppendable::appendTo()
   * @param \PapayaXmlElement $parent
   */
  public function appendTo(\PapayaXmlElement $parent) {
    $context = $this->context();
    if (!$context->isEmpty()) {
      $this->dialog()->hiddenValues()->merge($context);
    }
    if ($this->dialog()->execute()) {
      $this->getContent()->assign($this->dialog()->data());
    } elseif ($this->dialog()->isSubmitted()) {
      $this->papaya()->messages->dispatch(
        new \PapayaMessageDisplayTranslated(
          \PapayaMessage::SEVERITY_ERROR,
          'Invalid input. Please check the field(s) "%s".',
          array(implode(', ', $this->dialog()->errors()->getSourceCaptions()))
        )
      );
    }
    $parent->append($this->dialog());
  }

  /**
   * Getter/Setter for the dialog subobject.
   *
   * @param \PapayaUiDialog $dialog
   * @return \PapayaUiDialog
   */
  public function dialog(\PapayaUiDialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $this->createDialog();
    }
    return $this->_dialog;
  }

  /**
   * Create a dialog instance and initialize it.
   *
   * @return \PapayaUiDialog
   */
  protected function createDialog() {
    $dialog = new \PapayaUiDialog();
    $dialog->papaya($this->papaya());

    $dialog->caption = new \PapayaAdministrationLanguagesCaption(
      new \PapayaUiStringTranslated('Edit content')
    );
    $dialog->image = new \PapayaAdministrationLanguagesImage();

    $dialog->options->topButtons = TRUE;

    $dialog->parameterGroup('content');
    $dialog->data()->assign($this->getContent());

    $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(new \PapayaUiStringTranslated('Save'));

    return $dialog;
  }
}
