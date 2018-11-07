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

  use Papaya\Administration\UI\Route\Address;
  use Papaya\Administration\UI\Route\Templated;

  class Installer extends Templated {

    /**
     * @param \Papaya\Administration\Router $router
     * @param Address $address
     * @param int $level
     * @return null|\Papaya\Response
     */
    public function __invoke(\Papaya\Administration\Router $router, Address $address, $level = 0) {
      $application = $router->papaya();
      $installer = new \papaya_installer();
      $installer->getCurrentStatus();
      if (!$application->administrationUser->isValid) {
        $application->removeObject('administrationPhrases', TRUE);
      }
      $this->setTitle(
        $application->images['categories-installer'], ['Administration', 'Installation / Update']
      );
      if ($redirect = $application->session->activate(TRUE)) {
        return $redirect->send();
      }
      $installer->layout = $this->getTemplate();
      $installer->initialize();
      if ($response = $installer->execute()) {
        return $response;
      }
      $this->getTemplate()->parameters()->set('PAGE_MODE', 'installer');
      return $this->getOutput();
    }
  }
}
