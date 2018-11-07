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
namespace Papaya\Administration\UI {

  interface Route {
    /**
     * @param \Papaya\Administration\Router $router
     * @param \Papaya\Administration\UI\Route\Address $address
     * @param int $level
     * @return null|true|\Papaya\Response|callable
     */
    public function __invoke(\Papaya\Administration\Router $router, Route\Address $address, $level = 0);
  }
}
