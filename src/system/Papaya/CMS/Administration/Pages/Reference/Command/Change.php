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
use Papaya\CMS\Content;
use Papaya\UI;

/**
 * Add/save a page dependency.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Change extends UI\Control\Command\Dialog {
  /**
   * Create the add/edit dialog and assign callbacks.
   *
   * @return UI\Dialog\Database\Save
   */
  public function createDialog() {
    /** @var Administration\Pages\Dependency\Changer $changer */
    $changer = $this->owner();
    $pageId = $changer->getPageId();
    $record = $changer->reference();
    if (0 === (int)$record->sourceId) {
      $targetId = NULL;
    } elseif ((int)$record->sourceId === $pageId) {
      $targetId = $record->targetId;
    } else {
      $targetId = $record->sourceId;
    }

    $dialog = new UI\Dialog\Database\Save($record);

    $dialog->caption = new UI\Text\Translated('Page reference');
    $dialog->data->merge(
      [
        'source_id' => $pageId,
        'target_id' => $targetId
      ]
    );
    $dialog->parameterGroup('pageref');
    $dialog->hiddenFields->merge(
      [
        'cmd' => 'reference_change',
        'source_id' => $pageId
      ]
    );
    $dialog->hiddenValues->merge(
      [
        'tt' => [
          'cmd' => 'reference_change',
          'page_id' => $pageId,
          'target_id' => $targetId
        ]
      ]
    );

    $dialog->fields[] = $targetIdField = new UI\Dialog\Field\Input\Page(
      new UI\Text\Translated('Target page'), 'target_id', NULL, TRUE
    );
    $dialog->fields[] = new UI\Dialog\Field\Textarea(
      new UI\Text\Translated('Note'), 'note', 8, ''
    );
    $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Save'));

    $dialog->callbacks()->onBeforeSave = [$this, 'validateTarget'];
    $dialog->callbacks()->onBeforeSave->context->targetIdField = $targetIdField;

    $this->callbacks()->onExecuteSuccessful = [$this, 'dispatchSavedMessage'];
    $this->callbacks()->onExecuteFailed = [$this, 'dispatchErrorMessage'];

    return $dialog;
  }

  /**
   * Check that the references pages exists and if the are different from the current key,
   * a reference like this does not already exists.
   *
   * @param object $context
   * @param Content\Page\Reference $record
   *
   * @return bool
   */
  public function validateTarget($context, Content\Page\Reference $record) {
    list($sourceId, $targetId) = $this->sortAsc($record->sourceId, $record->targetId);
    $currentKey = $record->key()->getProperties();
    /* @noinspection TypeUnsafeComparisonInspection */
    if (
      $currentKey != ['source_id' => $sourceId, 'target_id' => $targetId] &&
      $record->exists($sourceId, $targetId)
    ) {
      $context->targetIdField->handleValidationFailure(
        new \Papaya\Filter\Exception\FailedCallback([$this, 'validateOrigin'])
      );
      return FALSE;
    }
    return TRUE;
  }

  /**
   * sort two numbers ascending.
   *
   * @param int $idOne
   * @param int $idTwo
   *
   * @return array
   */
  private function sortAsc($idOne, $idTwo) {
    if ((int)$idOne > (int)$idTwo) {
      return [$idTwo, $idOne];
    }
    return [$idOne, $idTwo];
  }

  /**
   * Callback to dispatch a message to the user that the record was saved.
   */
  public function dispatchSavedMessage() {
    $this->papaya()->messages->dispatch(
      new \Papaya\Message\Display\Translated(
        \Papaya\Message::SEVERITY_INFO, 'Reference saved.'
      )
    );
  }

  /**
   * Callback to dispatch a message to the user that here was an input error.
   *
   * @param $context
   * @param \Papaya\UI\Dialog $dialog
   */
  public function dispatchErrorMessage(
    /* @noinspection PhpUnusedParameterInspection */
    $context, UI\Dialog $dialog
  ) {
    $this->papaya()->messages->dispatch(
      new \Papaya\Message\Display\Translated(
        \Papaya\Message::SEVERITY_ERROR,
        'Invalid input. Please check the fields "%s".',
        [\implode(', ', $dialog->errors()->getSourceCaptions())]
      )
    );
  }
}
