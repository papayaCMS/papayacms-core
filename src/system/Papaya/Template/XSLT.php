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

use Papaya\Utility;

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
   * @var Engine\XSLT
   */
  private $_engine;

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
    Utility\Constraints::assertNotEmpty($fileName);
    $this->_xslFile = (string)$fileName;
  }

  /**
   * Get xsl filename
   *
   * @return string
   */
  public function getXslFile() {
    return $this->_xslFile;
  }

  /**
   * Getter/Setter for the xslt template engine
   *
   * @param Engine\XSLT $engine
   *
   * @return Engine\XSLT
   */
  public function engine(Engine\XSLT $engine = NULL) {
    if (NULL !== $engine) {
      $this->_engine = $engine;
    } elseif (NULL === $this->_engine) {
      $preferred = $this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::XSLT_EXTENSION, 'xslcache');
      $this->_engine = $engine = new Engine\XSLT();
      $engine->useCache('xsl' !== $preferred);
    }
    return $this->_engine;
  }

  /**
   * Parse data
   *
   * @param int $options
   *
   * @return string|false parsed $result or message
   */
  public function parse($options = self::STRIP_XML_EMPTY_NAMESPACE) {
    $engine = $this->engine();
    $engine->setTemplateFile($this->getXslFile());
    $engine->parameters->assign($this->parameters());
    $engine->values($this->values()->document());
    return $this->clean(
      $this->errors()->encapsulate(
        [$this, 'process'], [$engine]
      ),
      $options
    );
  }

  /**
   * Start processing of the provided engine. This is a calback used
   * by parse() and should not be called directly.
   *
   * @param \Papaya\Template\Engine $engine
   *
   * @return mixed
   */
  public function process($engine) {
    if ($this->papaya()->options->get(\Papaya\CMS\CMSConfiguration::LOG_RUNTIME_TEMPLATE, FALSE)) {
      $timer = new \Papaya\Profiler\Timer();
      $timer->papaya($this->papaya());
    } else {
      $timer = NULL;
    }
    $engine->prepare();
    if (NULL !== $timer) {
      $timer->take('Prepared XSLT file "%s"', $this->getXslFile());
    }
    $engine->run();
    if (NULL !== $timer) {
      $timer->take('Processed XSLT file "%s"', $this->getXslFile());
      $timer->emit();
    }
    return $engine->getResult();
  }
}
