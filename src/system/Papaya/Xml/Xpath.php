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
* Replacement for the DOMXpath without the (broken) automatic namespace registration if possible.
*
* @package Papaya-Library
* @subpackage Xml
*/
class PapayaXmlXpath extends DOMXpath {

  /**
   * @var boolean
   */
  private $_registerNodeNamespaces = FALSE;

  /**
   * Create object and diable the automatic namespace registration if possible.
   */
  public function __construct(DOMDocument $dom) {
    parent::__construct($dom);
    $this->registerNodeNamespaces(version_compare(PHP_VERSION, '<', '5.3.3'));
  }

  /**
   * @param string $prefix
   * @param string $namespaceUri
   * @return bool
   */
  public function registerNamespace($prefix, $namespaceUri) {
    $result = parent::registerNamespace($prefix, $namespaceUri);
    if ($result && $this->document instanceof \PapayaXmlDocument) {
      /** @noinspection PhpUndefinedMethodInspection */
      $this->document->registerNamespaces(
        array($prefix => $namespaceUri),
        FALSE
      );
    }
    return $result;
  }

  /**
   * Enable/Disable the automatic namespace registration, return the current status
   * @param boolean|NULL $enabled
   * @return boolean
   */
  public function registerNodeNamespaces($enabled = NULL) {
    if (isset($enabled)) {
      $this->_registerNodeNamespaces = (boolean)$enabled;
    }
    return $this->_registerNodeNamespaces;
  }

  /**
   * Evaluate an xpath expression an return the result
   *
   * @see DOMXPath::evaluate()
   * @param string $expression
   * @param DOMNode $contextNode
   * @param null|boolean $registerNodeNS
   * @return DOMNodelist|String|Float|Integer|Boolean|FALSE
   */
  public function evaluate($expression, DOMNode $contextNode = NULL, $registerNodeNS = NULL) {
    if ($registerNodeNS || (NULL === $registerNodeNS && $this->_registerNodeNamespaces)) {
      $result = isset($contextNode)
        ? parent::evaluate($expression, $contextNode)
        : parent::evaluate($expression);
    } else {
      $result = parent::evaluate($expression, $contextNode, FALSE);
    }
    if (is_float($result) && is_nan($result)) {
      return 0.0;
    } else {
      return $result;
    }
  }

  /**
   * Query should not be used, but evaluate. Block it.
   *
   * @deprecated
   * @see DOMXPath::query()
   * @param string $expression
   * @param DOMNode $contextnode
   * @param null|boolean $registerNodeNS
   * @throws LogicException
   * @return DOMNodelist
   */
  public function query($expression, DOMNode $contextnode = NULL, $registerNodeNS = NULL) {
    throw new \LogicException('"query()" should not be used, use "evaluate()".');
  }

}
