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

class Output extends \Papaya\Template\Simple\Visitor {
  private $_buffer = '';

  private $_callbacks;

  public function clear() {
    $this->_buffer = '';
  }

  public function __toString() {
    return $this->_buffer;
  }

  public function callbacks(Output\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Output\Callbacks();
    }
    return $this->_callbacks;
  }

  public function visitNodeOutput(\Papaya\Template\Simple\AST\Node\Output $node) {
    $this->_buffer .= $node->text;
  }

  public function visitNodeValue(\Papaya\Template\Simple\AST\Node\Value $node) {
    if ($value = $this->callbacks()->onGetValue($node->name)) {
      $this->_buffer .= (string)$value;
    } else {
      $this->_buffer .= (string)$node->default;
    }
  }
}
