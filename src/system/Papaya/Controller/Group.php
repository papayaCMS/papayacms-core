<?php
/**
* Papaya controller group, groups a list of controllers to be handled like one.
*
* @copyright 2002-2014 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Controller
* @version $Id: Group.php 39426 2014-02-27 18:33:54Z weinert $
*/

/**
* Papaya controller group, groups a list of controllers to be handled like one.
*
* @package Papaya-Library
* @subpackage Controller
*/
class PapayaControllerGroup extends PapayaObjectList implements PapayaController {

  /**
   * Create an object list for PapayaController instances, add all arguments as
   * elements of that list.
   */
  public function __construct() {
    parent::__construct('PapayaController');
    foreach (func_get_args() as $controller) {
      parent::add($controller);
    }
  }

  /**
   * Execute the attached controllers one after another. If a controller returns
   * TRUE, the request was handled. If the result is an PapayaController, it is delegated
   * to this object, if the result is FALSE the controller could not (completly) handle the
   * request, so use the next one.
   *
   * @param PapayaApplication $application
   * @param PapayaRequest &$request
   * @param PapayaResponse &$response
   * @return bool|PapayaController
   */
  public function execute(
    PapayaApplication $application,
    PapayaRequest &$request,
    PapayaResponse &$response
  ) {
    foreach ($this as $controller) {
      $limit = 20;
      do {
        /** @var bool|PapayaController $controller */
        $controller = $controller->execute($application, $request, $response);
        if (TRUE === $controller) {
          return TRUE;
        } elseif (--$limit < 1) {
          break;
        } else {
          $application->setObject('request', $request, PapayaApplication::DUPLICATE_OVERWRITE);
          $application->setObject('response', $response, PapayaApplication::DUPLICATE_OVERWRITE);
        }
      } while ($controller instanceof PapayaController);
    }
    return FALSE;
  }
}