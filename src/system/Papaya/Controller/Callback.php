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
namespace Papaya\Controller;

/**
 * Papaya controller callback encapsulate one function to be used as a controller
 *
 * This is for BC, to allow to more legacy source into a callback method.
 *
 * @package Papaya-Library
 * @subpackage Controller
 */
class Callback implements \Papaya\Controller {
  /**
   * @var callable
   */
  private $_callback;

  /**
   * Create an object list for \Papaya\Controller instances, add all arguments as
   * elements of that list.
   */
  public function __construct($callback) {
    \Papaya\Utility\Constraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * Execute the attached controllers one after another. If a controller returns
   * TRUE, the request was handled. If the result is an \Papaya\Controller, it is delegated
   * to this object, if the result is FALSE the controller could not (completely) handle the
   * request, so use the next one.
   *
   * @param \Papaya\Application $application
   * @param \Papaya\Request &$request
   * @param \Papaya\Response &$response
   *
   * @return bool|\Papaya\Controller
   */
  public function execute(
    \Papaya\Application $application,
    \Papaya\Request &$request,
    \Papaya\Response &$response
  ) {
    $callback = $this->_callback;
    return $callback($application, $request, $response);
  }
}
