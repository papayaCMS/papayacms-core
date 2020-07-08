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
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Field\Input\IntegerNumber;
  use Papaya\UI\Text\Translated as TranslatedText;

  class IntegerSetting extends SettingProfile {

    /**
     * @var int
     */
    private $_minimum;
    /**
     * @var int
     */
    private $_maximum;

    public function __construct($minimum = NULL, $maximum = NULL) {
      $this->_minimum = $minimum;
      $this->_maximum = $maximum;
    }

    public function appendFieldTo(Dialog $dialog, $settingName) {
      $dialog->fields[] = new IntegerNumber(
        $settingName,
        SettingsPage::PARAMETER_SETTING_VALUE,
        $this->_minimum,
        $this->_maximum
      );
    }

    /**
     * @param mixed $value
     * @return TranslatedText|string
     */
    public function getDisplayString($value) {
      return $value;
    }
  }
}
