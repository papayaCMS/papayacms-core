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
namespace Papaya\UI\Hierarchy;

use Papaya\UI;
use Papaya\XML;

/**
 * A hierarchy item represent one element in {@see \Papaya\UI\Hierarchy\Items}.
 *
 * @package Papaya-Library
 * @subpackage UI
 * @subpackage UI
 *
 * @property string $image
 * @property string|\Papaya\UI\Text $caption
 * @property string|\Papaya\UI\Text $hint
 * @property int $displayMode
 * @property UI\Reference $reference
 */
class Item extends UI\Control\Collection\Item {
  const DISPLAY_BOTH = 1;

  const DISPLAY_IMAGE_ONLY = 2;

  const DISPLAY_TEXT_ONLY = 3;

  /**
   * String representations for all available display modes. The list is used for
   * validation and xml generation.
   *
   * @var array(integer => string)
   */
  protected static $_displayModes = [
    self::DISPLAY_BOTH => 'both',
    self::DISPLAY_IMAGE_ONLY => 'image',
    self::DISPLAY_TEXT_ONLY => 'text',
  ];

  /**
   * Image index or url
   *
   * @var string
   */
  protected $_image = '';

  /**
   * Item caption/title
   *
   * @var string
   */
  protected $_caption = '';

  /**
   * Item hint
   *
   * @var string
   */
  protected $_hint = '';

  /**
   * Reference object
   *
   * @var null|UI\Reference
   */
  protected $_reference;

  /**
   * display mode - (both, image only, text only)
   *
   * @var int
   */
  protected $_displayMode = self::DISPLAY_BOTH;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['_caption', '_caption'],
    'hint' => ['_hint', '_hint'],
    'image' => ['_image', '_image'],
    'reference' => ['reference', 'reference'],
    'displayMode' => ['_displayMode', 'setDisplayMode']
  ];

  /**
   * Create object and set caption text
   *
   * @param string $caption
   */
  public function __construct($caption) {
    $this->_caption = $caption;
  }

  /**
   * Append item xml to parent xml element.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $itemNode = $parent->appendElement(
      'item',
      [
        'caption' => (string)$this->_caption,
        'hint' => (string)$this->_hint,
        'image' => $this->papaya()->images[(string)$this->_image] ?? (string)$this->_image,
        'mode' => self::$_displayModes[$this->_displayMode]
      ]
    );
    if (NULL !== $this->_reference) {
      $itemNode->setAttribute('href', $this->reference()->getRelative());
    }
    return $itemNode;
  }

  /**
   * Getter/Setter for the reference subobject
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new UI\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Read a cleaned display mode value
   *
   * @param int $mode
   *
   * @throws \OutOfBoundsException
   */
  public function setDisplayMode($mode) {
    if (\array_key_exists($mode, self::$_displayModes)) {
      $this->_displayMode = (int)$mode;
    } else {
      throw new \OutOfBoundsException(
        \sprintf('Invalid display mode for "%s".', __CLASS__)
      );
    }
  }
}
