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

namespace Papaya\Administration\Settings\Profiles {

  use Papaya\Administration\Settings\SettingProfile;
  use Papaya\Administration\Settings\SettingsPage;
  use Papaya\Filter\RegEx;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated as TranslatedText;

  class TextSetting extends SettingProfile {

    /**
     * @var int
     */
    private $_maximumLength;
    /**
     * @var null
     */
    private $_pattern;

    public function __construct($maximumLength = 0, $pattern = NULL) {
      $this->_maximumLength = $maximumLength;
      $this->_pattern = $pattern;
      return TRUE;
    }

    public function appendFieldTo(Dialog $dialog, $settingName) {
      $dialog->fields[] = new Dialog\Field\Input(
        $settingName,
        SettingsPage::PARAMETER_SETTING_VALUE,
        $this->_maximumLength,
        NULL,
        isset($this->_pattern) ? new RegEx($this->_pattern) : NULL
      );
    }
  }
}

