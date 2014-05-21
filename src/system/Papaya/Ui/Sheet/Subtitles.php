<?php
/**
* A list of subtitle elements for a sheet
*
* @copyright 2014 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Subtitles.php 39820 2014-05-13 15:48:35Z weinert $
*/

/**
* A list of subtitle elements for a sheet
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiSheetSubtitles extends PapayaUiControlCollection {

  protected $_itemClass = 'PapayaUiSheetSubtitle';

  public function __construct($subtitles = NULL) {
    if (isset($subtitles)) {
      PapayaUtilConstraints::assertArrayOrTraversable($subtitles);
      foreach ($subtitles as $subtitle) {
        if (is_string($subtitle) || method_exists($subtitle, '__toString')) {
          $this->addString($subtitle);
        } else {
          $this->add($subtitle);
        }
      }
    }
  }

  public function addString($string) {
    return $this->add(new PapayaUiSheetSubtitle($string));
  }
}