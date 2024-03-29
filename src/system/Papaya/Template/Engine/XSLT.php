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
use Papaya\Template\XSLT\Context;
use Papaya\Template\XSLT\Errors;
use Papaya\XML;

/**
 * XSLT template engine, uses ext/xsl or ext/xslcache
 *
 * @property BaseObject\Options\Collection $parameters
 * @property BaseObject\Collection $loaders
 * @property \DOMDocument $values
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class XSLT extends Template\Engine {


  /**
   * Transformation result buffer
   *
   * @var string
   */
  private $_result = '';
  /**
   * @var string
   */
  private $_templateString;

  /**
   * Transformation xslt template file
   *
   * @var string
   */
  private $_templateFile = '';

  /**
   * Transformation xslt template string
   *
   * @var string|\Papaya\XML\Document
   */
  private $_template = '';

  /**
   * Allow to use ext/xslcache e.g. cached xslt bytecode
   *
   * @var bool
   */
  private $_useCache = TRUE;

  /**
   * XSLT processor
   *
   * @var \XSLTCache|\XSLTProcessor
   */
  private $_processor;

  /**
   * Error handling wrapper for libxml/libxslt errors
   *
   * @var XML\Errors
   */
  private $_errorHandler;

  /**
   * Set the template directly as string, not as file.
   *
   * @param string $string
   */
  public function setTemplateString($string) {
    $this->_template = NULL;
    $this->_templateFile = NULL;
    $this->_templateString = $string;
    $this->useCache(FALSE);
  }

  /**
   * Set the template directly as string, not as file.
   *
   * @param \DOMDocument $document
   */
  public function setTemplateDocument(\DOMDocument $document) {
    $this->_template = $document;
    $this->_templateFile = NULL;
    $this->_templateString = NULL;
    $this->useCache(FALSE);
  }

  public function getTemplateDocument() {
    if (NULL === $this->_template) {
      $this->_template = new XML\Document();
      if (NULL !== $this->_templateString) {
        $this->_template->loadXML($this->_templateString);
      } elseif (NULL !== $this->_templateFile) {
        $this->_template->load($this->_templateFile);
      }
    }
    return $this->_template;
  }

  /**
   * Set the xsl file for the transformation, throw an exception it it is not readable
   *
   * @throws \InvalidArgumentException
   *
   * @param string $fileName
   */
  public function setTemplateFile($fileName) {
    if (
      \file_exists($fileName) &&
      \is_file($fileName) &&
      \is_readable($fileName)
    ) {
      $this->_template = NULL;
      $this->_templateString = NULL;
      $this->_templateFile = $fileName;
    } else {
      throw new \InvalidArgumentException(
        \sprintf(
          'File "%s" not found or not readable.', $fileName
        )
      );
    }
  }

  /**
   * Use the xslcache extension if possible or
   * enable/disable the caching if the processor is already created.
   *
   * The function will return TRUE if the cache will be used.
   *
   * @param bool|null $use
   *
   * @return bool
   */
  public function useCache($use = NULL) {
    if (NULL !== $use) {
      if ($use && \class_exists('XsltCache', FALSE)) {
        $this->_useCache = TRUE;
      } else {
        $this->_useCache = FALSE;
      }
    }
    if (($this->_useCache && $this->_processor instanceof \XsltProcessor) ||
      (!$this->_useCache && $this->_processor instanceof \XsltCache)) {
      $this->_processor = NULL;
    }
    return $this->_useCache;
  }

  /**
   * Set the xslt processor object
   *
   * @throws \InvalidArgumentException
   *
   * @param \XsltCache|\XsltProcessor $processor
   */
  public function setProcessor($processor) {
    \Papaya\Utility\Constraints::assertInstanceOf(['XsltProcessor', 'XsltCache'], $processor);
    $this->_processor = $processor;
  }

  /**
   * Get the xslt processor object
   *
   * @return \XsltCache|\XsltProcessor
   */
  public function getProcessor() {
    if (NULL === $this->_processor) {
      if ($this->_useCache &&
        \class_exists('XsltCache', FALSE)) {
        $this->_processor = new \XsltCache();
      } else {
        $this->_processor = new \XsltProcessor();
      }
      $functions = [
        __CLASS__.'::parseXML'
      ];
      if (class_exists('Carica\\XPathFunctions\\XSLTProcessor')) {
        Carica\XPathFunctions\ModuleLoader::register('xpath-functions');
        $functions[] = 'Carica\\XPathFunctions\\XSLTProcessor::handleFunctionCall';
      }
      $this->_processor->registerPHPFunctions($functions);
    }
    return $this->_processor;
  }

  /**
   * Set libxml errors handler
   *
   * @param XML\Errors $errorHandler
   */
  public function setErrorHandler(XML\Errors $errorHandler) {
    $this->_errorHandler = $errorHandler;
  }

  /**
   * Set libxml errors handler
   *
   * @return XML\Errors
   */
  public function getErrorHandler() {
    if (NULL === $this->_errorHandler) {
      $this->_errorHandler = new XML\Errors();
    }
    return $this->_errorHandler;
  }

  /**
   * Load xsl file into processor
   *
   * @throws \Papaya\XML\Exception
   *
   * @return true
   */
  public function prepare() {
    $errors = $this->getErrorHandler();
    $errors->activate();
    if (!$this->_templateFile || $this->_template instanceof \DOMDocument) {
      $this->useCache(FALSE);
    }
    $processor = $this->getProcessor();
    if ($processor instanceof \XsltCache) {
      $processor->importStylesheet($this->_templateFile, $this->_useCache);
    } else {
      $processor->importStylesheet($this->getTemplateDocument());
    }
    $errors->emit();
    $errors->deactivate();
    return TRUE;
  }

  /**
   * Run template processing and set result.
   *
   * @return bool
   *
   * @throws \Papaya\Xml\Exception
   */
  public function run() {
    $this->_result = '';
    $errors = $this->getErrorHandler();
    $errors->activate();
    foreach ($this->parameters as $name => $value) {
      if (
        FALSE !== \strpos($value, '"') &&
        FALSE !== \strpos($value, "'")
      ) {
        $value = \str_replace("'", "\xE2\x80\x99", $value);
      }
      $this->_processor->setParameter('', $name, $value);
    }
    try {
      $result = $this->_processor->transformToXml(
        ($context = $this->getContext()) ? $context : $this->values
      );
      $errors->emit();
      $this->_result = $result;
      $errors->deactivate();
      return TRUE;
    } catch (\Exception $e) {
      $errors->emit();
      $errors->deactivate();
      return FALSE;
    }
  }

  /**
   * Get processing result
   *
   * @return string
   */
  public function getResult() {
    return $this->_result;
  }

  /**
   * callback for templates to parse a generated XML string
   *
   * @param string $xmlString
   *
   * @return XML\Document
   */
  public static function parseXML($xmlString) {
    $errors = new XML\Errors();
    return $errors->encapsulate(
      static function($xmlString) {
        $document = new XML\Document();
        $document->loadXML($xmlString);
        return $document;
      },
      [$xmlString],
      FALSE
    );
  }

  public static function handleFunctionCall(string $module, string $function, ...$arguments) {
    $call = self::getCallback($module, $function);
    return $call(...$arguments);
  }

  private static function getCallback(string $module, string $function): callable {
    $moduleName = isset(self::$_modules[$module]) ? $module : strtolower($module);
    if (!isset(self::$_modules[$moduleName])) {
      throw new BadMethodCallException("Invalid XSLT callback module: {$module}");
    }
    $callback = self::$_modules[$moduleName].'::'.$function;
    if (!is_callable($callback)) {
      throw new BadMethodCallException("Invalid XSLT callback function: {$module} -> {$function}");
    }
    return $callback;
  }
}
