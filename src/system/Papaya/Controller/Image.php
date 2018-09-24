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

use Papaya\Application;
use Papaya\Controller;
use Papaya\Request;
use Papaya\Response;

/**
 * Papaya controller class for dynamic images
 *
 * @package Papaya-Library
 * @subpackage Controller
 */
class Image implements Controller {
  private $_imageGenerator;

  /**
   * Set image generator object
   *
   * @param \base_imagegenerator $imageGenerator
   */
  public function setImageGenerator($imageGenerator) {
    $this->_imageGenerator = $imageGenerator;
  }

  /**
   * Get image generator object (implicit create)
   *
   * @return \base_imagegenerator
   */
  public function getImageGenerator() {
    if (NULL === $this->_imageGenerator) {
      $this->_imageGenerator = new \base_imagegenerator();
    }
    return $this->_imageGenerator;
  }

  /**
   * Execute controller
   *
   * @param Application|Application\CMS $application
   * @param Request &$request
   * @param Response &$response
   *
   * @return bool|Controller
   */
  public function execute(
    /** @noinspection ReferencingObjectsInspection */
    Application $application,
    Request &$request,
    Response &$response
  ) {
    $imgGenerator = $this->getImageGenerator();
    $imgGenerator->publicMode = $request->getParameter(
      'preview', TRUE, NULL, Request::SOURCE_PATH
    );
    if ($imgGenerator->publicMode || $application->administrationUser->isLoggedIn()) {
      $identifier = $request->getParameter(
        'image_identifier', '', NULL, Request::SOURCE_PATH
      );
      if (!empty($identifier) &&
        $imgGenerator->loadByIdent($identifier)) {
        if ($imgGenerator->generateImage()) {
          return TRUE;
        }
        return Factory::createError(
          500, 'DYNAMIC_IMAGE_CREATE', $imgGenerator->lastError
        );
      }
      return Factory::createError(
        404, 'DYNAMIC_IMAGE_NOT_FOUND', 'Image identifier not found'
      );
    }
    return Factory::createError(
      403, 'DYNAMIC_IMAGE_ACCESS', 'Permission denied'
    );
  }
}
