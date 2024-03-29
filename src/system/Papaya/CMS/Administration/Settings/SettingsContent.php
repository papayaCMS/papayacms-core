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

namespace Papaya\CMS\Administration\Settings {

  use Papaya\CMS\Administration\Page\Part as AdministrationPagePart;
  use Papaya\CMS\Content\Configuration\Setting;
  use Papaya\Filter\NotEmpty;
  use Papaya\UI\Control\Command\Condition\Parameter as ParameterCommandCondition;
  use Papaya\UI\Control\Command\Controller as CommandsController;

  class SettingsContent extends AdministrationPagePart {

    /**
     * @var NULL|Setting
     */
    private $_setting;

    protected function _createCommands($name = SettingsPage::PARAMETER_COMMAND, $default = SettingsPage::COMMAND_EDIT) {
      $commands = new CommandsController($name, $default);
      $commands->owner($this);
      $commands[SettingsPage::COMMAND_EDIT] = $command = new Commands\EditSetting($this->setting());
      $command->condition(
        new ParameterCommandCondition(
          SettingsPage::PARAMETER_SETTING,
          new NotEmpty()
        )
      );
      $commands[SettingsPage::COMMAND_VALIDATE_PATHS] = new Commands\ValidatePaths();
      $commands[SettingsPage::COMMAND_EXPORT] = new Commands\ExportSettings();
      $commands[SettingsPage::COMMAND_IMPORT] = $importCommand = new Commands\ImportSettings();
      $commands[SettingsPage::COMMAND_IMPORT_CONFIRM] = $importCommand;
      return $commands;
    }

    public function setting(Setting $setting = NULL) {
      if (NULL !== $setting) {
        $this->_setting = $setting;
      } elseif (NULL === $this->_setting) {
        $this->_setting = new Setting();
        $this->_setting->papaya($this->papaya());
        if ($settingName = $this->parameters()->get(SettingsPage::PARAMETER_SETTING, '')) {
          $this->_setting->activateLazyLoad(['name' => $settingName]);
        }
      }
      return $this->_setting;
    }

  }
}
