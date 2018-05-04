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

/**
* Visitor to convert a variable into a xhtml formatted string dump
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageContextVariableVisitorXhtml
  extends PapayaMessageContextVariableVisitor {

  /**
  * Suffix for truncated string values
  * @var string
  */
  protected $_truncateSuffix = '...';

  /**
  * @var DOMDocument
  */
  private $_document = NULL;

  /**
  * @var DOMElement
  */
  private $_currentNode = NULL;

  /**
  * Handle a stack of nodes representing the indentation
  * @var array
  */
  private $_indentStack = array();

  /**
  * Construct visitor object and set recursion depth and string length
  *
  * @param integer $depth
  * @param integer $stringLength
  */
  public function __construct($depth, $stringLength) {
    parent::__construct($depth, $stringLength);
    $this->_document = new \DOMDocument('1.0', 'UTF-8');
    $this->_indentStack[] = $this->_currentNode = $this->_document->createElement('ul');
    $this->_currentNode->setAttribute('class', 'variableDump');
    $this->_document->appendChild(
      $this->_currentNode
    );
  }

  /**
  * return compiled string result
  *
  * @return string
  */
  public function get() {
    return $this->_document->saveXML($this->_currentNode, LIBXML_NOEMPTYTAG);
  }

  /**
  * Visit an array, and all its elements
  *
  * @param array $array
  */
  public function visitArray(array $array) {
    $elementCount = count($array);
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'array');
    $this->_addText($listNode, '(');
    $this->_addValueNode($listNode, 'number', $elementCount);
    $this->_addText($listNode, ') {');
    if ($elementCount > 0) {
      if ($this->_increaseIndent($listNode)) {
        foreach ($array as $index => $element) {
          $keyNode = $this->_createListNode();
          $this->_addText($keyNode, '[');
          $this->_addValueNode($keyNode, is_string($index) ? 'string' : 'number', $index);
          $this->_addText($keyNode, ']=>');
          $this->visitVariable($element);
        }
        $this->_decreaseIndent();
      } else {
        $this->_addValueNode($listNode, 'string', '...recursion limit...');
      }
    }
    $this->_addText($listNode, '}');
  }

  /**
   * Visit an boolean
   *
   * @param bool $boolean
   */
  public function visitBoolean($boolean) {
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'bool');
    $this->_addText($listNode, '(');
    $this->_addValueNode($listNode, 'boolean', $boolean ? 'true' : 'false');
    $this->_addText($listNode, ')');
  }

  /**
  * Visit an integer variable
  *
  * @param integer $integer
  */
  public function visitInteger($integer) {
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'int');
    $this->_addText($listNode, '(');
    $this->_addValueNode($listNode, 'number', (string)$integer);
    $this->_addText($listNode, ')');
  }

  /**
  * Visit a float variable
  *
  * @param float $float
  */
  public function visitFloat($float) {
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'float');
    $this->_addText($listNode, '(');
    $this->_addValueNode($listNode, 'number', (string)$float);
    $this->_addText($listNode, ')');
  }

  /**
  * Visit a NULL variable
  *
  * @param NULL $null
  */
  public function visitNull($null) {
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'null');
  }

  /**
  * Visit an object variable, handle recursions and duplicates
  *
  * @param object $object
  */
  public function visitObject($object) {
    $listNode = $this->_createListNode();
    $reflection = new \ReflectionObject($object);
    $hash = spl_object_hash($object);
    $isRecursion = $this->_isObjectRecursion($hash);
    $isDuplicate = $this->_isObjectDuplicate($hash);
    $this->_pushObjectStack($hash);
    $this->_addTypeNode($listNode, 'object');
    $this->_addText($listNode, '(');
    $this->_addValueNode($listNode, 'string', $reflection->getName());
    $this->_addText($listNode, ') #'.$this->_getObjectIndex($hash).' {');
    if ($isRecursion) {
      $this->_addValueNode($listNode, 'string', '...object recursion...');
    } elseif ($isDuplicate) {
      $this->_addValueNode($listNode, 'string', '...object duplication...');
    } elseif ($this->_increaseIndent($listNode)) {
      $values = array_merge((array)$reflection->getStaticProperties(), (array)$object);
      foreach ($reflection->getProperties() as $property) {
        $propertyName = $property->getName();
        $visibility = '';
        if ($property->isStatic()) {
          $visibility .= 'static:';
        }
        if ($property->isPrivate()) {
          $visibility .= 'private:';
        } elseif ($property->isProtected()) {
          $visibility .= 'protected:';
        } else {
          $visibility .= 'public:';
        }
        $keyNode = $this->_createListNode();
        $this->_addText($keyNode, '[');
        $this->_addValueNode($keyNode, 'string', $visibility.$propertyName);
        $this->_addText($keyNode, ']=>');
        if (array_key_exists($propertyName, $values)) {
          $this->visitVariable($values[$propertyName]);
        } elseif ($property->isProtected()) {
          $protectedName = "\0*\0".$propertyName;
          $this->visitVariable($values[$protectedName]);
        } elseif ($property->isPrivate()) {
          $privateName = "\0".$reflection->getName()."\0".$propertyName;
          $this->visitVariable($values[$privateName]);
        }
      }
      $this->_decreaseIndent();
    } else {
      $this->_addValueNode($listNode, 'string', '...recursion limit...');
    }
    $this->_popObjectStack($hash);
    $this->_addText($listNode, '}');
  }

  /**
  * Visit a resource
  *
  * @param resource $resource
  */
  public function visitResource($resource) {
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'resource');
    $this->_addText($listNode, '(#');
    $this->_addValueNode($listNode, 'number', (int)$resource);
    $this->_addText($listNode, ')');
  }

  /**
  * Visit a string variable
  *
  * @param string $string
  */
  public function visitString($string) {
    $length = strlen($string);
    if (strlen($string) > $this->_stringLength) {
      $value = substr($string, 0, $this->_stringLength).$this->_truncateSuffix;
    } else {
      $value = $string;
    }
    $listNode = $this->_createListNode();
    $this->_addTypeNode($listNode, 'string');
    $this->_addText($listNode, '(');
    $this->_addValueNode($listNode, 'number', $length);
    $this->_addText($listNode, ') "');
    $this->_addValueNode($listNode, 'string', $value);
    $this->_addText($listNode, '"');
  }

  /**
  * Create a list node and add it to the current node
  *
  * @return DOMElement
  */
  protected function _createListNode() {
    $listNode = $this->_document->createElement('li');
    $this->_currentNode->appendChild($listNode);
    return $listNode;
  }

  /**
  * Add a node describing the type of the variable
  *
  * @param DOMElement $targetNode append childnode to this parent
  * @param string $typeString
  * @return DOMElement new element
  */
  protected function _addTypeNode(DOMElement $targetNode, $typeString) {
    $typeNode = $this->_document->createElement('strong');
    $typeNode->appendChild($this->_document->createTextNode($typeString));
    $targetNode->appendChild($typeNode);
    return $typeNode;
  }

  /**
  * Add a node containing the value of the variable
  *
  * @param DOMElement $targetNode append childnode to this parent
  * @param string $valueClass type of value (number, string, boolean)
  * @param string $value string representation of the value
  * @return DOMElement new element
  */
  protected function _addValueNode(DOMElement $targetNode, $valueClass, $value) {
    $valueNode = $this->_document->createElement('em');
    $valueNode->setAttribute('class', $valueClass);
    $valueNode->appendChild($this->_document->createTextNode($value));
    $targetNode->appendChild($valueNode);
    return $valueNode;
  }

  /**
  * Add some text to the target node contents
  *
  * @param DOMElement $targetNode append childnode to this parent
  * @param string $text
  */
  protected function _addText(DOMElement $targetNode, $text) {
    $targetNode->appendChild($this->_document->createTextNode($text));
  }

  /**
  * Increase indent, add a new list to document, set parent node for list items
  *
  * @param DOMElement $targetNode parent/position of the new list
  * @return boolean return FALSE if identation limit is reached
  */
  protected function _increaseIndent(DOMElement $targetNode) {
    if (count($this->_indentStack) < ($this->_depth - 1)) {
      $this->_indentStack[] = $this->_document->createElement('ul');
      $this->_currentNode = end($this->_indentStack);
      $targetNode->appendChild($this->_currentNode);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Decrease indent, remove node from indent stack, set parent node for list items
  */
  protected function _decreaseIndent() {
    array_splice($this->_indentStack, -1);
    $this->_currentNode = end($this->_indentStack);
  }
}
