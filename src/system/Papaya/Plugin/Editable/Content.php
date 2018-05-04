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
* An interface to define that an object has information (in an subobject)
* which conditions decide how and if it is cachable.
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaPluginEditableContent extends PapayaObjectParameters {

  /**
   * Checksum buffer filled in {@see PapayaPluginEditableContent::setXml()}
   * @var string|NULL
   */
  private $_checksum = NULL;

  /**
   * Editor for the content
   * @var PapayaPluginEditor
   */
  private $_editor = NULL;

  /**
   * Buffer for plugin content callbacks
   * @var PapayaObjectCallbacks
   */
  private $_callbacks = NULL;

  /**
   * Set serialized data from a string. The format is a simple xml.
   *
   * @param string $xml
   */
  public function setXml($xml) {
    $this->clear();
    $this->merge(PapayaUtilStringXml::unserializeArray($xml));
    $this->_checksum = $this->getChecksum();
  }

  /**
   * Get serialized data as a string. The format is a simple xml.
   *
   * @return string
   */
  public function getXml() {
    return PapayaUtilStringXml::serializeArray((array)$this);
  }

  /**
   * Check if the contained data was modified. The data is considered modified if it was not
   * set using {@see PapayaPluginEditableContent::setXml()} or the generated checksum is
   * different.
   *
   * @return boolean
   */
  public function modified() {
    if (isset($this->_checksum)) {
      return $this->_checksum != $this->getChecksum();
    }
    return TRUE;
  }

  /**
   * Getter/Seter for the editor subobject
   *
   * @param PapayaPluginEditor $editor
   * @throws LogicException
   * @return PapayaPluginEditor
   */
  public function editor(PapayaPluginEditor $editor = NULL) {
    if (isset($editor)) {
      $this->_editor = $editor;
    } elseif (NULL === $this->_editor) {
      if (isset($this->callbacks()->onCreateEditor)) {
        $this->_editor = $this->callbacks()->onCreateEditor($this);
        if (!($this->_editor instanceof \PapayaPluginEditor)) {
          throw new \LogicException(
            'Callback did not return a valid PapayaPluginEditor instance.'
          );
        }
      } else {
        $this->_editor = new \PapayaAdministrationPluginEditorDialog($this);
      }
    }
    return $this->_editor;
  }

  /**
   * Getter/Setter for the callbacks subobject
   *
   * @param PapayaPluginEditableContentCallbacks $callbacks
   * @return PapayaPluginEditableContentCallbacks
   */
  public function callbacks(PapayaPluginEditableContentCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new \PapayaPluginEditableContentCallbacks();
    }
    return $this->_callbacks;
  }
}
