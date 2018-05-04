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
* Add/save a page dependency.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesReferenceCommandChange extends PapayaUiControlCommandDialog {

  /**
  * Create the add/edit dialog and assign callbacks.
  *
  * @return PapayaUiDialogDatabaseSave
  */
  public function createDialog() {
    /** @var PapayaAdministrationPagesDependencyChanger $changer */
    $changer = $this->owner();
    $pageId = $changer->getPageId();
    $record = $changer->reference();
    if ($record->sourceId == 0) {
      $targetId = NULL;
    } elseif ($record->sourceId == $pageId) {
      $targetId = $record->targetId;
    } else {
      $targetId = $record->sourceId;
    }

    $dialog = new \PapayaUiDialogDatabaseSave($record);

    $dialog->caption = new \PapayaUiStringTranslated('Page reference');
    $dialog->data->merge(
      array(
        'source_id' => $pageId,
        'target_id' => $targetId
      )
    );
    $dialog->parameterGroup('pageref');
    $dialog->hiddenFields->merge(
      array(
        'cmd' => 'reference_change',
        'source_id' => $pageId
      )
    );
    $dialog->hiddenValues->merge(
      array(
        'tt' => array(
          'cmd' => 'reference_change',
          'page_id' => $pageId,
          'target_id' => $targetId
        )
      )
    );

    $dialog->fields[] = $targetIdField = new \PapayaUiDialogFieldInputPage(
      new \PapayaUiStringTranslated('Target page'), 'target_id', NULL, TRUE
    );
    $dialog->fields[] = new \PapayaUiDialogFieldTextarea(
      new \PapayaUiStringTranslated('Note'), 'note', 8, ''
    );
    $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(new \PapayaUiStringTranslated('Save'));

    $dialog->callbacks()->onBeforeSave = array($this, 'validateTarget');
    $dialog->callbacks()->onBeforeSave->context->targetIdField = $targetIdField;

    $this->callbacks()->onExecuteSuccessful = array($this, 'dispatchSavedMessage');
    $this->callbacks()->onExecuteFailed = array($this, 'dispatchErrorMessage');

    return $dialog;
  }

  /**
   * Check that the references pages exists and if the are different from the current key,
   * a reference like this does not already exists.
   *
   * @param object $context
   * @param PapayaContentPageReference $record
   * @return bool
   */
  public function validateTarget($context, PapayaContentPageReference $record) {
    list($sourceId, $targetId) = $this->sortAsc($record->sourceId, $record->targetId);
    $currentKey = $record->key()->getProperties();
    if (
        $currentKey != array('source_id' => $sourceId, 'target_id' => $targetId) &&
        $record->exists($sourceId, $targetId)
       ) {
      $context->targetIdField->handleValidationFailure(
        new \PapayaFilterExceptionCallbackFailed(array($this, 'validateOrigin'))
      );
      return FALSE;
    }
    return TRUE;
  }

  /**
   * sort two numbers ascending.
   *
   * @param integer $idOne
   * @param integer $idTwo
   * @return array
   */
  private function sortAsc($idOne, $idTwo) {
    if ((int)$idOne > (int)$idTwo) {
      return array($idTwo, $idOne);
    } else {
      return array($idOne, $idTwo);
    }
  }

  /**
  * Callback to dispatch a message to the user that the record was saved.
  */
  public function dispatchSavedMessage() {
    $this->papaya()->messages->dispatch(
      new \PapayaMessageDisplayTranslated(
        PapayaMessage::SEVERITY_INFO, 'Reference saved.'
      )
    );
  }

  /**
  * Callback to dispatch a message to the user that here was an input error.
  */
  public function dispatchErrorMessage($context, PapayaUiDialog $dialog) {
    $this->papaya()->messages->dispatch(
      new \PapayaMessageDisplayTranslated(
        PapayaMessage::SEVERITY_ERROR,
        'Invalid input. Please check the fields "%s".',
        array(implode(', ', $dialog->errors()->getSourceCaptions()))
      )
    );
  }
}
