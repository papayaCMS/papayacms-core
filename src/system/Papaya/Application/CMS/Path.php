<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Application\CMS {

  use Papaya\Application\Access;
  use Papaya\BaseObject\Interfaces\StringCastable;
  use Papaya\Configuration;
  use Papaya\Configuration\CMS;
  use Papaya\Utility\File\Path as FilePathUtilities;

  class Path implements StringCastable, Access {

    use Access\Aggregation;

    const ADMINISTRATION = 'administration:';
    const PROJECT_SOURCE = 'src:';
    const DEPENDENCIES = 'vendor:';

    const TEMPLATES = 'templates:';
    const THEMES = 'themes:';

    const CURRENT_TEMPLATE = 'template:';
    const CURRENT_THEME = 'theme:';

    private $_path;

    public function __construct($path) {
      $this->_path = $path;
    }

    public function __toString() {
      try {
        return self::resolve($this->_path, $this->options);
      } catch (\Exception $e) {
        return $this->_path;
      }
    }

    public static function resolve($path, Configuration $options) {
      if (FALSE !== ($p = strpos($path, ':'))) {
        $prefix = substr($path, 0, $p + 1);
        $subPath = substr($path, $p + 1);
        switch ($prefix) {
        case self::ADMINISTRATION:
          $path = FilePathUtilities::getDocumentRoot($options).$options[CMS::PATH_ADMIN];
          break;
        case self::PROJECT_SOURCE:
          $path = FilePathUtilities::getSourcePath($options);
          break;
        case self::DEPENDENCIES:
          $path = FilePathUtilities::getVendorPath($options);
          break;
        case self::TEMPLATES:
          $path = $options->get(CMS::PATH_TEMPLATES, '');
          break;
        case self::THEMES:
          $path = FilePathUtilities::getDocumentRoot($options).$options->get(CMS::PATH_THEMES, '');
          break;
        case self::CURRENT_THEME:
          $path = self::resolve(self::THEMES, $options).'/'.$options->get(CMS::LAYOUT_THEME).'/';
          break;
        case self::CURRENT_TEMPLATE:
          $path = self::resolve(self::TEMPLATES, $options).'/'.$options->get(CMS::LAYOUT_TEMPLATES).'/';
          break;
        }
        return FilePathUtilities::cleanup($path.$subPath);
      }
      return FilePathUtilities::cleanup($path);
    }

  }
}


