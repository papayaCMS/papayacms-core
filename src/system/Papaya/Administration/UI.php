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
namespace Papaya\Administration {

  use Papaya\Application;
  use Papaya\Utility;
  use Papaya\Template;

  class UI implements Application\Access {
    use Application\Access\Aggregation;

    private $_template;

    public function __construct(Application $application) {
      $this->papaya($application);
    }

    public function execute() {
      $this->prepare();
    }

    public function getOutput() {

    }

    private function prepare() {
      $application = $this->papaya();
      $application->messages->setUp($application->options);
      if ($application->options->get('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
        \Papaya\Request\Log::getInstance();
      }
      $application->request->isAdministration = TRUE;
      $application->session->isAdministration(TRUE);
      if ($redirect = $application->session->activate(TRUE)) {
        $redirect->send(TRUE);
      }
      $application->pageReferences->setPreview(TRUE);

      // validate options and show warnings
      if (
        '' !== ($path = $application->options->get('PAPAYA_PATH_DATA')) &&
        FALSE !== strpos($path, $_SERVER['DOCUMENT_ROOT']) &&
        file_exists($path) && (!file_exists($path.'.htaccess'))
      ) {
        $application->messages->displayWarning(
          'The file ".htaccess" in the directory "papaya-data/" '.
          'is missing or not accessible. Please secure the directory.'
        );
      }
      if (!$application->options->get('PAPAYA_PASSWORD_REHASH', FALSE)) {
        $application->messages->displayWarning(
          'The password rehashing is not active. Please activate PAPAYA_PASSWORD_REHASH.'.
          ' Make sure the authentication tables are up to date before activating'.
          ' this option, otherwise the logins can become locked.'
        );
      }
    }

    public function template(Template $template = NULL) {
      if (NULL !== $template) {
        $this->_template = $template;
      } elseif (NULL === $this->_template) {
        $application = $this->papaya();
        $administrationPath = Utility\File\Path::cleanup(
          Utility\File\Path::getDocumentRoot(
            $application->options
          ).$application->options->get('PAPAYA_PATH_ADMIN')
        );
        $this->_template = new Template\XSLT(
          $administrationPath.'skins/'.$application->options->get('PAPAYA_UI_SKIN').'/style.xsl'
        );
      }
      return $this->_template;
    }
  }
}
