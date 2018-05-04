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
* Delete a page dependency.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencyCommandDelete
  extends PapayaUiControlCommandDialog {

  /**
  * Create confirmation dialog and assign callback for confirmation message.
  */
  public function createDialog() {
    /** @var PapayaAdministrationPagesDependencyChanger $changer */
    $changer = $this->owner();
    $dialog = new \PapayaUiDialogDatabaseDelete($changer->dependency());
    $dialog->caption = new \PapayaUiStringTranslated('Delete');
    $dialog->parameterGroup($this->owner()->parameterGroup());
    $dialog->hiddenFields->merge(
      array(
        'cmd' => 'dependency_delete',
        'page_id' => $changer->getPageId()
      )
    );
    $dialog->fields[] = new \PapayaUiDialogFieldInformation(
      new \PapayaUiStringTranslated('Delete dependency?'),
      'places-trash'
    );
    $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(new \PapayaUiStringTranslated('Delete'));

    $this->callbacks()->onExecuteSuccessful = array(
      $this, 'dispatchDeleteMessage'
    );
    return $dialog;
  }

  /**
  * Callback, dispatch the delete confirmation message to the user
  */
  public function dispatchDeleteMessage() {
    $this->papaya()->messages->dispatch(
      new \PapayaMessageDisplayTranslated(
        PapayaMessage::SEVERITY_INFO, 'Dependency deleted.'
      )
    );
  }
}
