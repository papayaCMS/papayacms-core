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

  class Installer implements Route {
    /**
     * @param UI $ui
     * @param Address $path
     * @param int $level
     * @return null|\Papaya\Response
     */
    public function __invoke(UI $ui, Address $path, $level = 0) {
      $application = $ui->papaya();
      $installer = new \papaya_installer();
      $installer->getCurrentStatus();
      if (!$application->administrationUser->isValid) {
        $application->removeObject('administrationPhrases', TRUE);
      }
      $ui->setTitle(
        $application->images['categories-installer'], ['Administration', 'Installation / Update']
      );
      if ($redirect = $application->session->activate(TRUE)) {
        return $redirect->send();
      }
      $installer->layout = $ui->template();
      $installer->initialize();
      if ($response = $installer->execute()) {
        return $response;
      }
      $ui->template()->parameters()->set('PAGE_MODE', 'installer');
      return $ui->getOutput();
    }
  }
}
