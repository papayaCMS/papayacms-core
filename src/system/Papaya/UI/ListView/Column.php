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
namespace Papaya\UI\ListView;

use Papaya\UI;
use Papaya\XML;

/**
 * A list view column represent one part of the column header in a {@see \Papaya\UI\ListView}.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $align
 * @property string|\Papaya\UI\Text $caption
 */
class Column extends UI\Control\Collection\Item {

  /**
   * Current caption value
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_caption = '';

  /**
   * Current alignment value
   *
   * @var int
   */
  protected $_align = UI\Option\Align::LEFT;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'align' => ['getAlign', 'setAlign'],
    'caption' => ['_caption', '_caption']
  ];

  /**
   * Initialize object and set standard values.
   *
   * @param string|\Papaya\UI\Text $caption
   * @param int $align
   */
  public function __construct($caption, $align = UI\Option\Align::LEFT) {
    $this->_caption = $caption;
    $this->setAlign($align);
  }

  /**
   * Set the alignment if it is valid throw an exception if not.
   *
   * @throws \InvalidArgumentException
   *
   * @param int $align
   */
  public function setAlign($align) {
    UI\Option\Align::validate($align);
    $this->_align = $align;
  }

  /**
   * Read the current alignment
   *
   * @return int
   */
  public function getAlign() {
    return $this->_align;
  }

  /**
   * Append column xml to parent node.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $parent->appendElement(
      'col',
      [
        'align' => UI\Option\Align::getString($this->_align)
      ],
      (string)$this->_caption
    );
  }
}
