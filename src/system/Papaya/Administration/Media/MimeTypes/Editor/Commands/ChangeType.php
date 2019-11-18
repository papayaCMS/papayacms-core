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

  use Papaya\Content\Media\MimeType;
  use Papaya\Database;
  use Papaya\Filter\ArrayOf as ArrayOfFilter;
  use Papaya\Filter\RegEx as RegExFilter;
  use Papaya\Filter\Text as TextFilter;
  use Papaya\Iterator\ArrayMapper;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Glob as GlobIterator;
  use Papaya\UI\Control\Command\Dialog\Database\Record as DatabaseDialogCommand;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\UI\Dialog\Database\Save as DatabaseDialog;
  use Papaya\UI\Dialog\Field\Group as DialogFieldGroup;
  use Papaya\UI\Dialog\Field\Input as InputField;
  use Papaya\UI\Dialog\Field\Select as SelectField;
  use Papaya\UI\Dialog\Field\Select\Radio as RadioGroupField;
  use Papaya\UI\Dialog\Options as DialogOptions;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\Utility\Bytes as BytesUtility;

  /**
   * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
   *
   * @package Papaya-Library
   * @subpackage Administration
   */
  class ChangeType
    extends DatabaseDialogCommand {

    private $_iconPath;
    /**
     * @var MimeType\Groups
     */
    private $_groups;

    public function __construct(Database\Interfaces\Record $record, $iconPath) {
      parent::__construct($record,  self::ACTION_SAVE);
      $this->_iconPath = $iconPath;
    }

    /**
     * @return DatabaseDialog
     */
    public function createDialog() {
      $groupId = $this->parameters()->get('group_id', 0);
      $typeId = $this->parameters()->get('type_id', 0);
      $dialogCaption = 'Add Mime Type';
      $buttonCaption = 'Add';
      if ($groupId > 0) {
        if ($this->record()->load(['id' => $typeId])) {
          $dialogCaption = 'Edit Mime Type';
          $buttonCaption = 'Save';
          // fix old icon values (png files)
          $this->record()->icon = str_replace('.png', '.svg', $this->record()->icon);
        } else {
          $groupId = 0;
        }
      }
      $dialog = new DatabaseDialog($this->record());
      $dialog->papaya($this->papaya());
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        [
          'cmd' => 'type_edit',
          'type_id' => $typeId
        ]
      );
      $dialog->data()->merge(
        [
          'extensions' => $this->getExtensions()
        ]
      );
      $dialog->options->dialogWidth = DialogOptions::SIZE_LARGE;
      $dialog->caption = new TranslatedText($dialogCaption);
      $dialog->fields[] = $group = new DialogFieldGroup(new TranslatedText('Properties'));
      $group->fields[] = $field = new InputField(
        new TranslatedText('Type'),
        'type',
        200,
        '',
        new RegExFilter('(^[\\w\\d-]+/[\\w\\d-]+$)D')
      );
      $group->fields[] = $field = new SelectField(
        new TranslatedText('Icon'),
        'icon',
        new CallbackIterator(
          new GlobIterator(
            $this->_iconPath.'/*.svg'
          ),
          static function($value, /** @noinspection PhpUnusedParameterInspection */ $key, $mode) {
            if ($mode === CallbackIterator::MODIFY_KEYS) {
              return basename($value);
            }
            return str_replace(['.svg', '.png'], '', basename($value));
          },
          CallbackIterator::MODIFY_BOTH
        ),
        FALSE
      );
      $group->fields[] = $field = new SelectField(
        new TranslatedText('Group'),
        'group_id',
        new ArrayMapper($this->groups(), 'title'),
        FALSE
      );
      $field->setDefaultValue($groupId);
      $group->fields[] = $field = new InputField\MultipleValues(
        new TranslatedText('Extensions'),
        'extensions',
        -1,
        '',
        new ArrayOfFilter(new RegExFilter('(^[\w]+$)D'))
      );
      $dialog->fields[] = $group = new DialogFieldGroup(new TranslatedText('Delivery Options'));
      $group->fields[] = $field = new RadioGroupField(
        new TranslatedText('Supports Range Headers'),
        'supports_ranges',
        new TranslatedList([TRUE => 'Yes', FALSE => 'No']
        )
      );
      $group->fields[] = $field = new RadioGroupField(
        new TranslatedText('Enable Bandwidth Limiter'),
        'enable_shaping',
        new TranslatedList([TRUE => 'Yes', FALSE => 'No']
        )
      );
      $dialog->fields[] = $field = new InputField\MappedValue(
        new TranslatedText('Bandwidth Limit (per second)'),
        'shaping_limit',
        200,
        0,
        new RegExFilter(
         '(^\\d+\\s*(?:bytes|byte|b|bit|kb|kbit|mb|mbit)?(?:/s)?$)iD'
        )
      );
      $field->setHint('You can enter units like 1kB or 1Mbit.');
      $field->callbacks()->mapToDisplay = static function($bytes) {
        return $bytes > 0 ? BytesUtility::toString($bytes) : '';
      };
      $field->callbacks()->mapFromDisplay = static function($input) {
        return BytesUtility::fromString($input);
      };
      $dialog->fields[] = $field = new InputField\MappedValue(
        new TranslatedText(
          'Bandwidth Offset'),
        'shaping_offset',
        200,
        0,
        new RegExFilter(
          '(^\\d+\\s*(?:bytes|byte|b|bit|kb|kbit|mb|mbit)?$)iD'
        )
      );
      $field->setHint( 'This part of the file will be send immediately. You can enter units like 1kB or 1Mbit.');
      $field->callbacks()->mapToDisplay = static function($bytes) {
        return $bytes > 0 ? BytesUtility::toString($bytes) : '';
      };
      $field->callbacks()->mapFromDisplay = static function($input) {
        return BytesUtility::fromString($input);
      };
      $dialog->buttons[] = new SubmitButton(
        new TranslatedText($buttonCaption)
      );
      $dialog->callbacks()->onBeforeSave = function() use ($dialog) {
        // set the first extension as default extension on the mime type
        $extensions = $dialog->data()->get('extensions', []);
        $dialog->data()->set('extension', (string)reset($extensions));
        /** @var MimeType $mimeType */
        $mimeType = $this->record();
        // save extensions as separate records
        try {
          return $mimeType->extensions()->update($mimeType->id, $dialog->data()->get('extensions', []));
        } catch (MimeType\ExtensionsConflict $exception) {
          $this->papaya()->messages->displayError(
            'Invalid input.  The following extension(s) are already used by other mime types: %s',
            [
              $exception->getExtensionUsageString()
            ]
          );
          return FALSE;
        }
      };
      $this->callbacks()->onExecuteSuccessful = function() use ($dialog) {
        $this->papaya()->messages->displayInfo('Mime type saved.');
      };
      $this->callbacks()->onExecuteFailed = function (
        /* @noinspection PhpUnusedParameterInspection */
        $context, DatabaseDialog $dialog
      ) {
        if (count( $dialog->errors()) > 0) {
          $this->papaya()->messages->displayError(
            'Invalid input. Please check the field(s) "%s".',
            [\implode(', ', $dialog->errors()->getSourceCaptions())]
          );
        }
      };
      return $dialog;
    }

    public function Groups(MimeType\Groups $groups = NULL) {
      if (NULL !== $groups) {
        $this->_groups = $groups;
      } elseif (NULL === $this->_groups) {
        $this->_groups = new MimeType\Groups();
        $this->_groups->papaya($this->papaya());
        $this->_groups->activateLazyLoad(
          ['language_id' => $this->papaya()->administrationLanguage->id]
        );
      }
      return $this->_groups;
    }

    private function getExtensions() {
      /** @var MimeType $mimeType */
      $mimeType = $this->record();
      $extensions = iterator_to_array(
        new ArrayMapper($mimeType->extensions(), 'extension')
      );
      $defaultExtension = $mimeType->extension;
      if (!empty($defaultExtension)) {
        if (FALSE !== ($position = array_search($defaultExtension, $extensions, TRUE))) {
          unset($extensions[$position]);
        }
        array_unshift($extensions, $defaultExtension);
      }
      return $extensions;
    }
  }
}
