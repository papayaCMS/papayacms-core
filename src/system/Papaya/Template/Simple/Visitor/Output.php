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

class PapayaTemplateSimpleVisitorOutput extends \PapayaTemplateSimpleVisitor {

  private $_buffer = '';

  private $_callbacks = NULL;

  public function clear() {
    $this->_buffer = '';
  }

  public function __toString() {
    return $this->_buffer;
  }

  public function callbacks(\PapayaTemplateSimpleVisitorOutputCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (NULL == $this->_callbacks) {
      $this->_callbacks = new \PapayaTemplateSimpleVisitorOutputCallbacks();
    }
    return $this->_callbacks;
  }

  public function visitNodeOutput(\PapayaTemplateSimpleAstNodeOutput $node) {
    $this->_buffer .= $node->text;
  }

  public function visitNodeValue(\PapayaTemplateSimpleAstNodeValue $node) {
    if ($value = $this->callbacks()->onGetValue($node->name)) {
      $this->_buffer .= (string)$value;
    } else {
      $this->_buffer .= (string)$node->default;
    }
  }

}
