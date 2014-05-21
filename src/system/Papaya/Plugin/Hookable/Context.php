<?php
/**
* An context for an hookable plugin.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Plugins
* @version $Id: Context.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* An context for an hookable plugin. Meaning that the current object provides context data
* to the plugin. Like itself or another object and data in an parameters object
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaPluginHookableContext {

  /**
   * @var NULL|object
   */
  private $_parent = NULL;
  /**
   * @var NULL|PapayaPluginEditableContent
   */
  private $_data = NULL;

  /**
   * Create the context with data
   *
   * @param object $parent
   * @param PapayaPluginEditableContent|array|Traversable|NULL $data
   */
  public function __construct($parent = NULL, $data = NULL) {
    if (isset($parent)) {
      PapayaUtilConstraints::assertObject($parent);
      $this->_parent = $parent;
    }
    if (isset($data)) {
      $this->data($data);
    }
  }

  /**
   * Check if an parent object was provided to the context.
   */
  public function hasParent() {
    return isset($this->_parent);
  }


  /**
   * Return the parent object if it was provided
   *
   */
  public function getParent() {
    if (NULL === $this->_parent) {
      throw new LogicException('No parent object was provided for this context.');
    }
    return $this->_parent;
  }

  /**
   * Getter/Setter for the context data. If a PapayaPluginEditableContent ist provided it will be
   * set a new context data, if an array or Traversalbe ist provided a new editable content
   * will be created an the data assigned.
   *
   * @param PapayaPluginEditableContent|array|Traversable|NULL $data
   * @return PapayaPluginEditableContent
   */
  public function data($data = NULL) {
    if (isset($data)) {
      if ($data instanceof PapayaPluginEditableContent) {
        $this->_data = $data;
      } else {
        $this->_data = new PapayaPluginEditableContent($data);
      }
    } elseif (NULL === $this->_data) {
      $this->_data = new PapayaPluginEditableContent();
    }
    return $this->_data;
  }
}
