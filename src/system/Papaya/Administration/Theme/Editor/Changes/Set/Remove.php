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
 * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Remove
  extends \PapayaUiControlCommandDialogDatabaseRecord {

  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see \PapayaUiControlCommandDialog::createDialog()
   * @return \PapayaUiDialog
   */
  public function createDialog() {
    $setId = $this->parameters()->get('set_id', 0);
    if ($setId > 0) {
      $loaded = $this->record()->load($setId);
    } else {
      $loaded = FALSE;
    }
    $dialog = new \PapayaUiDialogDatabaseDelete($this->record());
    $dialog->papaya($this->papaya());
    $dialog->caption = new \PapayaUiStringTranslated('Delete theme set');
    if ($loaded) {
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => 'set_delete',
          'theme' => $this->parameters()->get('theme', ''),
          'set_id' => $setId
        )
      );
      $dialog->fields[] = new \PapayaUiDialogFieldInformation(
        new \PapayaUiStringTranslated('Delete theme set'),
        'places-trash'
      );
      $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(new \PapayaUiStringTranslated('Delete'));
      $this->callbacks()->onExecuteSuccessful = array($this, 'callbackDeleted');
    } else {
      $dialog->fields[] = new \PapayaUiDialogFieldMessage(
        \PapayaMessage::SEVERITY_INFO, 'Theme set not found.'
      );
    }
    return $dialog;
  }

  /**
   * Show success message
   */
  public function callbackDeleted() {
    $this->papaya()->messages->dispatch(
      new \PapayaMessageDisplayTranslated(
        \PapayaMessage::SEVERITY_INFO,
        'Theme set deleted.'
      )
    );
  }
}
