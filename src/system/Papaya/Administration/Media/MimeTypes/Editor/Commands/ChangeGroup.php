<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Administration\Media\MimeTypes\Editor\Commands {

  use Papaya\Database;
  use Papaya\Filter\Text as TextFilter;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Glob as GlobIterator;
  use Papaya\UI\Control\Command\Dialog\Database\Record as DatabaseDialogCommand;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\UI\Dialog\Database\Save as DatabaseDialog;
  use Papaya\UI\Dialog\Field\Input as InputField;
  use Papaya\UI\Dialog\Field\Select as SelectField;
  use Papaya\UI\Text\Translated as TranslatedText;

  /**
   * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
   *
   * @package Papaya-Library
   * @subpackage Administration
   */
  class ChangeGroup
    extends DatabaseDialogCommand {

    private $_iconPath;

    public function __construct(Database\Interfaces\Record $record, $iconPath) {
      parent::__construct($record,  self::ACTION_SAVE);
      $this->_iconPath = $iconPath;
    }

    /**
     * @return DatabaseDialog
     */
    public function createDialog() {
      $groupId = $this->parameters()->get('group_id', 0);
      $languageId = $this->papaya()->administrationLanguage->id;
      $dialogCaption = 'Add Mime Type Group';
      $buttonCaption = 'Add';
      if ($groupId > 0) {
        if ($this->record()->load(['id' => $groupId, 'language_id' => $languageId])) {
          $dialogCaption = 'Edit Mime Type Group';
          $buttonCaption = 'Save';
        } else {
          $groupId = 0;
        }
        $this->record()['language_id'] = $languageId;
      }
      $dialog = new DatabaseDialog($this->record());
      $dialog->papaya($this->papaya());
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        [
          'cmd' => 'group_edit',
          'group_id' => $groupId,
          'type_id' => 0
        ]
      );
      $dialog->caption = new TranslatedText($dialogCaption);
      $dialog->fields[] = $field = new InputField(
        new TranslatedText('Title'), 'title', 200, '', new TextFilter()
      );
      $dialog->fields[] = $field = new SelectField(
        new TranslatedText('Icon'),
        'icon',
        new CallbackIterator(
          new GlobIterator(
            $this->_iconPath.'/*.svg'
          ),
          static function($value) {
            return str_replace(['.svg', '.png'], '', basename($value));
          },
          CallbackIterator::MODIFY_BOTH
        ),
        FALSE
      );
      $dialog->buttons[] = new SubmitButton(
        new TranslatedText($buttonCaption)
      );
      $this->callbacks()->onExecuteSuccessful = function () {
        $this->papaya()->messages->displayInfo('Mime type group saved.');
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
