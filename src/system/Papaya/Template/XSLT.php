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

namespace Papaya\Template;
/**
 * Papaya Template, using the XSLT engine to generate the output.
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class XSLT extends \Papaya\Template {

  /**
   * @var string
   */
  private $_xslFile = '';

  /**
   * @var \Papaya\Template\Engine\XSLT
   */
  private $_engine = NULL;

  public function __construct($xslFile = '') {
    if (!empty($xslFile)) {
      $this->setXsl($xslFile);
    }
  }

  /**
   * Set xsl using a filename
   *
   * @param $fileName
   */
  public function setXsl($fileName) {
    \Papaya\Utility\Constraints::assertNotEmpty($fileName);
    $this->_xslFile = $fileName;
  }

  /**
   * Get xsl filename
   *
   * @return string
   */
  public function getXslFile() {
    return (string)$this->_xslFile;
  }

  /**
   * Getter/Setter for the xslt template engine
   *
   * @param \Papaya\Template\Engine\XSLT $engine
   * @return \Papaya\Template\Engine\XSLT
   */
  public function engine(\Papaya\Template\Engine\XSLT $engine = NULL) {
    if (isset($engine)) {
      $this->_engine = $engine;
    } elseif (NULL === $this->_engine) {
      $preferred = $this->papaya()->options->get('PAPAYA_XSLT_EXTENSION', 'xslcache');
      $this->_engine = $engine = new \Papaya\Template\Engine\XSLT();
      $engine->useCache($preferred != 'xsl');
    }
    return $this->_engine;
  }

  /**
   * Parse data
   *
   * @param int $options
   * @return string|FALSE parsed $result or message
   */
  public function parse($options = self::STRIP_XML_EMPTY_NAMESPACE) {
    $engine = $this->engine();
    $engine->setTemplateFile($this->getXslFile());
    $engine->parameters->assign($this->parameters());
    $engine->values($this->values()->document());
    return $this->clean(
      $this->errors()->encapsulate(
        array($this, 'process'), array($engine)
      ),
      $options
    );
  }

  /**
   * Start processing of the provided engine. This is a calback used
   * by parse() and should not be called directly.
   *
   * @param \Papaya\Template\Engine $engine
   * @return mixed
   */
  public function process($engine) {
    if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_TEMPLATE', FALSE)) {
      $timer = new \Papaya\Profiler\Timer();
      $timer->papaya($this->papaya());
    } else {
      $timer = NULL;
    }
    $engine->prepare();
    if (isset($timer)) {
      $timer->take('Prepared XSLT file "%s"', $this->getXslFile());
    }
    $engine->run();
    if (isset($timer)) {
      $timer->take('Processed XSLT file "%s"', $this->getXslFile());
      $timer->emit();
    }
    return $engine->getResult();
  }
}