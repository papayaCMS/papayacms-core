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

namespace Papaya\UI\Toolbar;

/**
 * A menu/toolbar button with image and/or text.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\Reference $reference
 * @property string|\Papaya\UI\Text $caption
 * @property string|\Papaya\UI\Text $hint
 * @property bool $selected
 * @property string $accessKey
 * @property string $target
 * @property string $image
 */
class Button extends \Papaya\UI\Toolbar\Element {
  /**
   * Image or image index.  The button needs a cpation or/and an image.
   *
   * @var string
   */
  protected $_image = '';

  /**
   * Button caption. The button needs a caption or/and an image
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_caption = '';

  /**
   * Button quickinfo
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_hint = '';

  /**
   * The access key define the key for a browser shortcut. The real shortcut depends on
   * the browser
   *
   * @var string
   */
  protected $_accessKey = '';

  /**
   * If the button is selected/down
   *
   * @var bool
   */
  protected $_selected = FALSE;

  /**
   * Link target
   *
   * @var string
   */
  protected $_target = '_self';

  /**
   * Define public properties.
   *
   * @var array
   */
  protected $_declaredProperties = [
    'reference' => ['reference', 'reference'],
    'image' => ['_image', '_image'],
    'caption' => ['_caption', '_caption'],
    'hint' => ['_hint', '_hint'],
    'selected' => ['_selected', '_selected'],
    'accessKey' => ['_accessKey', 'setAccessKey'],
    'target' => ['_target', '_target']
  ];

  /**
   * Setter for access key character.
   *
   * @param string $key
   * @throws \InvalidArgumentException
   */
  public function setAccessKey($key) {
    \Papaya\Utility\Constraints::assertString($key);
    if (1 == \strlen($key)) {
      $this->_accessKey = $key;
    } else {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: Access key must be an single character.'
      );
    }
  }

  /**
   * Append button xml to menu. The button needs at least a caption or image to be shown.
   *
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $image = $this->papaya()->images[(string)$this->_image];
    $caption = (string)$this->_caption;
    if (!(empty($image) && empty($caption))) {
      $button = $parent->appendElement(
        'button',
        [
          'href' => $this->reference()->getRelative(),
          'target' => $this->_target
        ]
      );
      if (!empty($image)) {
        $button->setAttribute('glyph', $image);
      }
      if (!empty($caption)) {
        $button->setAttribute('title', $caption);
      }
      if (!empty($this->_accessKey)) {
        $button->setAttribute('accesskey', $this->_accessKey);
      }
      $hint = (string)$this->_hint;
      if (!empty($hint)) {
        $button->setAttribute('hint', $hint);
      }
      if ((bool)$this->_selected) {
        $button->setAttribute('down', 'down');
      }
    }
  }
}
