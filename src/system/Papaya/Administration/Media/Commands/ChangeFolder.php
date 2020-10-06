<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Administration\Media\Commands {

  use Papaya\Administration\Media\MediaFilesPage;
  use Papaya\Content\Media\Folder;
  use Papaya\Database;
  use Papaya\Filter\Text as TextFilter;
  use Papaya\UI\Control\Command\Dialog\Database\Record as DatabaseDialogCommand;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\UI\Dialog\Database\Save as DatabaseDialog;
  use Papaya\UI\Dialog\Field\Input as InputField;
  use Papaya\UI\Dialog\Field\Select as SelectField;
  use Papaya\UI\Dialog\Options as DialogOptions;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;

  /**
   * @method Folder record
   */
  class ChangeFolder
    extends DatabaseDialogCommand {

    const PARAMETER_TITLE = 'title';
    const PARAMETER_PERMISSION_MODE = 'permission_mode';

    private $_command;

    public function __construct(Database\Interfaces\Record $record, $command) {
      parent::__construct($record, self::ACTION_SAVE);
      $this->_command = $command;
    }

    protected function createDialog() {
      $dialog = new Dialog();
      $dialog->papaya($this->papaya());
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      if ($this->_command === MediaFilesPage::COMMAND_ADD_FOLDER) {
        $dialog->caption = new TranslatedText('Ádd Folder');
        $dialog->buttons[] = new SubmitButton(
          new TranslatedText('Add')
        );
      } else {
        $dialog->caption = new TranslatedText('Edit Folder');
        $dialog->buttons[] = new SubmitButton(
          new TranslatedText('Save')
        );
        $dialog->data->merge($this->record());
      }
      $dialog->hiddenFields()->merge(
        [
          MediaFilesPage::PARAMETER_COMMAND => $this->_command,
          MediaFilesPage::PARAMETER_FOLDER => $this->record()->id
        ]
      );
      $dialog->data()->set('language_id', $this->papaya()->administrationLanguage->id);
      $dialog->options->dialogWidth = DialogOptions::SIZE_LARGE;
      $dialog->fields[] = $field = new InputField(
        new TranslatedText('Title'),
        self::PARAMETER_TITLE,
        200,
        '',
        new TextFilter(TextFilter::ALLOW_SPACES | TextFilter::ALLOW_DIGITS)
      );
      $dialog->fields[] = $field = new SelectField(
        new TranslatedText('Permission Mode'),
        self::PARAMETER_PERMISSION_MODE,
        new TranslatedList(
          [
            Folder::PERMISSION_MODE_INHERIT => 'Inherit',
            Folder::PERMISSION_MODE_EXTEND => 'Extend',
            Folder::PERMISSION_MODE_DEFINE => 'Define',
          ]
        ),
        FALSE
      );
      $field->setDisabled($this->record()->parentId < 1);
      $this->resetAfterSuccess(TRUE);
      $this->callbacks()->onExecuteSuccessful = function () use ($dialog) {
        if ($this->_command === MediaFilesPage::COMMAND_ADD_FOLDER) {
          $parentId = (int)$this->record()->id;
          $ancestors = $this->record()->ancestors ?: [];
          $ancestors[] = $this->record()->parentId;
          array_unique($ancestors);
          $record = new Folder();
          $record->assign(
            [
              'parent_id' => $parentId,
              'ancestors' => $ancestors,
              'permission_mode' => $parentId > 0
                ? $dialog->parameters()->get(self::PARAMETER_PERMISSION_MODE,  Folder::PERMISSION_MODE_INHERIT)
                : Folder::PERMISSION_MODE_DEFINE,
              'language_id' => $this->papaya()->administrationLanguage->id,
              'title' => $dialog->parameters()->get(self::PARAMETER_TITLE, '')
            ]
          );
          if ($record->save()) {
            $this->papaya()->messages->displayInfo('Folder added.');
            $this->parameters()->set(MediaFilesPage::PARAMETER_FOLDER, $record->id);
            $this->record()->load(['id' => $record->id, 'language_id' => $this->papaya()->administrationLanguage->id]);
            if ($this->hasOwner()) {
              $this->owner()->parameters()->set(MediaFilesPage::PARAMETER_FOLDER, $record->id);
            }
          }
        } else {
          $this->record()->assign(
            [
              'permission_mode' => $this->record()->parentId > 0
                ? $dialog->parameters()->get(self::PARAMETER_PERMISSION_MODE,  Folder::PERMISSION_MODE_INHERIT)
                : Folder::PERMISSION_MODE_DEFINE,
              'title' => $dialog->parameters()->get(self::PARAMETER_TITLE, '')
            ]
          );
          if ($this->record()->save()) {
            $this->papaya()->messages->displayInfo('Folder saved.');
          }
        }
        $this->_command = MediaFilesPage::COMMAND_EDIT_FOLDER;
        return TRUE;
      };
      $this->callbacks()->onExecuteFailed = function (
        /* @noinspection PhpUnusedParameterInspection */
        $context, DatabaseDialog $dialog
      ) {
        $this->papaya()->messages->displayError(
          'Invalid input. Please check the field(s) "%s".',
          [\implode(', ', $dialog->errors()->getSourceCaptions())]
        );
      };
      return $dialog;
    }
  }
}


