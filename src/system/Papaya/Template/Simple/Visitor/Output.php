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
namespace Papaya\Template\Simple\Visitor;

use Papaya\Template\Simple;

class Output extends Simple\Visitor {
  /**
   * @var string
   */
  private $_buffer = '';

  /**
   * @var Output\Callbacks
   */
  private $_callbacks;

  public function clear() {
    $this->_buffer = '';
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->_buffer;
  }

  /**
   * @param Output\Callbacks|null $callbacks
   * @return Output\Callbacks
   */
  public function callbacks(Output\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Output\Callbacks();
    }
    return $this->_callbacks;
  }

  /**
   * @param Simple\AST\Node\Output $node
   */
  public function visitNodeOutput(Simple\AST\Node\Output $node) {
    $this->_buffer .= $node->text;
  }

  /**
   * @param Simple\AST\Node\Value $node
   */
  public function visitNodeValue(Simple\AST\Node\Value $node) {
    if ($value = $this->callbacks()->onGetValue($node->name)) {
      $this->_buffer .= $value;
    } else {
      $this->_buffer .= $node->default;
    }
  }
}
