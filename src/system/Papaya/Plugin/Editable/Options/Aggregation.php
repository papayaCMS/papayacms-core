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
 * makes implements the PapayaPluginEditable interface and
 * expects an implementation of the abstract method "createEditor".
 *
 * The method needs to return a PapayaPluginEditor instance.
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
trait PapayaPluginEditableOptionsAggregation {

  /**
   * @var PapayaPluginEditableContent
   */
  private $_options;

  /**
   * The content is an {@see ArrayObject} child class containing the stored data.
   *
   * @see PapayaPluginAdaptable::options()
   * @param PapayaPluginEditableOptions $options
   * @return PapayaPluginEditableOptions
   */
  public function options(PapayaPluginEditableOptions $options = NULL) {
    if (NULL !== $options) {
      $this->_options = $options;
    } elseif (NULL === $this->_options) {
      $this->_options = new PapayaPluginEditableOptions(
        new PapayaPluginOptions($this->getPluginGuid())
      );
      $this->_options->callbacks()->onCreateEditor = function($context, PapayaPluginEditableOptions $content) {
        return $this->createOptionsEditor($content);
      };
    }
    return $this->_options;
  }

  /**
   * @param PapayaPluginEditableOptions $content
   * @return PapayaPluginEditor
   */
  abstract public function createOptionsEditor(PapayaPluginEditableOptions $content);

  /**
   * The plugin guid will be set as a public property by the plugin manager.
   *
   * @return string
   */
  public function getPluginGuid() {
    if (isset($this->guid)) {
      return $this->guid;
    }
    throw new \LogicException('No plugin guid found.');
  }
}
