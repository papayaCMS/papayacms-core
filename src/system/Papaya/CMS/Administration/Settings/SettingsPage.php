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

  use Papaya\CMS\Administration\Page as AdministrationPage;
  use Papaya\CMS\Administration\PageParameters;

  class SettingsPage extends AdministrationPage {

    const PARAMETER_COMMAND = 'cmd';
    const COMMAND_VALIDATE_PATHS = 'validate-paths';
    const COMMAND_EDIT = 'edit';
    const COMMAND_EXPORT = 'export';
    const COMMAND_IMPORT = 'import';
    const COMMAND_IMPORT_CONFIRM = 'import-confirm';
    const PARAMETER_SETTINGS_GROUP = 'group';
    const PARAMETER_SETTING = 'setting';
    const PARAMETER_SETTING_VALUE = 'value';
    const PARAMETER_SEARCH_FOR = 'search-for';
    const PARAMETER_SEARCH_CLEAR = 'search-clear';
    const PARAMETER_IMPORT_FILE = 'settings-file';
    const PARAMETER_IMPORT_SETTING = 'import-settings';

    protected $_parameterGroup = 'settings';

    protected function createContent() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_CONTENT, '70%');
      return new SettingsContent($this);
    }

    protected function createNavigation() {
      $this->getTemplate()->parameters()->set(PageParameters::COLUMN_WIDTH_NAVIGATION, '30%');
      return new SettingsNavigation($this);
    }
  }
}
