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
namespace Papaya\Template\XSLT;

use Papaya\Application;
use Papaya\Request;
use Papaya\Utility;

/**
 * Papaya XSLT template handler class
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Handler extends Application\BaseObject {
  /**
   * Get absolute local file path to current template directory
   *
   * @return string
   */
  public function getLocalPath() {
    $path = $this
      ->papaya()
      ->options
      ->get(\Papaya\Configuration\CMS::PATH_TEMPLATES);
    return Utility\File\Path::cleanup($path.'/'.$this->getTemplate());
  }

  /**
   * Get the currently active template name
   *
   * @return string
   */
  public function getTemplate() {
    $template = '';
    $isPreview = $this
      ->papaya()
      ->request
      ->getParameter('preview', FALSE, NULL, Request::SOURCE_PATH);
    if ($isPreview) {
      $template = $this
        ->papaya()
        ->session
        ->values
        ->get('PapayaPreviewTemplate');
    }
    if (empty($template)) {
      $template = $this
        ->papaya()
        ->options
        ->get(\Papaya\Configuration\CMS::LAYOUT_TEMPLATES);
    }
    return $template;
  }

  /**
   * Set preview template (saved in session)
   *
   * @param string $templateName
   */
  public function setTemplatePreview($templateName) {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTemplate', $templateName);
  }

  /**
   * Remove preview template (saved in session)
   */
  public function removeTemplatePreview() {
    $this
      ->papaya()
      ->session
      ->values
      ->set('PapayaPreviewTemplate', NULL);
  }
}
