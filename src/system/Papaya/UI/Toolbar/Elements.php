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
namespace Papaya\UI\Toolbar;

use Papaya\UI;
use Papaya\Utility;

/**
 * A list of menu elements, used for the $elements property of a {@see \Papaya\UI\Menu}
 *
 * @property bool $allowGroups
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Elements extends UI\Control\Collection {
  /**
   * Only {@see \Papaya\UI\Toolbar\Element} objects are allowed in this list
   *
   * @var string
   */
  protected $_itemClass = Element::class;

  /**
   * Allow group elements
   *
   * @var bool
   */
  protected $_allowGroups = TRUE;

  /**
   * Declare public properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'allowGroups' => ['_allowGroups', '_allowGroups']
  ];

  /**
   * Create object and set owner.
   *
   * @param UI\Control $owner
   */
  public function __construct(UI\Control $owner = NULL) {
    $this->owner($owner);
  }

  /**
   * Additionally to the standard validation, we block the groups in groups to avoid recursion.
   *
   * @throws \InvalidArgumentException
   *
   * @param UI\Control\Collection\Item|Element $item
   *
   * @return bool
   */
  protected function validateItemClass(UI\Control\Collection\Item $item) {
    Utility\Constraints::assertInstanceOf(Element::class, $item);
    parent::validateItemClass($item);
    if (
      !$this->_allowGroups &&
      $item instanceof Group
    ) {
      throw new \InvalidArgumentException(
        \sprintf(
          'InvalidArgumentException: Invalid item class "%s".',
          \get_class($item)
        )
      );
    }
    return TRUE;
  }
}
