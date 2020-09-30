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
namespace Papaya\Administration\UI\Navigation\Reference {

  use Papaya\Administration\UI;
  use Papaya\BaseObject\Interfaces\StringCastable;

  class MimeTypeIcon implements StringCastable {

    const DEFAULT_ICON = 'file-other';

    /**
     * @var string
     */
    private $_icon;
    /**
     * @var int
     */
    private $_size;

    public function __construct($icon, $size = 16) {
      $this->_icon = trim($icon) !== '' ? (string)$icon : self::DEFAULT_ICON;
      $this->_size = (int)$size;
    }

    public function __toString() {
       return UI::ICON_MIMETYPE.'.'.preg_replace('(\.(gif|png|svg)$)', '', $this->_icon).'?size='.$this->_size;
    }
  }
}


