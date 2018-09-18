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

namespace Papaya\Message;

/**
 * Papaya Message Hook, interface for hooks that that capture php events
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
interface Hook {
  /**
   * Activate message hook, make it capture the php events
   */
  public function activate();

  /**
   * Dectivate message hook, restoring default behavour
   */
  public function deactivate();
}
