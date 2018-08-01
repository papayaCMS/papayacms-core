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
 * A list of menu elements, used for the $elements property of a {@see \PapayaUiMenu}
*
* @property boolean $allowGroups
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiToolbarElements extends \Papaya\Ui\Control\Collection {

  /**
  * Only {@see \PapayaUiToolbarElement} objects are allowed in this list
  *
  * @var string
  */
  protected $_itemClass = \PapayaUiToolbarElement::class;

  /**
  * Allow group elements
  *
  * @var boolean
  */
  protected $_allowGroups = TRUE;

  /**
  * Declare public properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'allowGroups' => array('_allowGroups', '_allowGroups')
  );

  /**
  * Create object and set owner.
  *
  * @param \Papaya\Ui\Control $owner
  */
  public function __construct(\Papaya\Ui\Control $owner = NULL) {
    $this->owner($owner);
  }

  /**
  * Additionally to the standard validation, we block the groups in groups to avoid recursion.
  *
  * @throws \InvalidArgumentException
  * @param \Papaya\Ui\Control\Collection\Item|\PapayaUiToolbarElement $item
  * @return bool
  */
  protected function validateItemClass(\Papaya\Ui\Control\Collection\Item $item) {
    \Papaya\Utility\Constraints::assertInstanceOf(\PapayaUiToolbarElement::class, $item);
    parent::validateItemClass($item);
    if (!$this->_allowGroups &&
        $item instanceof \PapayaUiToolbarGroup) {
      throw new \InvalidArgumentException(
        sprintf(
          'InvalidArgumentException: Invalid item class "%s".',
          get_class($item)
        )
      );
    }
    return TRUE;
  }
}
