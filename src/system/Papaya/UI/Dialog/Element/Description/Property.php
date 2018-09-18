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

namespace Papaya\UI\Dialog\Element\Description;

/**
 * Dialog element description item encapsulating a named property.
 *
 * @property string $name
 * @property string $value
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Property extends Item {
  protected $_name = '';

  protected $_value = '';

  protected $_declaredProperties = [
    'name' => ['_name', 'setName'],
    'value' => ['_value', '_value']
  ];

  /**
   * Create object, and store name and value data
   *
   * @param string $name
   * @param string $value
   */
  public function __construct($name, $value) {
    $this->setName($name);
    $this->_value = $value;
  }

  /**
   * Name can not be empty - not a very strong validation, but should be enough for the most cases.
   *
   * @param string $name
   */
  public function setName($name) {
    \Papaya\Utility\Constraints::assertNotEmpty($name);
    $this->_name = $name;
  }

  /**
   * Append description element with href attribute to parent xml element.
   *
   * @param \Papaya\XML\Element $parent
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    return $parent->appendElement(
      'property',
      [
        'name' => (string)$this->_name,
        'value' => (string)$this->_value
      ]
    );
  }
}
