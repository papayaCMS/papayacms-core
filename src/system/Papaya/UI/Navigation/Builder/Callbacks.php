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
namespace Papaya\UI\Navigation\Builder;

/**
 * Callbacks that are used by the navigation builder object
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\BaseObject\Callback $onBeforeAppend
 * @property \Papaya\BaseObject\Callback $onAfterAppend
 * @property \Papaya\BaseObject\Callback $onCreateItem
 * @property \Papaya\BaseObject\Callback $onAfterAppendItem
 *
 * @method void onBeforeAppend(\Papaya\UI\Navigation\Items $items)
 * @method void onAfterAppend(\Papaya\UI\Navigation\Items $items)
 * @method null|\Papaya\UI\Navigation\Item onCreateItem($element, $index)
 * @method void onAfterAppendItem(\Papaya\UI\Navigation\Item $item, $element, $index)
 */
class Callbacks extends \Papaya\BaseObject\Callbacks {
  public function __construct() {
    parent::__construct(
      [
        'onBeforeAppend' => NULL,
        'onAfterAppend' => NULL,
        'onCreateItem' => NULL,
        'onAfterAppendItem' => NULL
      ]
    );
  }
}
