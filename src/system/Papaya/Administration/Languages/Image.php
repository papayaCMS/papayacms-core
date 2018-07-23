<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Administration\Languages;
/**
 * Language image source administration control.
 *
 * Returns the image url for the language icon as string. If no language id is provided
 * it returns the icon for the currently selected administration content language
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Image extends \PapayaObject {

  private $_languageId;
  private $_language;
  private $_image;

  /**
   * Create language image for the current or a specified language
   *
   * @param integer $languageId
   */
  public function __construct($languageId = 0) {
    \PapayaUtilConstraints::assertInteger($languageId);
    $this->_languageId = $languageId;
  }

  /**
   * If the object is cast to string, fetch the language image and return the url
   *
   * @return string
   */
  public function __toString() {
    if (NULL === $this->_image) {
      $this->_image = '';
      if (NULL === $this->_language) {
        $this->_language = FALSE;
        if (isset($this->papaya()->administrationLanguage)) {
          if ($this->_languageId > 0) {
            $this->_language = $this
              ->papaya()
              ->administrationLanguage
              ->languages()
              ->getLanguage($this->_languageId);
          } else {
            $this->_language = $this->papaya()->administrationLanguage->getCurrent();
          }
        }
      }
      if ($this->_language) {
        $this->_image = './pics/language/'.$this->_language['image'];
      }
    }
    return (string)$this->_image;
  }
}
