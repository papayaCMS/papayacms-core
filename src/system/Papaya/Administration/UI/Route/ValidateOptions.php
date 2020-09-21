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

  use Papaya\Router;

  /**
   * Validate options an add warnings
   */
  class ValidateOptions implements Router\PathRoute {
    /**
     * @param Router $router
     * @param Router\Path $address
     * @param int $level
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      $application = $router->papaya();
      if (
        '' !== ($dataPath = $application->options->get(\Papaya\Configuration\CMS::PATH_DATA)) &&
        FALSE !== \strpos($dataPath, $_SERVER['DOCUMENT_ROOT']) &&
        \file_exists($dataPath) && (!\file_exists($dataPath.'.htaccess'))
      ) {
        $application->messages->displayWarning(
          'The file ".htaccess" in the directory "papaya-data/" '.
          'is missing or not accessible. Please secure the directory.'
        );
      }
      if (!$application->options->get(\Papaya\Configuration\CMS::PASSWORD_REHASH, FALSE)) {
        $application->messages->displayWarning(
          'The password rehashing is not active. Please activate PAPAYA_PASSWORD_REHASH.'.
          ' Make sure the authentication tables are up to date before activating'.
          ' this option, otherwise the logins can become locked.'
        );
      }
      return NULL;
    }
  }
}
