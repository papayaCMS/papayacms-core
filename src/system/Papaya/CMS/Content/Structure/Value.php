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
namespace Papaya\CMS\Content\Structure;

/**
 * Content structure value element
 *
 * Content structure values are organized in groups and pages. A page can contain multiple groups
 * and a group multiple values.
 *
 * @package Papaya-Library
 * @subpackage Theme
 *
 * @property string $title
 * @property string $name
 * @property string $type
 * @property string $default
 * @property string $hint
 * @property string $fieldType
 * @property mixed $fieldParameters
 */
class Value extends Node {
  private $_group;

  /**
   * Create the object and store the group
   *
   * @param Group $group
   */
  public function __construct(Group $group) {
    parent::__construct(
      [
        'name' => 'value',
        'title' => '',
        'type' => 'text',
        'default' => '',
        'hint' => '',
        'fieldType' => '',
        'fieldParameters' => ''
      ]
    );
    $this->_group = $group;
  }

  /**
   * Return the identifier compiled from the group identifier and the value name
   *
   * @return string
   */
  public function getIdentifier() {
    return $this->_group->getIdentifier().'/'.$this->name;
  }

  public function getGroup(): Group {
    return $this->_group;
  }
}
