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
namespace Papaya\UI\Dialog\Field\Input;

/**
 * A single line input for an identifier/name
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property string $name
 * @property string $hint
 * @property string|null $defaultValue
 * @property bool $mandatory
 */
class Identifier extends \Papaya\UI\Dialog\Field\Input {
  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['getCaption', 'setCaption'],
    'name' => ['getName', 'setName'],
    'hint' => ['getHint', 'setHint'],
    'defaultValue' => ['getDefaultValue', 'setDefaultValue'],
    'mandatory' => ['getMandatory', 'setMandatory']
  ];

  /**
   * Creates dialog field for color input with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param string $default optional, default ''
   * @param bool $mandatory optional, default FALSE
   */
  public function __construct($caption, $name, $default = '', $mandatory = FALSE) {
    parent::__construct($caption, $name, 100, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(new \Papaya\Filter\Identifier());
  }
}
