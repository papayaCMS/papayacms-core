<?php
/**
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

namespace Papaya\Administration\Settings\Commands {

  use Papaya\Administration\Settings\SettingGroups;
  use Papaya\Administration\Settings\SettingsPage;
  use Papaya\Configuration\CMS;
  use Papaya\Content\Configuration\Setting;
  use Papaya\Message\Logable;
  use Papaya\UI\Control\Command\Dialog\Database\Record as DatabaseDialogCommand;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\UI\Sheet;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\Utility\File\Path as PathUtility;

  /**
   * @method Setting record(Setting $setting = NULL)
   */
  class EditSetting extends DatabaseDialogCommand {

    /**
     * @var mixed|SettingGroups
     */
    private $_groups;

    /**
     * @return Dialog\Database\Save
     */
    public function createDialog() {
      $settingName = $this->parameters()->get(SettingsPage::PARAMETER_SETTING, '');
      $dialog = parent::createDialog();
      $dialog->options->captionStyle = Dialog\Options::CAPTION_NONE;
      $dialog->options->dialogWidth = Dialog\Options::SIZE_LARGE;
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->hiddenFields()->merge(
        [
          SettingsPage::PARAMETER_COMMAND => SettingsPage::COMMAND_EDIT,
          SettingsPage::PARAMETER_SETTING => $settingName,
          SettingsPage::PARAMETER_SEARCH_FOR => $this->parameters()->get(SettingsPage::PARAMETER_SEARCH_FOR)
        ]
      );
      $dialog->caption = new TranslatedText('Edit Setting');
      if ($this->record()->isLoaded()) {
        $dialog->data()->merge($this->record());
        $currentValue = $this->record()->value;
      } else {
        $currentValue = $this->papaya()->options->get($settingName);
        $dialog->data()->merge(
          [
            'name' => $settingName,
            'value' => $currentValue
          ]
        );
      }
      if ($profile = $this->groups()->getProfile($settingName)) {
        $dialog->fields[] = $field = new Dialog\Field\Sheet();
        $sheet = $field->sheet();
        $sheet->padding = Sheet::PADDING_MEDIUM;
        $sheet->title($settingName);
        $sheet->subtitles()->addString(
          (new TranslatedText('Request / Active')).': '.
          $profile->getDisplayString($this->papaya()->options[$settingName])
        );
        if ($this->record()->isLoaded()) {
          $sheet->subtitles()->addString(
            (new TranslatedText('Database')).': '.
            $profile->getDisplayString($this->record()->value)
          );
        }
        $sheet->content()->appendElement('p')->appendXML($this->getNote($settingName));
        if ($editable = $profile->appendFieldTo($dialog, $settingName)) {
          $dialog->buttons[] = new SubmitButton(new TranslatedText('Save'));
        } else {
          $dialog->caption = new TranslatedText('Display Setting (Read Only)');
        }
      } else {
        $dialog->fields[] = $field = new Dialog\Field\Sheet();
        $sheet = $field->sheet();
        $sheet->padding = Sheet::PADDING_MEDIUM;
        $sheet->title($this->record()->name);
        $sheet->content()->appendElement('p')->appendXML($this->getNote($settingName));
      }
      $this->callbacks()->onExecuteSuccessful = function() use ($settingName, $currentValue) {
        $this->papaya()->messages->displayInfo('Setting saved.');
        if ((string)$currentValue !== (string)$this->record()->value) {
          if ($profile = $this->groups()->getProfile($settingName)) {
            $getDisplayString = static function($value) use ($profile) {
              return $profile->getDisplayString($value);
            };
          } else {
            $getDisplayString = static function($value) {
              return $value;
            };
          }
          $context = new \Papaya\Message\Context\Table('');
          $context->addRow(
            ['New', $getDisplayString($this->record()->value)]
          );
          $context->addRow(
            ['Old', $getDisplayString($currentValue)]
          );
          $this->papaya()->messages->log(
            Logable::SEVERITY_INFO,
            Logable::GROUP_SYSTEM,
            'Changed setting: '.$settingName,
            $context
          );
        }
        $this->record()->load(['name' => $settingName]);
      };
      $this->callbacks()->onExecuteFailed = function() {
        $this->papaya()->messages->displayError('Invalid setting value.');
      };
      $this->resetAfterSuccess(TRUE);
      return $dialog;
    }

    /**
     * @param SettingGroups|NULL $groups
     * @return SettingGroups
     */
    public function groups(SettingGroups $groups = NULL) {
      if (NULL !== $groups) {
        $this->_groups = $groups;
      } elseif (NULL === $this->_groups) {
        $this->_groups = new SettingGroups();
        $this->_groups->papaya($this->papaya());
      }
      return $this->_groups;
    }

    /**
     * @param $setting
     * @return string
     */
    private function getNote($setting) {
      $file = (
        PathUtility::getBasePath(TRUE).
        $this->papaya()->options[CMS::PATH_ADMIN].'/data/'.$this->papaya()->administrationLanguage->code.
        '/doc/conf/'.$setting.'.txt'
      );
      if (file_exists($file)) {
        return (string)file_get_contents($file);
      }
      return '';
    }
  }
}

