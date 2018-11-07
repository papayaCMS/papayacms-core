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
  use Papaya\Response;

  /**
   * Output one or more files
   */
  class Files implements Route {
    /**
     * @var string|string[]
     */
    private $_files;

    /**
     * @var string[]
     */
    private $_commentPatterns = [
      'success' => "/* File: %s */\n",
      'error' => "/* Failed: %s */\n"
    ];

    /**
     * @var string
     */
    private $_contentType;

    /**
     * @var string
     */
    private $_prefix;

    /**
     * @var string
     */
    private $_suffix;

    /**
     * @param string|string[] $files
     * @param string $contentType
     * @param string $prefix
     * @param string $suffix
     */
    public function __construct($files, $contentType, $prefix = '', $suffix = '') {
      $this->_files = $files;
      $this->_contentType = $contentType;
      $this->_prefix = $prefix;
      $this->_suffix = $suffix;
    }

    /**
     * @param \Papaya\Administration\Router $router
     * @param Address $address
     * @param int $level
     * @return null|Response
     */
    public function __invoke(\Papaya\Administration\Router $router, Address $address, $level = 0) {
      return $this->createResponse($this->getFilesContent());
    }

    public function setCommentPattern($success, $error) {
      $this->_commentPatterns = [
        'success' => (string)$success,
        'error' => (string)$error
      ];
    }

    protected function createResponse($content) {
      $response = new Response();
      $response->setContentType($this->_contentType);
      $response->content(new Response\Content\Text($content));
      return $response;
    }

    protected function getFilesContent() {
      $files = \is_array($this->_files) ? $this->_files : [$this->_files];
      $result = $this->_prefix;
      foreach ($files as $file) {
        if ($contents = @\file_get_contents($file)) {
          $result .= \sprintf($this->_commentPatterns['success'], \basename($file));
          $result .= $contents."\n";
        } else {
          $result .= \sprintf($this->_commentPatterns['error'], \basename($file));
        }
      }
      $result .= $this->_suffix;
      return $result;
    }
  }
}
