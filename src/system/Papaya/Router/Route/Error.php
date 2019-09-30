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
namespace Papaya\Router\Route {

  use Papaya\Response;
  use Papaya\Router;

  /**
   * Return error document
   */
  class Error implements Router\Route {
    /**
     * HTTP response status
     *
     * @var int
     */
    private $_status;

    /**
     * Error message
     *
     * @var string
     */
    private $_errorMessage;

    /**
     * Error identifier
     *
     * @var string|null
     */
    private $_errorIdentifier;

    /**
     * @param string $message
     * @param int $status
     * @param null|int|string $identifier
     */
    public function __construct($message, $status, $identifier = NULL) {
      $this->_status = (int)$status;
      $this->_errorMessage = $message;
      $this->_errorIdentifier = $identifier;
    }

    /**
     * @param Router $router
     * @param NULL|object $context
     * @return null|Response
     */
    public function __invoke(Router $router, $context = NULL) {
      return new Response\Failure($this->_errorMessage, $this->_errorIdentifier, $this->_status);
    }
  }
}
