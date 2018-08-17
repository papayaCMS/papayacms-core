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
 * XSLT template engine, uses ext/xsl or ext/xslcache
 *
 * @property \Papaya\BaseObject\Options\Collection $parameters
 * @property \Papaya\BaseObject\Collection $loaders
 * @property \DOMDocument $values
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class XSLT extends \Papaya\Template\Engine {

  /**
   * Transformation result buffer
   *
   * @var string
   */
  private $_result = '';

  /**
   * Transformation xslt template file
   *
   * @var string
   */
  private $_templateFile = '';

  /**
   * Transformation xslt template string
   *
   * @var string
   */
  private $_template = '';

  /**
   * Allow to use ext/xslcache e.g. cached xslt bytecode
   *
   * @var boolean
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
   * @var \Papaya\XML\Errors
   */
  private $_errorHandler;

  /**
   * Set the template directly as string, not as file.
   *
   * @param $string
   */
  public function setTemplateString($string) {
    $this->_template = $string;
    $this->_templateFile = FALSE;
    $this->useCache(FALSE);
  }

  /**
   * Set the xsl file for the transformation, throw an exception it it is not readable
   *
   * @throws \InvalidArgumentException
   * @param string $fileName
   */
  public function setTemplateFile($fileName) {
    if (
      file_exists($fileName) &&
      is_file($fileName) &&
      is_readable($fileName)
    ) {
      $this->_templateFile = $fileName;
    } else {
      throw new \InvalidArgumentException(
        sprintf(
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
   * @param boolean|NULL $use
   * @return boolean
   */
  public function useCache($use = NULL) {
    if (NULL !== $use) {
      if ($use && class_exists('XsltCache', FALSE)) {
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
   * @param \XsltCache|\XsltProcessor $processor
   */
  public function setProcessor($processor) {
    \Papaya\Utility\Constraints::assertInstanceOf(array('XsltProcessor', 'XsltCache'), $processor);
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
        class_exists('XsltCache', FALSE)) {
        $this->_processor = new \XsltCache();
      } else {
        $this->_processor = new \XsltProcessor();
      }
      $this->_processor->registerPHPFunctions(
        array(__CLASS__.'::parseXML')
      );
    }
    return $this->_processor;
  }

  /**
   * Set libxml errors handler
   *
   * @param \Papaya\XML\Errors $errorHandler
   */
  public function setErrorHandler(\Papaya\XML\Errors $errorHandler) {
    $this->_errorHandler = $errorHandler;
  }

  /**
   * Set libxml errors handler
   *
   * @return \Papaya\XML\Errors
   */
  public function getErrorHandler() {
    if (NULL === $this->_errorHandler) {
      $this->_errorHandler = new \Papaya\XML\Errors();
    }
    return $this->_errorHandler;
  }

  /**
   * Load xsl file into processor
   *
   * @throws \Papaya\XML\Exception
   * @return TRUE
   */
  public function prepare() {
    $errors = $this->getErrorHandler();
    $errors->activate();
    if (!$this->_templateFile) {
      $this->useCache(FALSE);
    }
    $processor = $this->getProcessor();
    if ($processor instanceof \XsltCache) {
      $processor->importStylesheet($this->_templateFile, $this->_useCache);
    } elseif ($this->_templateFile) {
      $xslDom = new \DOMDocument('1.0', 'UTF-8');
      $xslDom->load($this->_templateFile);
      $processor->importStylesheet($xslDom);
      unset($xslDom);
    } else {
      $xslDom = new \DOMDocument('1.0', 'UTF-8');
      $xslDom->loadXML($this->_template);
      $processor->importStylesheet($xslDom);
      unset($xslDom);
    }
    $errors->emit();
    $errors->deactivate();
    return TRUE;
  }

  /**
   * Run template processing and set result.
   *
   * @return bool
   * @throws \Papaya\Xml\Exception
   */
  public function run() {
    $this->_result = '';
    $errors = $this->getErrorHandler();
    $errors->activate();
    foreach ($this->parameters as $name => $value) {
      if (
        FALSE !== strpos($value, '"') &&
        FALSE !== strpos($value, "'")
      ) {
        $value = str_replace("'", "\xE2\x80\x99", $value);
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
   * @return \Papaya\Xml\Document
   */
  public static function parseXML($xmlString) {
    $errors = new \Papaya\Xml\Errors();
    return $errors->encapsulate(
      function ($xmlString) {
        $document = new \Papaya\XML\Document();
        $document->loadXML($xmlString);
        return $document;
      },
      array($xmlString),
      FALSE
    );
  }
}
