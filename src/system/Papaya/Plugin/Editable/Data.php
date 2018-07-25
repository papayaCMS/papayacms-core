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

use Papaya\Administration\Plugin\Editor\Dialog;

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

abstract class PapayaPluginEditableData extends PapayaObjectParameters {

  /**
   * @var \PapayaPluginEditor
   */
  private $_editor;

  /**
   * @var \PapayaPluginEditableCallbacks
   */
  private $_callbacks;

  /**
   * Getter/Setter for the editor object
   *
   * @param \PapayaPluginEditor $editor
   * @throws LogicException
   * @return \PapayaPluginEditor
   */
  public function editor(PapayaPluginEditor $editor = NULL) {
    if (NULL !== $editor) {
      $this->_editor = $editor;
    } elseif (NULL === $this->_editor) {
      if (isset($this->callbacks()->onCreateEditor)) {
        $this->_editor = $this->callbacks()->onCreateEditor($this);
        if (!($this->_editor instanceof PapayaPluginEditor)) {
          throw new LogicException(
            'Callback did not return a valid PapayaPluginEditor instance.'
          );
        }
      } else {
        $this->_editor = new Dialog($this);
      }
    }
    return $this->_editor;
  }

  /**
   * Getter/Setter for the callbacks subobject
   *
   * @param \PapayaPluginEditableCallbacks $callbacks
   * @return \PapayaPluginEditableCallbacks
   */
  public function callbacks(PapayaPluginEditableCallbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new PapayaPluginEditableCallbacks();
    }
    return $this->_callbacks;
  }
}
