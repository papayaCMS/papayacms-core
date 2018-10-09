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
namespace Papaya\UI\Dialog;

use Papaya\UI;

/**
 * A list of dialog elements
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Elements extends UI\Control\Collection {
  /**
   * Only \Papaya\UI\Dialog\Element objects are allows in this list
   *
   * @var string
   */
  protected $_itemClass = Element::class;

  /**
   * Initialize object an set owner dialog if available.
   *
   * @param UI\Dialog $dialog
   */
  public function __construct(UI\Dialog $dialog = NULL) {
    if (NULL !== $dialog) {
      $this->owner($dialog);
    }
  }

  /**
   * Collect data from elements (buttons/fields)
   */
  public function collect() {
    /** @var Element $item */
    foreach ($this->_items as $item) {
      $item->collect();
    }
  }
}
