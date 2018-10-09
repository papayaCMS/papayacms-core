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
namespace Papaya\Message\Context {

  class Exception
    implements Interfaces\Items, Interfaces\Text, Interfaces\XHTML {
    /**
     * @var \Exception
     */
    private $_exception;

    /**
     * @var Backtrace
     */
    private $_backtrace;

    /**
     * Exception constructor.
     *
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception) {
      $this->_exception = $exception;
    }

    /**
     * @return \Exception
     */
    public function getException() {
      return $this->_exception;
    }

    /**
     * @return string
     */
    public function getLabel() {
      return 'Exception';
    }

    /**
     * @return \Papaya\Message\Context\Backtrace
     */
    public function getBacktraceContext() {
      if (NULL === $this->_backtrace) {
        $this->_backtrace = new Backtrace(0, $this->_exception->getTrace());
      }
      return $this->_backtrace;
    }

    /**
     * @return array
     */
    public function asArray() {
      return $this->getBacktraceContext()->asArray();
    }

    /**
     * @return string
     */
    public function asString() {
      return $this->getBacktraceContext()->asString();
    }

    /**
     * @return string
     */
    public function asXhtml() {
      return $this->getBacktraceContext()->asXhtml();
    }
  }
}
