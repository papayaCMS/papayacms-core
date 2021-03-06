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
namespace Papaya\UI\ListView\Item;

use Papaya\UI;
use Papaya\XML;

class Radio extends UI\ListView\Item {
  /**
   * @var string
   */
  private $_fieldName;

  /**
   * @var UI\Dialog
   */
  private $_dialog;

  /**
   * @var mixed
   */
  private $_value;

  /**
   * @var bool
   */
  private $_checked;

  /**
   * @param string $image
   * @param \Papaya\UI\Text|string $caption
   * @param UI\Dialog $dialog
   * @param string $fieldName
   * @param mixed $value
   */
  public function __construct($image, $caption, UI\Dialog $dialog, $fieldName, $value) {
    parent::__construct($image, $caption);
    $this->_dialog = $dialog;
    $this->_fieldName = $fieldName;
    $this->_value = $value;
  }

  /**
   * @param XML\Element $parent
   * @return XML\Element|void
   */
  public function appendTo(XML\Element $parent) {
    $node = parent::appendTo($parent);
    $input = $node->appendElement(
      'input',
      [
        'type' => 'radio',
        'name' => new UI\Dialog\Field\Parameter\Name($this->_fieldName, $this->_dialog),
        'value' => $this->_value
      ]
    );
    if ($this->isChecked()) {
      $input->setAttribute('checked', 'checked');
    }
  }

  public function isChecked() {
    if (NULL === $this->_checked) {
      $this->_checked = FALSE;
      if ($this->_dialog->parameters()->has($this->_fieldName)) {
        $this->_checked = $this->_dialog->parameters()->get($this->_fieldName, '') === (string)$this->_value;
      } elseif ($this->_dialog->data()->has($this->_fieldName)) {
        $this->_checked = $this->_dialog->data()->get($this->_fieldName, '') === (string)$this->_value;
      }
    }
    return $this->_checked;
  }
}
