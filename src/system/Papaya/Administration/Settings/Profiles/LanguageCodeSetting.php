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
  use Papaya\Content\Languages;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Filter\Callback as CallbackFilter;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated as TranslatedText;

  class LanguageCodeSetting extends SettingProfile {

    const FILTER_NONE = Languages::FILTER_NONE;

    /**
     * Content languages filter
     */
    const FILTER_IS_CONTENT = Languages::FILTER_IS_CONTENT;

    /**
     * Interface languages filter
     */
    const FILTER_IS_INTERFACE = Languages::FILTER_IS_INTERFACE;

    /**
     * @var array
     */
    private $_list;
    /**
     * @var int
     */
    private $_filter;

    public function __construct($filter = self::FILTER_NONE) {
      $this->_filter = $filter;
    }

    public function appendFieldTo(Dialog $dialog, $settingName) {
      $dialog->fields[] = new Dialog\Field\Select(
        $settingName,
        SettingsPage::PARAMETER_SETTING_VALUE,
        $this->getList(),
        FALSE
      );
    }

    private function getList() {
      if (NULL === $this->_list) {
        $this->_list = iterator_to_array(
          new CallbackIterator(
            new CallbackFilter(
              $this->papaya()->languages,
              function ($language) {
                switch ($this->_filter) {
                case self::FILTER_IS_CONTENT:
                  return $language['is_content'];
                case self::FILTER_IS_INTERFACE:
                  return $language['is_interface'];
                }
                return TRUE;
              }
            ),
            static function($language, $key, $mode) {
              if ($mode === CallbackIterator::MODIFY_KEYS) {
                return $language['code'];
              }
              return  $language['title'];
            },
            CallbackIterator::MODIFY_BOTH
          )
        );
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

