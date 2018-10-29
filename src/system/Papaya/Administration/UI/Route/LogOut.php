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

  use Papaya\Administration\UI\Route;

  class LogOut implements Route {
    /**
     * @param \Papaya\Administration\UI $ui
     * @param Address $path
     * @param int $level
     * @return null|\Papaya\Response
     */
    public function __invoke(\Papaya\Administration\UI $ui, Address $path, $level = 0) {
      $application = $ui->papaya();
      $locking = \papaya_locking::getInstance();
      $locking->removeLocks($application->session->id);
      $application->session->activate(FALSE);
      $application->session->destroy();
      return new \Papaya\Response\Redirect('./');
    }
  }
}