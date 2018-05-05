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
 * Papaya controller callback encapsulate one function to be used as a controller
 *
 * This is for BC, to allow to more legacy source into a callback method.
 *
 * @package Papaya-Library
 * @subpackage Controller
 */
class PapayaControllerCallback implements \PapayaController {

  /**
   * @var callable
   */
  private $_callback = NULL;

  /**
   * Create an object list for PapayaController instances, add all arguments as
   * elements of that list.
   */
  public function __construct($callback) {
    \PapayaUtilConstraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * Execute the attached controllers one after another. If a controller returns
   * TRUE, the request was handled. If the result is an PapayaController, it is delegated
   * to this object, if the result is FALSE the controller could not (completly) handle the
   * request, so use the next one.
   *
   * @param \Papaya\Application $application
   * @param \PapayaRequest &$request
   * @param \PapayaResponse &$response
   * @return bool|\PapayaController
   */
  public function execute(
    \Papaya\Application $application,
    \PapayaRequest &$request,
    \PapayaResponse &$response
  ) {
    $callback = $this->_callback;
    return $callback($application, $request, $response);
  }
}
