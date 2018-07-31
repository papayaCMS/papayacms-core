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

namespace Papaya\Plugin\Editable;

abstract class Data extends \Papaya\BaseObject\Parameters {

  /**
   * @var \Papaya\Plugin\Editor
   */
  private $_editor;

  /**
   * @var \Papaya\Plugin\Editable\Callbacks
   */
  private $_callbacks;

  /**
   * Getter/Setter for the editor object
   *
   * @param \Papaya\Plugin\Editor $editor
   * @throws \LogicException
   * @return \Papaya\Plugin\Editor
   */
  public function editor(\Papaya\Plugin\Editor $editor = NULL) {
    if (NULL !== $editor) {
      $this->_editor = $editor;
    } elseif (NULL === $this->_editor) {
      if (isset($this->callbacks()->onCreateEditor)) {
        $this->_editor = $this->callbacks()->onCreateEditor($this);
        if (!($this->_editor instanceof \Papaya\Plugin\Editor)) {
          throw new \LogicException(
            'Callback did not return a valid \PapayaPluginEditor instance.'
          );
        }
      } else {
        $this->_editor = new \Papaya\Administration\Plugin\Editor\Dialog($this);
      }
    }
    return $this->_editor;
  }

  /**
   * Getter/Setter for the callbacks subobject
   *
   * @param \Papaya\Plugin\Editable\Callbacks $callbacks
   * @return \Papaya\Plugin\Editable\Callbacks
   */
  public function callbacks(\Papaya\Plugin\Editable\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new \Papaya\Plugin\Editable\Callbacks();
    }
    return $this->_callbacks;
  }
}
