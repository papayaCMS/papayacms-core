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
namespace Papaya\UI;

/**
 * A ui control for an icon, the icon can add itself to the output using a <glyph> element.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string $image
 * @property string|\Papaya\UI\Text $title
 * @property string|\Papaya\UI\Text $hint
 * @property bool $visible
 * @property array $actionParameters
 * @property \Papaya\UI\Reference $reference
 */
class Icon extends Control {
  /**
   * internal reference object buffer
   *
   * @var \Papaya\UI\Reference|null
   */
  protected $_reference;

  /**
   * image index or url
   *
   * @var string
   */
  protected $_image = '';

  /**
   * caption/alternative text for image
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_caption = '';

  /**
   * hint/quickinfo text for image
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_hint = '';

  /**
   * hide the icon/replace with empty element
   *
   * @var bool
   */
  protected $_visible = TRUE;

  /**
   * action parameters list, if provided the icon will be linked
   *
   * @var array
   */
  protected $_actionParameters;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'image' => ['_image', '_image'],
    'caption' => ['_caption', '_caption'],
    'visible' => ['_visible', '_visible'],
    'hint' => ['_hint', '_hint'],
    'actionParameters' => ['_actionParameters', 'setActionParameters'],
    'reference' => ['reference', 'reference']
  ];

  /**
   * Create object and assign provided data
   */
  public function __construct($image, $caption = '', $hint = '', array $actionParameters = NULL) {
    $this->_image = $image;
    $this->_caption = $caption;
    $this->_hint = $hint;
    $this->_actionParameters = $actionParameters;
  }

  /**
   * If the object is castet to stirng, return the image source url.
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->getImageURL();
  }

  /**
   * append icon to output using a <glyph> element.
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    if ($this->_visible) {
      $glyph = $parent->appendElement(
        'glyph',
        [
          'src' => $this->getImageURL(),
          'caption' => (string)$this->_caption
        ]
      );
      $hint = (string)$this->_hint;
      if (!empty($hint)) {
        $glyph->setAttribute('hint', $hint);
      }
      $url = $this->getURL();
      if (!empty($url)) {
        $glyph->setAttribute('href', $url);
      }
    } else {
      $glyph = $parent->appendElement(
        'glyph',
        [
          'src' => '-',
          'caption' => ''
        ]
      );
    }
    return $glyph;
  }

  /**
   * Use the global images object, to determine the image source
   *
   * @return string
   */
  public function getImageURL() {
    return $this->papaya()->images[(string)$this->_image];
  }

  /**
   * If action parameters were provided, return the reference for a link containing these
   * parameters in the query string
   *
   * @return \Papaya\UI\Reference|null
   */
  public function getURL() {
    if (empty($this->_actionParameters)) {
      return;
    } else {
      $reference = clone $this->reference();
      $reference->setParameters($this->_actionParameters);
      return $reference->getRelative();
    }
  }

  /**
   * Getter/Setter for a reference subobject used to create hyperlinks.
   *
   * @param \Papaya\UI\Reference $reference
   *
   * @return \Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (\is_null($this->_reference)) {
      $this->_reference = new \Papaya\UI\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}
