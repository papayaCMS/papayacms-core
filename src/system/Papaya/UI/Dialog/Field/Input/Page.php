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

use Papaya\Filter;
use Papaya\UI;

/**
 * A single line input for a page id
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property string $name
 * @property string|\Papaya\UI\Text $hint
 * @property int|null $defaultValue
 * @property bool $mandatory
 */
class Page extends UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var bool
   */
  protected $_type = 'page';

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
   * Create field, set caption, name, defaultvalue and mandatory status
   *
   * @param string|\Papaya\UI\Text $caption
   * @param string $name
   * @param int|null $default
   * @param bool $mandatory
   */
  public function __construct($caption, $name, $default = NULL, $mandatory = FALSE) {
    parent::__construct($caption, $name, 20, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new Filter\IntegerValue(1)
    );
  }
}
