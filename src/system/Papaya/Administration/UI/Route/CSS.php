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
  use Papaya\Response;

  /**
   * Execute the inner route if the session contains an authorized user.
   * Return the login page, otherwise.
   *
   * @package Papaya\Administration\UI\Route
   */
  class CSS extends Files {
    /**
     * @var string|string[]
     */
    private $_files;

    /**
     * @var string
     */
    private $_themeVariablesPath;

    /**
     * @param string|string[] $files
     * @param string $themeVariablesPath
     */
    public function __construct($files, $themeVariablesPath = '') {
      parent::__construct($files, 'text/css');
      $this->_themeVariablesPath = \trim($themeVariablesPath);
    }

    /**
     * @param \Papaya\Administration\UI $ui
     * @param Address $path
     * @param int $level
     * @return null|Response
     */
    public function __invoke(UI $ui, Address $path, $level = 0) {
      $css = $this->getFilesContent();

      $variables = $this->getThemeVariables(
        $this->_themeVariablesPath, empty($_GET['theme'])
          ? $ui->papaya()->options->get('PAPAYA_UI_THEME', '')
          : $_GET['theme']
      );
      if ($variables) {
        $engine = new \Papaya\Template\Engine\Simple();
        $engine->loaders()->add(new \Papaya\Template\Engine\Values\ArrayLoader());
        $engine->setTemplateString($css);
        $engine->values($variables);
        $engine->prepare();
        $engine->run();
        $css = $engine->getResult();
      }

      return $this->createResponse($css);
    }

    /**
     * @param string $basePath
     * @param string $themeName
     * @return bool|array
     */
    private function getThemeVariables($basePath, $themeName) {
      if ('' !== $this->_themeVariablesPath && preg_match('(^[a-z\d_]+)', $themeName)) {
        $fileName = \Papaya\Utility\File\Path::cleanup($basePath.'/').$themeName.'.ini';
        if (file_exists($fileName) && is_readable($fileName)) {
          return parse_ini_file($fileName, TRUE,INI_SCANNER_NORMAL) ?: FALSE;
        }
      }
      return FALSE;
    }
  }
}
