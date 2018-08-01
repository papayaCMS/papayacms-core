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

namespace Papaya\Administration\Plugin\Editor;

/**
 * An PluginEditor implementation that combines several other dialogs,
 * allowing to separate the fields.
 *
 * It adds a toolbar to switch between the dialogs.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Group extends \Papaya\Plugin\Editor {

  private $_editors = [];
  private $_toolbar;
  private $_indexParameterName;

  /**
   * Papaya\Administration\Plugin\Editor\PapayaAdministrationPluginEditorGroup constructor.
   *
   * @param \Papaya\Plugin\Editable\Data $data
   * @param string $indexParameterName
   */
  public function __construct(\Papaya\Plugin\Editable\Data $data, $indexParameterName = 'editor_index') {
    parent::__construct($data);
    $this->_indexParameterName = $indexParameterName;
  }

  /**
   * @param \Papaya\Plugin\Editor $editor
   * @param $buttonCaption
   * @param string $buttonImage
   */
  public function add(\Papaya\Plugin\Editor $editor, $buttonCaption, $buttonImage = '') {
    $this->_editors[] = [$editor, $buttonCaption, $buttonImage];
  }

  /**
   * @param \PapayaUiToolbar|NULL $toolbar
   * @return \PapayaUiToolbar
   */
  public function toolbar(\PapayaUiToolbar $toolbar = NULL) {
    if (NULL !== $toolbar) {
      $this->_toolbar = $toolbar;
    } elseif (NULL === $this->_toolbar) {
      $this->_toolbar = $toolbar = new \PapayaUiToolbar();
      $toolbar->papaya($this->papaya());
      $toolbar->elements[] = $buttons = new \PapayaUiToolbarSelectButtons(
        $this->_indexParameterName,
        new \Papaya\Iterator\Callback(
          $this->_editors,
          function ($data) {
            return array('caption' => $data[1], 'image' => $data[2]);
          }
        )
      );
      if (!$this->context()->isEmpty()) {
        $buttons->reference()->setParameters($this->context());
      }
    }
    return $this->_toolbar;
  }

  /**
   * @return \Papaya\Plugin\Editor
   */
  private function getCurrentEditor() {
    $editorIndex = $this->parameters()->get($this->_indexParameterName, 0);
    $editorIndex = isset($this->_editors[$editorIndex]) ? $editorIndex : 0;
    if (isset($this->_editors[$editorIndex])) {
      /** @var \Papaya\Plugin\Editor $editor */
      $editor = $this->_editors[$editorIndex][0];
      $editor->context()->set($this->_indexParameterName, $editorIndex);
      return $editor;
    }
    throw new \LogicException('Editor group contains no editors');
  }

  /**
   * Execute and append the dialog to to the administration interface DOM.
   *
   * @see \Papaya\Xml\Appendable::appendTo()
   * @param \Papaya\Xml\Element $parent
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $parent->append($this->toolbar());
    $parent->append($this->getCurrentEditor());
  }
}
