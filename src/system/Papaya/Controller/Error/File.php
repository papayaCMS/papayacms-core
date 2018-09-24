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
namespace Papaya\Controller\Error;

use Papaya\Controller;

/**
 * Papaya Controller class for error pages with template file
 *
 * @package Papaya-Library
 * @subpackage Controller
 */
class File extends Controller\Error {
  /**
   * Set template data from file
   *
   * @param string $fileName
   *
   * @return bool
   */
  public function setTemplateFile($fileName) {
    if (!empty($fileName) &&
      \file_exists($fileName) &&
      \is_file($fileName) &&
      \is_readable($fileName)) {
      $this->_template = \file_get_contents($fileName);
      return TRUE;
    }
    return FALSE;
  }
}
