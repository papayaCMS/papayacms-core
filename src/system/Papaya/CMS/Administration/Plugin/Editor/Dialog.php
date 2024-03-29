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
namespace Papaya\CMS\Administration\Plugin\Editor;

use Papaya\CMS\Administration;
use Papaya\Plugin\Editable as EditablePlugin;
use Papaya\UI;
use Papaya\XML;

/**
 * An PluginEditor implementation that build a dialog based on an array of field definitions
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Dialog extends \Papaya\Plugin\Editor {
  private $_dialog;

  private $_onExecuteCallback;

  /**
   * Execute and append the dialog to to the administration interface DOM.
   *
   * @see \Papaya\XML\Appendable::appendTo()
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $context = $this->context();
    if (!$context->isEmpty()) {
      $this->dialog()->hiddenValues()->merge($context);
    }
    if ($this->dialog()->execute()) {
      if (NULL !== $this->_onExecuteCallback) {
        $callback = $this->_onExecuteCallback;
        $callback();
      } else {
        $this->getData()->assign($this->dialog()->data());
      }
    } elseif ($this->dialog()->isSubmitted()) {
      $this->papaya()->messages->displayError(
        'Invalid input. Please check the field(s) "%s".',
        [\implode(', ', $this->dialog()->errors()->getSourceCaptions())]
      );
    }
    $parent->append($this->dialog());
  }

  /**
   * Replace the default execution logic (assign data)
   *
   * @param callable $callback
   */
  public function onExecute(callable $callback) {
    $this->_onExecuteCallback = $callback;
  }

  /**
   * Getter/Setter for the dialog subobject.
   *
   * @param UI\Dialog $dialog
   *
   * @return UI\Dialog
   */
  public function dialog(UI\Dialog $dialog = NULL) {
    if (NULL !== $dialog) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $this->createDialog();
    }
    return $this->_dialog;
  }

  /**
   * Create a dialog instance and initialize it.
   *
   * @return UI\Dialog
   */
  protected function createDialog() {
    $dialog = new UI\Dialog();
    $dialog->papaya($this->papaya());

    if ($this->getData() instanceof EditablePlugin\Content) {
      $dialog->caption = new Administration\Languages\Caption(
        new UI\Text\Translated('Edit content')
      );
      $dialog->image = new Administration\Languages\Image();
      $dialog->parameterGroup('content');
    } elseif ($this->getData() instanceof EditablePlugin\Options) {
      $dialog->caption = new UI\Text\Translated('Edit options');
      $dialog->parameterGroup('options');
    } else {
      $dialog->caption = new UI\Text\Translated('Edit properties');
      $dialog->parameterGroup('properties');
    }
    $dialog->data()->assign($this->getData());

    $dialog->options->topButtons = TRUE;
    $dialog->buttons[] = new UI\Dialog\Button\Submit(new UI\Text\Translated('Save'));

    return $dialog;
  }
}
