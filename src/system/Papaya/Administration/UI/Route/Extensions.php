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

  use Papaya\Administration\UI\Route;

  class Extensions implements Route {

    private $_image;
    private $_caption;

    /**
     * @param string $image
     * @param array|string $caption
     */
    public function __construct($image, $caption) {
      $this->_image = $image;
      $this->_caption = $caption;
    }

    /**
     * @param \Papaya\Administration\UI $ui
     * @param Address $path
     * @param int $level
     */
    public function __invoke(\Papaya\Administration\UI $ui, Address $path, $level = 0) {
      $ui->setTitle($this->_image, $this->_caption);
      $pluginGuid = NULL;
      if (($c = count($path)) > 0) {
        $pluginGuid = \Papaya\Filter\Factory::isGuid($path[$c - 1]) ? $path[$c - 1] : NULL;
      }
      $module = new \papaya_editmodules($pluginGuid);
      if ('image' === $path[1]) {
        $module->getGlyph();
      } else {
        $module = new \papaya_editmodules($pluginGuid);
        $module->layout = $ui->template();
        $module->initialize();
        $module->execute();
      }
    }
  }
}
