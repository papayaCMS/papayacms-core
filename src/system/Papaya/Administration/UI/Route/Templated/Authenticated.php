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
namespace Papaya\Administration\UI\Route\Templated {

  use Papaya\Administration\UI\Route\Templated;
  use Papaya\Router;

  /**
   * Execute the inner route if the session contains an authorized user.
   * Return the login page, otherwise.
   *
   * @package Papaya\Router\Route
   */
  class Authenticated extends Templated {
    private $_route;

    /**
     * @param \Papaya\Template $template
     * @param \Papaya\Router\Route $route
     */
    public function __construct(\Papaya\Template $template, Router\Route $route) {
      parent::__construct($template);
      $this->_route = $route;
    }

    /**
     * @param Router $router
     * @param Router\Path $address
     * @param int $level
     * @return null|true|\Papaya\Response|callable
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      $application = $router->papaya();
      $user = $application->administrationUser;
      $user->layout = $this->getTemplate();
      $user->initialize();
      $application->administrationPhrases->setLanguage($application->languages->getDefault());
      $user->execLogin();
      $uiLanguage = $application->languages->getLanguage(
        $application->administrationUser->options->get(\Papaya\Configuration\CMS::UI_LANGUAGE)
      );
      if ($uiLanguage) {
        $application->administrationPhrases->setLanguage($uiLanguage);
      }
      if ($application->administrationUser->isValid) {
        $route = $this->_route;
        return $route($router, $address, $level);
      }
      return $this->getOutput();
    }
  }
}
