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
namespace Papaya\CMS\Administration\UI\Route\Templated {

  use Papaya\CMS\Administration\UI\Route\Templated;
  use Papaya\Router;

  /**
   * Route for the papaya administration plugins/extensions.
   *
   *   $routeName - shows extension list
   *   $routeName.$guid - executes module
   *   $routeName.'image' - returns extension image (module guid as query string parameter)
   */
  class Extensions extends Templated {
    /**
     * @var string
     */
    private $_image;

    /**
     * @var array|string
     */
    private $_caption;

    /**
     * @param \Papaya\Template $template
     * @param string $image
     * @param array|string $caption
     */
    public function __construct(\Papaya\Template $template, $image, $caption) {
      parent::__construct($template);
      $this->_image = $image;
      $this->_caption = $caption;
    }

    /**
     * @param Router $router
     * @param Router\Path $address
     * @param int $level
     * @return null|\Papaya\Response
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      $this->setTitle($this->_image, $this->_caption);
      $pluginGuid = NULL;
      if (($c = \count($address)) > 0) {
        $pluginGuid = \Papaya\Filter\Factory::isGuid($address[$c - 1]) ? $address[$c - 1] : NULL;
      }
      $module = new \papaya_editmodules($pluginGuid);
      if ('image' === $address[1]) {
        $module->getGlyph();
      } else {
        $module->administrationUI = $router;
        $module->initialize();
        $module->execute();
        return $this->getOutput();
      }
      return NULL;
    }
  }
}
