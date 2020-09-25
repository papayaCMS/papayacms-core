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

namespace Papaya\Administration\Settings\Commands {

  use Papaya\Administration\Settings\SettingGroups;
  use Papaya\Administration\Settings\SettingsPage;
  use Papaya\Content\Configuration as SettingValues;
  use Papaya\CSV\Reader;
  use Papaya\Iterator\Filter\Callback as CallbackFilterIterator;
  use Papaya\Message;
  use Papaya\UI\Control\Command;
  use Papaya\UI\Dialog;
  use Papaya\UI\ListView;
  use Papaya\UI\Text\Translated;
  use Papaya\Utility\Arrays;
  use Papaya\XML\Element;

  class ImportSettings extends Command
  {

    /**
     * @var Dialog
     */
    private $_uploadDialog;

    /**
     * @var Dialog
     */
    private $_selectionDialog;
    /**
     * @var mixed|SettingGroups
     */
    private $_groups;

    private $_values;

    private $_newValues = [];

    public function appendTo(Element $parent)
    {
      if ($this->isConfirmation()) {
        $this->_newValues = $this->parameters()->get(SettingsPage::PARAMETER_IMPORT_SETTING, []);
        $selectionDialog = $this->selectionDialog();
        if ($selectionDialog->execute()) {
          $counter = 0;
          $changes = new Message\Context\Table('Changes');
          $changes->setColumns(
            ['name' => 'Setting', 'value' => 'Value']
          );
          foreach ($this->_newValues as $setting => $value) {
            if ($profile = $this->groups()->getProfile($setting)) {
              $item = $this->values()->getItem();
              $item->assign(
                ['name' => $setting, 'value' => $value]
              );
              if (FALSE === $item->save()) {
                break;
              }
              $changes->addRow(iterator_to_array($item));
              $counter++;
            }
          }
          $this->papaya()->messages->displayInfo(
            'Updated %s setting(s).', [$counter]
          );
          $this->papaya()->messages->log(
            Message\Logable::SEVERITY_INFO,
            Message\Logable::GROUP_SYSTEM,
            'Imported settings',
            $changes
          );
        }
        $parent->append($this->uploadDialog());
      } else {
        $uploadDialog = $this->uploadDialog();
        if ($uploadDialog->execute()) {
          if ($this->_newValues = $this->readSettings((string)$uploadDialog->fields[0]->file())) {
            $parent->append($this->selectionDialog());
          } else {
            $this->papaya()->messages->displayError(
              'Could not read settings from uploaded file.'
            );
          }
        } else {
          $parent->append($uploadDialog);
        }
      }
    }

    public function uploadDialog(Dialog $dialog = NULL)
    {
      if (NULL !== $dialog) {
        $this->_uploadDialog = $dialog;
      } elseif (NULL === $this->_uploadDialog) {
        $this->_uploadDialog = $dialog = new Dialog();
        $dialog->setEncoding('multipart/form-data');
        $dialog->papaya($this->papaya());
        $dialog->parameterGroup($this->parameterGroup());
        $dialog->hiddenFields()->merge(
          [
            SettingsPage::PARAMETER_COMMAND => SettingsPage::COMMAND_IMPORT
          ]
        );
        $dialog->caption = new Translated('Settings Import');
        $dialog->fields[] = $fileField = new \Papaya\UI\Dialog\Field\File\Temporary(
          'CSV file', SettingsPage::PARAMETER_IMPORT_FILE
        );
        $fileField->setMandatory(TRUE);
        $fileField->acceptFileTypes('text/csv');
        $dialog->buttons[] = new Dialog\Button\Submit(new Translated('Upload'));
      }
      return $this->_uploadDialog;
    }

    public function selectionDialog(Dialog $dialog = NULL)
    {
      if (NULL !== $dialog) {
        $this->_selectionDialog = $dialog;
      } elseif (NULL === $this->_selectionDialog) {
        $this->_selectionDialog = $dialog = new Dialog();
        $dialog->papaya($this->papaya());
        $dialog->parameterGroup($this->parameterGroup());
        $dialog->hiddenFields()->merge(
          [
            SettingsPage::PARAMETER_COMMAND => SettingsPage::COMMAND_IMPORT_CONFIRM
          ]
        );
        $dialog->caption = new Translated('Settings Selection');
        $dialog->fields[] = new Dialog\Field\ListView(
          $listView = new ListView()
        );
        $groups = $this->groups();
        $newValues = $this->_newValues;
        $currentValues = [];
        foreach ($this->values() as $record) {
          $currentValues[$record['name']] = $record['value'];
        }
        foreach ($groups->getTranslatedLabels() as $group => $label) {
          $settings = new CallbackFilterIterator(
            $groups->getSettingsInGroup($group),
            function ($setting) use ($currentValues, $newValues) {
              return (
                isset($newValues[$setting], $currentValues[$setting]) &&
                (
                  (string)$newValues[$setting] !==
                  (string)$currentValues[$setting]
                )
              );
            }
          );
          if (iterator_count($settings) > 0) {
            $listView->items[] = $item = new ListView\Item('items.folder', $label);
            $item->columnSpan = 2;
            foreach ($settings as $setting) {
              if ($profile = $this->groups()->getProfile($setting)) {
                $settingParameterName = SettingsPage::PARAMETER_IMPORT_SETTING.'['.$setting.']';
                $dialog->data()->set($settingParameterName, $newValues[$setting]);
                $listView->items[] = $item = new ListView\Item\Checkbox(
                  'items.option',
                  $setting,
                  $dialog,
                  $settingParameterName,
                  $newValues[$setting]
                );
                $item->indentation = 1;
                $item->subitems[] = new ListView\SubItem\Text(
                  $profile->getDisplayString($newValues[$setting])
                );
              }
            }
          }
        };
        $dialog->buttons[] = new Dialog\Button\Submit(new Translated('Save'));
      }
      return $this->_selectionDialog;
    }

    public function groups(SettingGroups $groups = NULL)
    {
      if (NULL !== $groups) {
        $this->_groups = $groups;
      } elseif (NULL === $this->_groups) {
        $this->_groups = new SettingGroups();
        $this->_groups->papaya($this->papaya());
      }
      return $this->_groups;
    }

    public function values(SettingValues $values = NULL)
    {
      if (NULL !== $values) {
        $this->_values = $values;
      } elseif (NULL === $this->_values) {
        $this->_values = new SettingValues();
        $this->_values->papaya($this->papaya());
        $this->_values->activateLazyLoad();
      }
      return $this->_values;
    }

    private function isConfirmation() {
      return (
        $this->parameters()->get(SettingsPage::PARAMETER_COMMAND, '') ===
        SettingsPage::COMMAND_IMPORT_CONFIRM
      );
    }

    private function readSettings($fileName)
    {
      $csvReader = new Reader($fileName);
      $values = [];
      foreach ($csvReader as $row) {
        $values[Arrays::get($row, ['setting', 0])] = Arrays::get($row, ['value', 1]);
      }
      return $values;
    }
  }
}

