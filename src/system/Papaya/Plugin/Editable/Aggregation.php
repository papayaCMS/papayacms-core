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

/**
 * This a standard implementation for editable plugin content. It
 * makes implements \the PapayaPluginEditable interface and
 * expects an implementation of the abstract method "createEditor".
 *
 * The method needs to return a PapayaPluginEditor instance.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait PapayaPluginEditableAggregation {

  /**
   * @var PapayaPluginEditableContent
   */
  private $_content;

  /**
   * The content is an {@see ArrayObject} child class containing the stored data.
   *
   * @see \PapayaPluginEditable::content()
   * @param \PapayaPluginEditableContent $content
   * @return \PapayaPluginEditableContent
   */
  public function content(\PapayaPluginEditableContent $content = NULL) {
    if ($content !== NULL) {
      $this->_content = $content;
    } elseif (NULL === $this->_content) {
      $this->_content = new \PapayaPluginEditableContent();
      $this->_content->callbacks()->onCreateEditor = function($callbackContext, \PapayaPluginEditableContent $content) {
        return $this->createEditor($content);
      };
    }
    return $this->_content;
  }

  /**
   * @param \PapayaPluginEditableContent $content
   * @return \PapayaPluginEditor
   */
  abstract public function createEditor(\PapayaPluginEditableContent $content);
}
