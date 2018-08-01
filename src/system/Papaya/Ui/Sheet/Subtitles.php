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

/**
* A list of subtitle elements for a sheet
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiSheetSubtitles extends \Papaya\Ui\Control\Collection {

  protected $_itemClass = \PapayaUiSheetSubtitle::class;

  /**
   * PapayaUiSheetSubtitles constructor.
   *
   * @param array|\Traversable|NULL $subtitles
   */
  public function __construct($subtitles = NULL) {
    if (NULL !== $subtitles) {
      \Papaya\Utility\Constraints::assertArrayOrTraversable($subtitles);
      /** @var array|\Traversable $subtitles */
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
    return $this->add(new \PapayaUiSheetSubtitle($string));
  }
}
