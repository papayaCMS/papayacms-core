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

namespace Papaya\Ui\Listview\Subitem;
/**
 * A simple listview subitem displaying date time.
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property int $align
 * @property string|\PapayaUiString $text
 * @property int $timestamp
 */
class Date extends \Papaya\Ui\Listview\Subitem {

  const SHOW_DATE = \PapayaUiStringDate::SHOW_DATE;
  const SHOW_TIME = \PapayaUiStringDate::SHOW_TIME;
  const SHOW_SECONDS = \PapayaUiStringDate::SHOW_SECONDS;

  /**
   * @var int
   */
  protected $_timestamp = '';

  /**
   * @var int
   */
  protected $_options = '';

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'timestamp' => array('_timestamp', '_timestamp'),
    'options' => array('_options', '_options')
  );

  /**
   * Create subitem object, set text content and alignment.
   *
   * @param int $timestamp
   * @param int $options
   * @throws \UnexpectedValueException
   */
  public function __construct($timestamp, $options = self::SHOW_TIME) {
    \Papaya\Utility\Constraints::assertInteger($timestamp);
    \Papaya\Utility\Constraints::assertInteger($options);
    $this->_timestamp = $timestamp;
    $this->_options = $options;
  }

  /**
   * Append subitem xml data to parent node.
   *
   * @param \Papaya\Xml\Element $parent
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $parent->appendElement(
      'subitem',
      array(
        'align' => \Papaya\Ui\Option\Align::getString($this->getAlign())
      ),
      (string)(
      $this->_timestamp > 0 ? new \PapayaUiStringDate($this->_timestamp, $this->_options) : ''
      )
    );
  }
}
