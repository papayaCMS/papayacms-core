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

  use Papaya\Administration\Page as AdministrationPage;
  use Papaya\Administration\PageParameters;

  class SettingsPage extends AdministrationPage {

    const PARAMETER_COMMAND = 'cmd';
    const COMMAND_VALIDATE_PATHS = 'validate-paths';
    const COMMAND_EDIT = 'edit';
    const COMMAND_EXPORT = 'export';
    const COMMAND_IMPORT = 'import';
    const PARAMETER_SETTINGS_GROUP = 'group';
    const PARAMETER_SETTING = 'setting';
    const PARAMETER_SETTING_VALUE = 'value';

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
