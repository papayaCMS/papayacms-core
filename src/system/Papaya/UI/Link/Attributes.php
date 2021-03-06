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
namespace Papaya\UI\Link;

use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * An control part that append link attributes like class, target and a popup configuration to
 * an parent xml element.
 *
 * @property string $class
 * @property string $target
 * @property bool $isPopup
 * @property string $popupWidth
 * @property string $popupHeight
 * @property string $popupTop
 * @property string $popupLeft
 * @property int $popupOptions
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Attributes extends UI\Control\Part {
  const OPTION_RESIZEABLE = 1;

  const OPTION_SCROLLBARS_AUTO = 2;

  const OPTION_SCROLLBARS_ALWAYS = 4;

  const OPTION_SCROLLBARS_NEVER = 8;

  const OPTION_TOOLBAR = 16;

  const OPTION_MENUBAR = 32;

  const OPTION_LOCATIONBAR = 64;

  const OPTION_STATUSBAR = 128;

  /**
   * @var string
   */
  protected $_class = '';

  /**
   * @var string
   */
  protected $_target = '_self';

  /**
   * @var bool
   */
  protected $_isPopup = FALSE;

  /**
   * @var string|int|null
   */
  protected $_popupWidth = '50%';

  /**
   * @var string|int|null
   */
  protected $_popupHeight = '50%';

  /**
   * @var string|int|null
   */
  protected $_popupTop;

  /**
   * @var string|int|null
   */
  protected $_popupLeft;

  /**
   * @var int
   */
  protected $_popupOptions = self::OPTION_SCROLLBARS_NEVER;

  private $_attributeNames = [
    'class' => 'class',
    'target' => 'target',
    'popup' => 'data-popup'
  ];

  protected $_declaredProperties = [
    'class' => ['_class', '_class'],
    'target' => ['_target', '_target'],
    'isPopup' => ['isPopup'],
    'popupWidth' => ['_popupWidth', '_popupWidth'],
    'popupHeight' => ['_popupHeight', '_popupHeight'],
    'popupTop' => ['_popupTop', '_popupTop'],
    'popupLeft' => ['_popupLeft', '_popupLeft'],
    'popupOptions' => ['_popupOptions', 'setPopupOptions']
  ];

  /**
   * Return true if the attribute contain a popup configuration
   *
   * @return bool
   */
  public function isPopup() {
    return $this->_isPopup;
  }

  /**
   * Remove the popup configuration. Keep the class and reset the target to "_self".
   */
  public function removePopup() {
    $this->_isPopup = FALSE;
    $this->_popupOptions = self::OPTION_SCROLLBARS_NEVER;
    $this->_target = '_self';
  }

  /**
   * Set the basic data for a popup
   *
   * @param string $target
   * @param string|int $width
   * @param string|int $height
   * @param string|int $top
   * @param string|int $left
   * @param int $options
   */
  public function setPopup($target, $width, $height, $top = NULL, $left = NULL, $options = NULL) {
    $this->_isPopup = TRUE;
    $this->_target = $target;
    $this->_popupWidth = $width;
    $this->_popupHeight = $height;
    $this->_popupLeft = $left;
    $this->_popupTop = $top;
    if (NULL !== $options) {
      $this->setPopupOptions($options);
    }
  }

  /**
   * Validate and set the popup options bitmask. This will throw an exception if
   * more then one scrollbars option is set.
   *
   * @param int $options
   *
   * @throws \InvalidArgumentException
   */
  public function setPopupOptions($options) {
    $counter = 0;
    $counter += Utility\Bitwise::inBitmask(self::OPTION_SCROLLBARS_AUTO, $options) ? 1 : 0;
    $counter += Utility\Bitwise::inBitmask(self::OPTION_SCROLLBARS_ALWAYS, $options) ? 1 : 0;
    $counter += Utility\Bitwise::inBitmask(self::OPTION_SCROLLBARS_NEVER, $options) ? 1 : 0;
    if ($counter > 1) {
      throw new \InvalidArgumentException(
        'Invalid options definition: only one scrollbars option can be set.'
      );
    }
    $this->_popupOptions = (int)$options;
  }

  /**
   * Return the popup options as an array. "appendTo()" will use this method to fetch the array and
   * serialize it to json for a data-* attribute.
   *
   * @return array
   */
  public function getPopupOptionsArray() {
    $data = [
      'width' => $this->_popupWidth,
      'height' => $this->_popupHeight
    ];
    if (NULL !== $this->_popupTop) {
      $data['top'] = $this->_popupTop;
    }
    if (NULL !== $this->_popupLeft) {
      $data['left'] = $this->_popupLeft;
    }
    $popupOptions = $this->popupOptions;
    $data['resizeable'] = Utility\Bitwise::inBitmask(self::OPTION_RESIZEABLE, $popupOptions);
    $data['toolBar'] = Utility\Bitwise::inBitmask(self::OPTION_TOOLBAR, $popupOptions);
    $data['menuBar'] = Utility\Bitwise::inBitmask(self::OPTION_MENUBAR, $popupOptions);
    $data['locationBar'] = Utility\Bitwise::inBitmask(self::OPTION_LOCATIONBAR, $popupOptions);
    $data['statusBar'] = Utility\Bitwise::inBitmask(self::OPTION_STATUSBAR, $popupOptions);
    if (Utility\Bitwise::inBitmask(self::OPTION_SCROLLBARS_ALWAYS, $popupOptions)) {
      $data['scrollBars'] = 'yes';
    } elseif (Utility\Bitwise::inBitmask(self::OPTION_SCROLLBARS_NEVER, $popupOptions)) {
      $data['scrollBars'] = 'no';
    } else {
      $data['scrollBars'] = 'auto';
    }
    return $data;
  }

  /**
   * The object append the link attributes to a given element.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $class = $this->class;
    if (!empty($class)) {
      $parent->setAttribute($this->_attributeNames['class'], $class);
    }
    $target = $this->target;
    if (!empty($target) && '_self' !== $target) {
      $parent->setAttribute($this->_attributeNames['target'], $target);
    }
    if ($this->isPopup()) {
      $parent->setAttribute(
        $this->_attributeNames['popup'], \json_encode($this->getPopupOptionsArray())
      );
    }
    return $parent;
  }
}
