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
namespace Papaya\CMS\Administration\Pages\Reference\Command;

use Papaya\CMS\Administration;
use Papaya\Message;
use Papaya\UI;

/**
 * Delete a page reference.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Delete
  extends UI\Control\Command\Dialog {
  /**
   * Create confirmation dialog and assign callback for confirmation message.
   */
  public function createDialog() {
    /** @var Administration\Pages\Dependency\Changer $changer */
    $changer = $this->owner();
    $dialog = new UI\Dialog\Database\Delete(
      $reference = $changer->reference()
    );
    $dialog->caption = new UI\Text\Translated('Delete');
    $dialog->parameterGroup($this->owner()->parameterGroup());
    $dialog->hiddenFields->merge(
      [
        'cmd' => 'reference_delete',
        'page_id' => $changer->getPageId(),
        'target_id' => $changer->getPageId() === (int)$reference->sourceId
          ? $reference->targetId : $reference->sourceId
      ]
    );
    $dialog->fields[] = new UI\Dialog\Field\Information(
      new UI\Text\Translated('Delete reference?'),
      'places-trash'
    );
    $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Delete'));

    $this->callbacks()->onExecuteSuccessful = [
      $this, 'dispatchDeleteMessage'
    ];
    return $dialog;
  }

  /**
   * Callback, dispatch the delete confirmation message to the user
   */
  public function dispatchDeleteMessage() {
    $this->papaya()->messages->dispatch(
      new Message\Display\Translated(
        Message::SEVERITY_INFO, 'Reference deleted.'
      )
    );
  }
}
