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
namespace Papaya\UI\Dialog\Element;

use Papaya\UI;

/**
 * Superclass for dialog element description. In the most cases this is a separate page opened
 * directly or in an popup, so it needs a reference
 *
 * For simple text information the dialog fields use the "hint".
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Description extends UI\Control\Collection {
  /**
   * @var string
   */
  protected $_itemClass = Description\Item::class;

  /**
   * @var string
   */
  protected $_tagName = 'description';

  /**
   * @param $name
   * @param $value
   * @return Description\Property
   */
  public function addProperty($name, $value) {
    $this->add($property = new Description\Property($name, $value));
    return $property;
  }

  /**
   * @param UI\Reference|NULL $reference
   * @return Description\Link
   */
  public function addLink(UI\Reference $reference = NULL) {
    $this->add($link = new Description\Link());
    if (isset($reference)) {
      $link->reference($reference);
    }
    return $link;
  }
}
