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
namespace Papaya\CMS\Administration\UI\Navigation\Reference {

  use Papaya\CMS\Administration\UI;
  use Papaya\BaseObject\Interfaces\StringCastable;

  class LanguageIcon implements StringCastable {

    /**
     * @var string
     */
    private $_icon;

    public function __construct($icon) {
      $this->_icon = trim($icon) !== '' ? (string)basename($icon) : '';
    }

    public function __toString() {
      if ($this->_icon === '') {
        return '';
      }
       return UI::ICON_LANGUAGE.'/'.$this->_icon;
    }
  }
}



