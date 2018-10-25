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

  use Papaya\Administration;
  use Papaya\Administration\UI\Route;

  /**
   * Execute an \Papaya\Administration\Page or one of
   * the old classes and return them as an HTML response.
   */
  class Page implements Route {
    /**
     * @var string
     */
    private $_image;

    /**
     * @var array|string
     */
    private $_caption;

    /**
     * @var string
     */
    private $_className;

    /**
     * @var int|null
     */
    private $_permission;

    /**
     * @param string $image
     * @param array|string $caption
     * @param string $className
     * @param null|int $permission
     */
    public function __construct($image, $caption, $className, $permission = NULL) {
      $this->_image = $image;
      $this->_caption = $caption;
      $this->_className = $className;
      $this->_permission = $permission;
    }

    /**
     * @param Administration\UI $ui
     * @param Address $path
     * @param int $level
     * @return null|\Papaya\Response
     * @throws \ReflectionException
     */
    public function __invoke(Administration\UI $ui, Address $path, $level = 0) {
      if (
        NULL === $this->_permission ||
        $ui->papaya()->administrationUser->hasPerm($this->_permission)
      ) {
        $ui->setTitle($this->_image, $this->_caption);
        $reflection = new \ReflectionClass($this->_className);
        if ($reflection->isSubclassOf(Administration\Page::class)) {
          $page = $reflection->newInstance($ui);
          /** @noinspection PhpUndefinedMethodInspection */
          $page->execute();
          return $ui->getOutput();
        }
        if ($reflection->hasMethod('getXML')) {
          $page = $reflection->newInstance();
          $page->administrationUI = $ui;
          $page->layout = $ui->template();
          if ($reflection->hasMethod('initialize')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $page->initialize();
          }
          if ($reflection->hasMethod('execute')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $page->execute();
          }
          /** @noinspection PhpUndefinedMethodInspection */
          $page->getXML();
          return $ui->getOutput();
        }
      }
      return NULL;
    }
  }
}
