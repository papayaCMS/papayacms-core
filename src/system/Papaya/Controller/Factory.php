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

namespace Papaya\Controller;
/**
 * Papaya Controller Factory, a class to create special controllers more simple
 *
 * @package Papaya-Library
 * @subpackage Controller
 */
class Factory {

  /**
   * Get error controller to return.
   *
   * If a template file is provided a \Papaya\Controller\Error\PapayaControllerErrorFile is created.
   *
   * @param integer $status
   * @param string $errorIdentifier
   * @param string $errorMessage
   * @param string $templateFile
   * @return Error
   */
  public static function createError($status, $errorIdentifier, $errorMessage, $templateFile = '') {
    if (empty($templateFile)) {
      $controller = new Error();
    } else {
      $controller = new Error\File();
      $controller->setTemplateFile($templateFile);
    }
    $controller->setError($errorMessage, $errorIdentifier);
    $controller->setStatus($status);
    return $controller;
  }
}
