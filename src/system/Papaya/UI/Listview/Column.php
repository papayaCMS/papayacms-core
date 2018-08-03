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

namespace Papaya\UI\Listview;
/**
 * A listview column represent one part of the column header in a {@see \Papaya\UI\Listview}.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property integer $align
 * @property string|\Papaya\UI\Text $caption
 */
class Column extends \Papaya\UI\Control\Collection\Item {

  /**
   * Current caption value
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_caption = '';

  /**
   * Current alignment value
   *
   * @var integer
   */
  protected $_align = \Papaya\UI\Option\Align::LEFT;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'caption' => array('_caption', '_caption')
  );

  /**
   * Initialize object and set standard values.
   *
   * @param string|\Papaya\UI\Text $caption
   * @param integer $align
   */
  public function __construct($caption, $align = \Papaya\UI\Option\Align::LEFT) {
    $this->_caption = $caption;
    $this->setAlign($align);
  }

  /**
   * Set the alignment if it is valid throw an exception if not.
   *
   * @throws \InvalidArgumentException
   * @param integer $align
   */
  public function setAlign($align) {
    \Papaya\UI\Option\Align::validate($align);
    $this->_align = $align;
  }

  /**
   * Read the current alignment
   *
   * @return integer
   */
  public function getAlign() {
    return $this->_align;
  }

  /**
   * Append column xml to parent node.
   *
   * @param \Papaya\Xml\Element $parent
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $parent->appendElement(
      'col',
      array(
        'align' => \Papaya\UI\Option\Align::getString($this->_align)
      ),
      (string)$this->_caption
    );
  }
}
