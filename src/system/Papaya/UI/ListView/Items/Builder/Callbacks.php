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

namespace Papaya\UI\ListView\Items\Builder;

/**
 * Callbacks that are used by the listview items builder
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\BaseObject\Callback $onBeforeFill
 * @property \Papaya\BaseObject\Callback $onAfterFill
 * @property \Papaya\BaseObject\Callback $onCreateItem
 * @method bool onBeforeFill(\Papaya\UI\ListView\Items $items) if the callback returns FALSE, the items will be cleared.
 * @method bool onAfterFill(\Papaya\UI\ListView\Items $items)
 * @method bool onCreateItem(\Papaya\UI\ListView\Items $items, mixed $element, int $index)
 */
class Callbacks extends \Papaya\BaseObject\Callbacks {
  /**
   * Initialize object and set callback definition
   */
  public function __construct() {
    parent::__construct(
      [
        'onBeforeFill' => FALSE,
        'onAfterFill' => NULL,
        'onCreateItem' => NULL
      ]
    );
  }
}
