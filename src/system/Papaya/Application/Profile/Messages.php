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

namespace Papaya\Application\Profile;

use Papaya\Application;
use Papaya\Message;
use Papaya\Plugin;

/**
 * Application object profile for the messages (manager) object
 *
 * @package Papaya-Library
 * @subpackage Application
 */
class Messages implements Application\Profile {
  /**
   * Create the profile object and return it
   *
   * @param Application|Application\CMS $application
   * @return Message\Manager
   */
  public function createObject($application) {
    $messages = new Message\Manager();
    $messages->addDispatcher(new Message\Dispatcher\Template());
    $messages->addDispatcher(new Message\Dispatcher\Database());
    $messages->addDispatcher(new Message\Dispatcher\Wildfire());
    $messages->addDispatcher(new Message\Dispatcher\XHTML());
    $plugins = $application->plugins;
    if (NULL !== $plugins) {
      foreach ($plugins->withType(Plugin\Types::LOGGER) as $plugin) {
        if (
          $plugin instanceof Plugin\LoggerFactory &&
          ($dispatcher = $plugin->createLogger())
        ) {
          $messages->addDispatcher($dispatcher);
        }
      }
    }
    return $messages;
  }
}
