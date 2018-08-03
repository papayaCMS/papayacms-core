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

namespace Papaya\Administration\Pages\Reference\Command;

use Papaya\Administration\Pages\Dependency\Changer;

/**
 * Delete a page reference.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Delete
  extends \Papaya\Ui\Control\Command\Dialog {

  /**
   * Create confirmation dialog and assign callback for confirmation message.
   */
  public function createDialog() {
    /** @var Changer $changer */
    $changer = $this->owner();
    $dialog = new \Papaya\Ui\Dialog\Database\Delete(
      $reference = $changer->reference()
    );
    $dialog->caption = new \PapayaUiStringTranslated('Delete');
    $dialog->parameterGroup($this->owner()->parameterGroup());
    $dialog->hiddenFields->merge(
      array(
        'cmd' => 'reference_delete',
        'page_id' => $changer->getPageId(),
        'target_id' => $changer->getPageId() == $reference->sourceId
          ? $reference->targetId : $reference->sourceId
      )
    );
    $dialog->fields[] = new \Papaya\Ui\Dialog\Field\Information(
      new \PapayaUiStringTranslated('Delete reference?'),
      'places-trash'
    );
    $dialog->buttons[] = new \Papaya\Ui\Dialog\Button\Submit(new \PapayaUiStringTranslated('Delete'));

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
      new \Papaya\Message\Display\Translated(
        \Papaya\Message::SEVERITY_INFO, 'Reference deleted.'
      )
    );
  }
}
