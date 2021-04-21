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

use Papaya\BaseObject;
use Papaya\Template;
use Papaya\XML;

/**
 * Papayas php implemented simple template engine.
 *
 * @property BaseObject\Options\Collection $parameters
 * @property BaseObject\Collection $loaders
 * @property \DOMDocument $values
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Simple extends Template\Engine {
  private $_template = '';

  private $_templateFile = FALSE;

  private $_ast;

  private $_visitor;

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
    $errors = new XML\Errors();
    $errors->encapsulate(
      function() {
        $this->ast()->accept($this->visitor());
      },
      NULL,
      FALSE
    );
  }

  /**
   * Get result of last run
   */
  public function getResult() {
    return (string)$this->visitor();
  }

  /**
   * Set a template file, loads the content of the file and stores the file name
   *
   * @param string $fileName
   *
   * @throws \InvalidArgumentException
   */
  public function setTemplateFile($fileName) {
    if (\file_exists($fileName) &&
      \is_file($fileName) &&
      \is_readable($fileName)) {
      $this->_template = \file_get_contents($fileName);
      $this->_templateFile = $fileName;
      $this->_ast = NULL;
    } else {
      throw new \InvalidArgumentException(
        \sprintf('File "%s" not found or not readable.', $fileName)
      );
    }
  }

  public function getTemplate(): string {
    return $this->_template;
  }

  public function getTemplateFile(): string {
    return $this->_templateFile;
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
   * @param Template\Simple\AST $ast
   *
   * @return Template\Simple\AST
   * @throws \Papaya\Template\Simple\Exception
   */
  public function ast(Template\Simple\AST $ast = NULL) {
    if (NULL !== $ast) {
      $this->_ast = $ast;
    } elseif (NULL === $this->_ast) {
      $tokens = [];
      $scanner = new Template\Simple\Scanner(
        new Template\Simple\Scanner\Status\CSS()
      );
      $scanner->scan($tokens, $this->_template);
      $parser = new Template\Simple\Parser\Output($tokens);
      return $parser->parse();
    }
    return $this->_ast;
  }

  /**
   * Getter/Setter for the ast visitor used to execute the template.
   *
   * @param Template\Simple\Visitor $visitor
   *
   * @return Template\Simple\Visitor
   */
  public function visitor(Template\Simple\Visitor $visitor = NULL) {
    if (NULL !== $visitor) {
      $this->_visitor = $visitor;
    } elseif (NULL === $this->_visitor) {
      $this->_visitor = new Template\Simple\Visitor\Output();
      $this->_visitor->callbacks()->onGetValue = function(
        /** @noinspection PhpUnusedParameterInspection */
        $context, $expression
      ) {
        if (0 === \strpos($expression, 'xpath(')) {
          $expression = 'string('.\substr($expression, 6, -1).')';
        } else {
          $expression = 'string('.\str_replace('.', '/', \strtolower($expression)).')';
        }
        return $this->values()->xpath()->evaluate($expression, $this->getContext());
      };
    }
    return $this->_visitor;
  }
}
