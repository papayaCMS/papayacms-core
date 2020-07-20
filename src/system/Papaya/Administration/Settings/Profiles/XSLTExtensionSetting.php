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
  use Papaya\Iterator\TraversableIterator;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;

  class XSLTExtensionSetting extends SettingProfile {

    /**
     * @var array
     */
    private $_list;


    public function appendFieldTo(Dialog $dialog, $settingName) {
      $dialog->fields[] = new Dialog\Field\Select(
        $settingName,
        SettingsPage::PARAMETER_SETTING_VALUE,
        $this->getList(),
        FALSE
      );
      return TRUE;
    }

    private function getList() {
      if (NULL === $this->_list) {
        $this->_list = [
          '' => new TranslatedText('Automatic')
        ];
        $extensions = ['xsl', 'xslcache'];
        foreach ($extensions as $extension) {
          if (extension_loaded($extension)) {
            $this->_list[$extension] = $extension;
          }
        }
      }
      return $this->_list;
    }

    /**
     * @param mixed $value
     * @return TranslatedText|string
     */
    public function getDisplayString($value) {
      $list = $this->getList();
      return isset($list[$value]) ? $list[$value] : $value;
    }
  }
}

