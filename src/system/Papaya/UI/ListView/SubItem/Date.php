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
namespace Papaya\UI\ListView\SubItem;

use Papaya\UI;
use Papaya\XML;

/**
 * A simple listview subitem displaying date time.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $align
 * @property string|\Papaya\UI\Text $text
 * @property int $timestamp
 */
class Date extends UI\ListView\SubItem {
  const SHOW_DATE = UI\Text\Date::SHOW_DATE;

  const SHOW_TIME = UI\Text\Date::SHOW_TIME;

  const SHOW_SECONDS = UI\Text\Date::SHOW_SECONDS;

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
  protected $_declaredProperties = [
    'align' => ['getAlign', 'setAlign'],
    'timestamp' => ['_timestamp', '_timestamp'],
    'options' => ['_options', '_options']
  ];

  /**
   * Create subitem object, set text content and alignment.
   *
   * @param int $timestamp
   * @param int $options
   *
   * @throws \UnexpectedValueException
   */
  public function __construct($timestamp, $options = self::SHOW_TIME) {
    \Papaya\Utility\Constraints::assertInteger($options);
    $this->_timestamp = (int)$timestamp;
    $this->_options = $options;
  }

  /**
   * Append subitem xml data to parent node.
   *
   * @param XML\Element $parent
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $subitem = $this->_appendSubItemTo($parent);
    $subitem->appendText(
      (string)($this->_timestamp > 0 ? new UI\Text\Date($this->_timestamp, $this->_options) : '')
    );
    return $subitem;
  }
}
