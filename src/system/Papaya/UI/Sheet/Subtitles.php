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
namespace Papaya\UI\Sheet;

use Papaya\UI;
use Papaya\Utility;

/**
 * A list of subtitle elements for a sheet
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Subtitles extends UI\Control\Collection {
  /**
   * @var string
   */
  protected $_itemClass = Subtitle::class;

  /**
   * Papaya\UI\Sheet\Subtitles constructor.
   *
   * @param array|\Traversable|null $subtitles
   */
  public function __construct($subtitles = NULL) {
    if (NULL !== $subtitles) {
      Utility\Constraints::assertArrayOrTraversable($subtitles);
      /** @var array|\Traversable $subtitles */
      foreach ($subtitles as $subtitle) {
        if (\is_string($subtitle) || \method_exists($subtitle, '__toString')) {
          $this->addString($subtitle);
        } else {
          $this->add($subtitle);
        }
      }
    }
  }

  /**
   * @param $string
   * @return \Papaya\UI\Control\Collection
   */
  public function addString($string) {
    return $this->add(new Subtitle($string));
  }
}
