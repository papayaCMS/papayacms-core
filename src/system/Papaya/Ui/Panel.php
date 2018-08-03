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

namespace Papaya\Ui;
/**
 * Abstract superclass for controls inside a panel.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Panel extends Control {

  /**
   * Panel caption/title
   *
   * @var string
   */
  protected $_caption = '';

  /**
   * Panel caption/title
   *
   * @var \PapayaUiToolbars
   */
  protected $_toolbars = NULL;

  /**
   * Append panel to output xml
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element $panel
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $panel = $parent->appendElement('panel');
    if (!empty($this->_caption)) {
      $panel->setAttribute('title', (string)$this->_caption);
    }
    $this->toolbars()->appendTo($panel);
    return $panel;
  }

  /**
   * Set a caption for the panel
   *
   * @param \PapayaUiString|string $caption
   */
  public function setCaption($caption) {
    $this->_caption = $caption;
  }

  /**
   * Toolbars for the four corners of the panel
   *
   * @param \PapayaUiToolbars $toolbars
   * @return \PapayaUiToolbars
   */
  public function toolbars(\PapayaUiToolbars $toolbars = NULL) {
    if (NULL !== $toolbars) {
      $this->_toolbars = $toolbars;
    }
    if (NULL === $this->_toolbars) {
      $this->_toolbars = new \PapayaUiToolbars();
      $this->_toolbars->papaya($this->papaya());
    }
    return $this->_toolbars;
  }

}
