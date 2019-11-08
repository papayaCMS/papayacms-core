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
namespace Papaya\Router\Route {

  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Template\Engine as TemplateEngine;
  use Papaya\Utility\File\Path as FilePathUtility;

  /**
   * Execute the inner route if the session contains an authorized user.
   * Return the login page, otherwise.
   *
   * @package Papaya\Router\Route
   */
  class CSS extends Files {
    /**
     * @var string
     */
    private $_themesPath;

    /**
     * @var
     */
    private $_themeName;

    /**
     * @param string|string[] $files
     * @param string $themeName
     * @param string $themesPath
     */
    public function __construct($files, $themeName, $themesPath = '') {
      parent::__construct($files, 'text/css');
      $this->_themeName = $themeName;
      $this->_themesPath = \trim($themesPath);
    }

    /**
     * @param Router $router
     * @param NULL|object $context
     * @return null|Response
     */
    public function __invoke(Router $router, $context = NULL) {
      $css = $this->getFilesContent();

      $variables = $this->getThemeVariables();
      if ($variables) {
        $engine = new TemplateEngine\Simple();
        $engine->loaders()->add(new TemplateEngine\Values\ArrayLoader());
        $engine->setTemplateString($css);
        $engine->values($variables);
        $engine->prepare();
        $engine->run();
        $css = $engine->getResult();
      }

      return $this->createResponse($css);
    }

    /**
     * @return bool|array
     */
    private function getThemeVariables() {
      if ('' !== $this->_themesPath && \preg_match('(^[a-z\d_]+)', $this->_themeName)) {
        $fileName = FilePathUtility::cleanup($this->_themesPath.'/').$this->_themeName;
        if (substr($fileName, -4) !== '.ini') {
          $fileName .= '.ini';
        }
        if (\file_exists($fileName) && \is_readable($fileName)) {
          return \parse_ini_file($fileName, TRUE, INI_SCANNER_NORMAL) ?: FALSE;
        }
      }
      return FALSE;
    }
  }
}
