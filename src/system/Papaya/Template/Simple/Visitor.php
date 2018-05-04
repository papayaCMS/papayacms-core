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
* Abstract superclass for papaya template simple ast visitors. This maps the
* node class names, to methods and calls them if the exists.
*
* @package PhpCss
* @subpackage Ast
*/
abstract class PapayaTemplateSimpleVisitor {

  abstract public function clear();

  abstract public function __toString();

  /**
  * Visit an ast object
  *
  * @param \PapayaTemplateSimpleAst $ast
  */
  public function visit(\PapayaTemplateSimpleAst $ast) {
    if ($method = $this->getMethodName($ast, 'visit')) {
      call_user_func(array($this, $method), $ast);
    }
  }

  /**
  * Visit an ast object
  *
  * @param \PapayaTemplateSimpleAst $ast
  */
  public function enter(\PapayaTemplateSimpleAst $ast) {
    if ($method = $this->getMethodName($ast, 'enter')) {
      call_user_func(array($this, $method), $ast);
    }
  }

  /**
  * Visit an ast object
  *
  * @param \PapayaTemplateSimpleAst $ast
  */
  public function leave(\PapayaTemplateSimpleAst $ast) {
    if ($method = $this->getMethodName($ast, 'leave')) {
      call_user_func(array($this, $method), $ast);
    }
  }

  /**
   * Map the ast node class to a method name. Validate if the method exists. Return the
   * method name if the method exists or FALSE if not.
   *
   * @param \PapayaTemplateSimpleAst $ast
   * @param string $prefix
   *
   * @return string|FALSE
   */
  private function getMethodName(\PapayaTemplateSimpleAst $ast, $prefix = 'visit') {
    $class = get_class($ast);
    if (0 === ($p = strpos($class, 'PapayaTemplateSimpleAst'))) {
      $method = $prefix.substr($class, strlen('PapayaTemplateSimpleAst'));
    } else {
      $method = $prefix.$class;
    }
    if (method_exists($this, $method)) {
      return $method;
    }
    return FALSE;
  }
}
