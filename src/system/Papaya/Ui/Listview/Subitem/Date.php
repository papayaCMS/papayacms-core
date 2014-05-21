<?php
/**
* A simple listview subitem displaying date time.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Date.php 39125 2014-02-06 16:17:14Z weinert $
*/

/**
* A simple listview subitem displaying date time.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property integer $align
* @property string|PapayaUiString $text
*/
class PapayaUiListviewSubitemDate extends PapayaUiListviewSubitem {

  const SHOW_DATE = PapayaUiStringDate::SHOW_DATE;
  const SHOW_TIME = PapayaUiStringDate::SHOW_TIME;
  const SHOW_SECONDS = PapayaUiStringDate::SHOW_SECONDS;

  /**
  * @var integer
  */
  protected $_timestamp = '';

  /**
  * @var integer
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
   * @param integer $timestamp
   * @param int $options
   * @internal param int $align
   */
  public function __construct($timestamp, $options = self::SHOW_TIME) {
    PapayaUtilConstraints::assertInteger($timestamp);
    PapayaUtilConstraints::assertInteger($options);
    $this->_timestamp = $timestamp;
    $this->_options = $options;
  }

  /**
  * Append subitem xml data to parent node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement(
      'subitem',
      array(
        'align' => PapayaUiOptionAlign::getString($this->getAlign())
      ),
      (string)(
        $this->_timestamp > 0 ? new PapayaUiStringDate($this->_timestamp, $this->_options) : ''
      )
    );
  }
}