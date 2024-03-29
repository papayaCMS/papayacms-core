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

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\XML;

/**
 * A ui control for an icon, the icon can add itself to the output using a <glyph> element.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string $image
 * @property string|\Papaya\UI\Text $title
 * @property string|\Papaya\UI\Text $hint
 * @property string|\Papaya\UI\Text $caption
 * @property bool $visible
 * @property array $actionParameters
 * @property Reference $reference
 */
class Icon extends Control {
  /**
   * internal reference object buffer
   *
   * @var Reference|null
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
   * @var string|StringCastable
   */
  protected $_caption = '';

  /**
   * hint/quickinfo text for image
   *
   * @var string|StringCastable
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
   *
   * @param string $image
   * @param string|StringCastable $caption
   * @param string|StringCastable $hint
   * @param array|null $actionParameters
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
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    if ($this->_visible) {
      $glyph = $parent->appendElement(
        'glyph',
        [
          'src' => $this->getImageURL(),
          'caption' => (string)$this->_caption
        ]
      );
      $hint = (string)$this->_hint;
      if ('' !== \trim($hint)) {
        $glyph->setAttribute('hint', $hint);
      }
      $url = $this->getURL();
      if ('' !== \trim($url)) {
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
   * @return string|null
   */
  public function getImageURL() {
    return $this->papaya()->images[(string)$this->_image] ?? null;
  }

  /**
   * If action parameters were provided, return the reference for a link containing these
   * parameters in the query string
   *
   * @return string|null
   */
  public function getURL() {
    if (empty($this->_actionParameters)) {
      return NULL;
    }
    $reference = clone $this->reference();
    $reference->setParameters($this->_actionParameters);
    return $reference->getRelative();
  }

  /**
   * Getter/Setter for a reference subobject used to create hyperlinks.
   *
   * @param Reference $reference
   *
   * @return Reference
   */
  public function reference(Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}
