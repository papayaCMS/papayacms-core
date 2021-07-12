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

  use Papaya\Application\Access;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Button\Submit as SubmitButton;
  use Papaya\UI\Text\Translated as TranslatedText;

  abstract class SettingProfile implements Access {

    use Access\Aggregation;

    /**
     * @param Dialog $dialog
     * @param string $settingName
     * @return boolean - added editable field
     */
    abstract public function appendFieldTo(Dialog $dialog, $settingName);

    /**
     * @param mixed $value
     * @return TranslatedText|string
     */
    public function getDisplayString($value) {
      return $value;
    }
  }
}


