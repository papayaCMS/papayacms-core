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

namespace Papaya\Administration\Settings {

  use Papaya\Administration\Page\Part as AdministrationPagePart;
  use Papaya\Administration\Protocol\ProtocolPage;
  use Papaya\Administration\UI;
  use Papaya\Content\Configuration as SettingValues;
  use Papaya\Iterator\ArrayMapper;
  use Papaya\Iterator\Filter\Callback as CallbackFilterIterator;
  use Papaya\UI\ListView;
  use Papaya\UI\Toolbar;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class SettingsNavigation extends AdministrationPagePart {

    /**
     * @var ListView
     */
    private $_listView;
    /**
     * @var SettingValues
     */
    private $_values;
    /**
     * @var SettingGroups
     */
    private $_groups;


    public function appendTo(XMLElement $parent) {
      $parent->append($this->listView());
    }

    /**
     * @param ListView $listView
     * @return ListView
     */
    public function listView(ListView $listView = NULL) {
      if (NULL !== $listView) {
        $this->_listView = $listView;
      } elseif (NULL === $this->_listView) {
        $this->_listView = new ListView();
        $this->_listView->caption = new TranslatedText('Settings');
        $groups = $this->groups();
        $currentSetting = $this->parameters()->get(SettingsPage::PARAMETER_SETTING, '');
        if ($currentSetting !== '') {
          $currentGroup = $groups->getGroupOfSetting($currentSetting);
        } else {
          $currentGroup = $this->parameters()->get(SettingsPage::PARAMETER_SETTINGS_GROUP, -1);
        }
        $unknownOptions = new CallbackFilterIterator(
          new ArrayMapper(
            $this->values(), 'name'
          ),
          static function ($setting) use ($groups) {
            return $groups->getGroupOfSetting($setting) === SettingGroups::UNKNOWN;
          }
        );
        $this->_listView->builder(
          $builder = new ListView\Items\Builder($groups->getTranslatedLabels())
        );
        $builder->callbacks()->onCreateItem = function(
          $context, $items, $label, $group
        ) use (
          $groups, $currentGroup, $currentSetting, $unknownOptions
        ) {
          $isUnknownGroup = $group === SettingGroups::UNKNOWN;
          if ($isUnknownGroup && iterator_count($unknownOptions) === 0) {
            return;
          }
          $isSelectedGroup = $currentGroup === $group;
          $items[] = new ListView\Item(
            $isSelectedGroup ? 'status.folder-open' : 'items.folder',
            $label,
            [
              $this->parameterGroup() => [
                SettingsPage::PARAMETER_COMMAND => SettingsPage::COMMAND_EDIT,
                SettingsPage::PARAMETER_SETTINGS_GROUP => $group,
                SettingsPage::PARAMETER_SETTING => ''
              ]
            ]
          );
          if ($isSelectedGroup) {
            $settings = $isUnknownGroup ? $unknownOptions : $groups->getSettingsInGroup($group);
            foreach ($settings as $setting) {
              $items[] = $item = new ListView\Item(
                'items.option',
                $setting,
                [
                  $this->parameterGroup() => [
                    SettingsPage::PARAMETER_COMMAND => SettingsPage::COMMAND_EDIT,
                    SettingsPage::PARAMETER_SETTINGS_GROUP => $group,
                    SettingsPage::PARAMETER_SETTING => $setting
                  ]
                ],
                $setting === $currentSetting
              );
              $item->indentation = 1;
            }
          }
        };
      }
      return $this->_listView;
    }

    protected function _initializeToolbar(Toolbar\Collection $toolbar) {
      parent::_initializeToolbar($toolbar);
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'categories.installer';
      $button->caption = new TranslatedText('Install/Upgrade');
      $button->reference = $this->papaya()->references->byString(UI::INSTALLER);
      $toolbar->elements[] = new Toolbar\Separator();
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'items.link';
      $button->caption = new TranslatedText('Link types');
      $button->reference = $this->papaya()->references->byString(UI::ADMINISTRATION_LINK_TYPES);
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'items.mime-group';
      $button->caption = new TranslatedText('Mime types');
      $button->reference = $this->papaya()->references->byString(UI::ADMINISTRATION_MIME_TYPES);
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'items.cronjob';
      $button->caption = new TranslatedText('Cronjobs');
      $button->reference = $this->papaya()->references->byString(UI::ADMINISTRATION_CRONJOBS);
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'items.junk';
      $button->caption = new TranslatedText('Spam filter');
      $button->reference = $this->papaya()->references->byString(UI::ADMINISTRATION_SPAM_FILTER);
      $toolbar->elements[] = new Toolbar\Separator();
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'actions.view-icons';
      $button->caption = new TranslatedText('View Icons');
      $button->reference = $this->papaya()->references->byString(UI::ADMINISTRATION_ICONS);
      $toolbar->elements[] = new Toolbar\Separator();
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'items.tree.search';
      $button->caption = new TranslatedText('Check paths');
      $button->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_COMMAND => SettingsPage::COMMAND_VALIDATE_PATHS
          ]
        ]
      );
      $toolbar->elements[] = new Toolbar\Separator();
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'actions.download';
      $button->caption = new TranslatedText('Export');
      $button->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_COMMAND => SettingsPage::COMMAND_EXPORT
          ]
        ]
      );
      $toolbar->elements[] = $button = new Toolbar\Button();
      $button->image = 'actions.upload';
      $button->caption = new TranslatedText('Import');
      $button->reference()->setParameters(
        [
          $this->parameterGroup() => [
            ProtocolPage::PARAMETER_NAME_COMMAND => SettingsPage::COMMAND_IMPORT
          ]
        ]
      );
    }

    public function values(SettingValues $values = NULL) {
      if (NULL !== $values) {
        $this->_values = $values;
      } elseif (NULL === $this->_values) {
        $this->_values = new SettingValues();
        $this->_values->papaya($this->papaya());
        $this->_values->activateLazyLoad();
      }
      return $this->_values;
    }

    public function groups(SettingGroups $groups = NULL) {
      if (NULL !== $groups) {
        $this->_groups = $groups;
      } elseif (NULL === $this->_groups) {
        $this->_groups = new SettingGroups();
        $this->_groups->papaya($this->papaya());
      }
      return $this->_groups;
    }
  }
}
