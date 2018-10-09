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
 * A simple listview subitem displaying text.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $align
 * @property string|\Papaya\UI\Text $text
 * @property array $actionParameters
 * @property UI\Reference $reference
 */
class Text extends Link {
  /**
   * buffer for text variable
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_text = '';

  /**
   * Basic reference/link
   *
   * @var UI\Reference
   */
  protected $_reference;

  /**
   * @var null
   */
  protected $_actionParameters;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'align' => ['getAlign', 'setAlign'],
    'text' => ['_text', '_text'],
    'actionParameters' => ['_actionParameters', 'setActionParameters'],
    'reference' => ['reference', 'reference']
  ];

  /**
   * Create subitem object, set text content and alignment.
   *
   * @param string|\Papaya\UI\Text $text
   * @param array $actionParameters
   */
  public function __construct($text, array $actionParameters = NULL) {
    parent::__construct($actionParameters);
    $this->_text = $text;
    $this->setActionParameters($actionParameters);
  }

  /**
   * Append subitem xml data to parent node.
   *
   * @param XML\Element $parent
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $subitem = $this->_appendSubItemTo($parent);
    if (!empty($this->_actionParameters)) {
      $subitem->appendElement('a', ['href' => $this->getURL()], (string)$this->_text);
    } else {
      $subitem->appendText((string)$this->_text);
    }
    return $subitem;
  }
}
