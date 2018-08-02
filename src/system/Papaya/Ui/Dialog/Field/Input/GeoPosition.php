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

namespace Papaya\Ui\Dialog\Field\Input;
/**
 * A single line input for a geographic position
 *
 * @property string|\PapayaUiString $caption
 * @property string $name
 * @property string|\PapayaUiString $hint
 * @property string $defaultValue
 * @property boolean $mandatory
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class GeoPosition extends \Papaya\Ui\Dialog\Field\Input {

  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'geoposition';

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'name' => array('getName', 'setName'),
    'hint' => array('getHint', 'setHint'),
    'defaultValue' => array('getDefaultValue', 'setDefaultValue'),
    'mandatory' => array('getMandatory', 'setMandatory')
  );

  /**
   * Create field, set caption, name, defaultvalue and mandatory status
   *
   * @param string|\PapayaUiString $caption
   * @param string $name
   * @param integer|NULL $default
   * @param boolean $mandatory
   */
  public function __construct($caption, $name, $default = NULL, $mandatory = FALSE) {
    parent::__construct($caption, $name, 100, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new \Papaya\Filter\Geo\Position()
    );
  }

}
