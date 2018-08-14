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

namespace Papaya\Template\Engine;

/**
 * Papayas php implemented simple template engine.
 *
 * @property \Papaya\BaseObject\Options\Collection $parameters
 * @property \Papaya\BaseObject\Collection $loaders
 * @property \DOMDocument $values
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Simple extends \Papaya\Template\Engine {

  private $_template = '';
  private $_templateFile = FALSE;

  private $_ast = NULL;
  private $_visitor = NULL;

  /**
   * Prepare template engine if needed
   */
  public function prepare() {
    $this->visitor()->clear();
  }

  /**
   * Execute/run template engine
   */
  public function run() {
    $errors = new \Papaya\XML\Errors();
    $errors->activate();
    try {
      $this->ast()->accept($this->visitor());
    } catch (\Papaya\XML\Exception $e) {
    }
    $errors->deactivate();
  }

  /**
   * Get result of last run
   */
  public function getResult() {
    return (string)$this->visitor();
  }

  /**
   * Match a value name or xpath expresion agains the values document.
   * It will wrap it into a string typecast.
   *
   * This allows to use simple names like "foo.bar" in the template but xpath expressions, too.
   *
   * @param object $context
   * @param string $expression
   * @return mixed
   */
  public function callbackGetValue($context, $expression) {
    if (0 === strpos($expression, 'xpath(')) {
      $expression = 'string('.substr($expression, 6, -1).')';
    } else {
      $expression = 'string('.str_replace('.', '/', strtolower($expression)).')';
    }
    return $this->values()->xpath()->evaluate($expression, $this->getContext());
  }

  /**
   * Set a template file, loads the content of the file and stores the file name
   *
   * @param string $fileName
   * @throws \InvalidArgumentException
   */
  public function setTemplateFile($fileName) {
    if (file_exists($fileName) &&
      is_file($fileName) &&
      is_readable($fileName)) {
      $this->_template = file_get_contents($fileName);
      $this->_templateFile = $fileName;
      $this->_ast = NULL;
    } else {
      throw new \InvalidArgumentException(
        sprintf('File "%s" not found or not readable.', $fileName)
      );
    }
  }

  /**
   * Set a template string and set the template file to FALSE.
   *
   * @param string $string
   */
  public function setTemplateString($string) {
    $this->_template = $string;
    $this->_templateFile = FALSE;
    $this->_ast = NULL;
  }

  /**
   * Getter/Setter for the ast. The default ast is created using scanner and parser objects.
   *
   * @param \Papaya\Template\Simple\AST $ast
   * @return \Papaya\Template\Simple\AST
   */
  public function ast(\Papaya\Template\Simple\AST $ast = NULL) {
    if (isset($ast)) {
      $this->_ast = $ast;
    } elseif (NULL === $this->_ast) {
      $tokens = array();
      $scanner = new \Papaya\Template\Simple\Scanner(
        new \Papaya\Template\Simple\Scanner\Status\CSS()
      );
      $scanner->scan($tokens, $this->_template);
      $parser = new \Papaya\Template\Simple\Parser\Output($tokens);
      return $parser->parse();
    }
    return $this->_ast;
  }

  /**
   * Getter/Setter for the ast visitor used to execute the template.
   *
   * @param \Papaya\Template\Simple\Visitor $visitor
   * @return \Papaya\Template\Simple\Visitor
   */
  public function visitor(\Papaya\Template\Simple\Visitor $visitor = NULL) {
    if (isset($visitor)) {
      $this->_visitor = $visitor;
    } elseif (NULL === $this->_visitor) {
      $this->_visitor = new \Papaya\Template\Simple\Visitor\Output();
      $this->_visitor->callbacks()->onGetValue = array($this, 'callbackGetValue');
    }
    return $this->_visitor;
  }
}
