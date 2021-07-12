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

use Papaya\BaseObject;

use Papaya\Plugin;

abstract class Data extends BaseObject\Parameters {
  /**
   * @var Plugin\Editor
   */
  private $_editor;

  /**
   * @var Callbacks
   */
  private $_callbacks;

  /**
   * Getter/Setter for the editor object
   *
   * @param Plugin\Editor $editor
   *
   * @throws \LogicException
   *
   * @return Plugin\Editor
   */
  public function editor(Plugin\Editor $editor = NULL) {
    if (NULL !== $editor) {
      $this->_editor = $editor;
    } elseif (NULL === $this->_editor) {
      if (isset($this->callbacks()->onCreateEditor)) {
        $this->_editor = $this->callbacks()->onCreateEditor($this);
        if (!($this->_editor instanceof Plugin\Editor)) {
          throw new \LogicException(
            \sprintf(
              'Callback did not return a valid %s instance.',
              Plugin\Editor::class
            )
          );
        }
      } else {
        throw new \LogicException(
          'No editor callback defined.'
        );
      }
    }
    return $this->_editor;
  }

  /**
   * Getter/Setter for the callbacks subobject
   *
   * @param Callbacks $callbacks
   *
   * @return Callbacks
   */
  public function callbacks(Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Callbacks();
    }
    return $this->_callbacks;
  }
}
