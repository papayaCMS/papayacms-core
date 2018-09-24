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

use Papaya\Application;
use Papaya\BaseObject;
use Papaya\Controller;
use Papaya\Request;
use Papaya\Response;

/**
 * Papaya controller group, groups a list of controllers to be handled like one.
 *
 * @package Papaya-Library
 * @subpackage Controller
 */
class Group extends BaseObject\Collection implements Controller {
  /**
   * Create an object list for \Papaya\Controller instances, add all arguments as
   * elements of that list.
   */
  public function __construct() {
    parent::__construct(Controller::class);
    foreach (\func_get_args() as $controller) {
      parent::add($controller);
    }
  }

  /**
   * Execute the attached controllers one after another. If a controller returns
   * TRUE, the request was handled. If the result is an \Papaya\Controller, it is delegated
   * to this object, if the result is FALSE the controller could not (completely) handle the
   * request, so use the next one.
   *
   * @param Application $application
   * @param Request &$request
   * @param Response &$response
   *
   * @return bool|Controller
   */
  public function execute(
    /* @noinspection ReferencingObjectsInspection */
    Application $application,
    Request &$request,
    Response &$response
  ) {
    foreach ($this as $controller) {
      $limit = 20;
      do {
        /** @var bool|Controller $controller */
        $controller = $controller->execute($application, $request, $response);
        if (TRUE === $controller) {
          return TRUE;
        }
        if (--$limit < 1) {
          break;
        }
        $application->setObject('request', $request, Application::DUPLICATE_OVERWRITE);
        $application->setObject('response', $response, Application::DUPLICATE_OVERWRITE);
      } while ($controller instanceof Controller);
    }
    return FALSE;
  }
}
