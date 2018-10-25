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
namespace Papaya\Administration\UI\Route {

  use Papaya\Administration\UI;
  use Papaya\Administration\UI\Route;
  use Papaya\Response;

  /**
   * Execute the inner route if the current user has the permission
   */
  class Permission implements Route {
    /**
     * @var int
     */
    private $_permission;

    /**
     * @var Route|callable
     */
    private $_route;

    /**
     * @param int $permission
     * @param callable|Route $route
     */
    public function __construct($permission, callable $route) {
      $this->_permission = (int)$permission;
      $this->_route = $route;
    }

    /**
     * @param UI $ui
     * @param Address $path
     * @param int $level
     * @return null|Response
     */
    public function __invoke(UI $ui, Address $path, $level = 0) {
      if ($ui->papaya()->administrationUser->hasPerm($this->_permission)) {
        $route = $this->_route;
        return $route($ui, $path, $level);
      }
      return NULL;
    }
  }
}
