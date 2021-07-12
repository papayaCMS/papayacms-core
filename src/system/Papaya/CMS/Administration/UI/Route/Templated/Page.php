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

  use Papaya\CMS\Administration;
  use Papaya\CMS\Administration\UI\Route\Templated;
  use Papaya\Router;

  /**
   * Execute an \Papaya\CMS\Administration\Page or one of
   * the old classes and return them as an HTML response.
   */
  class Page extends Templated {
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
     * @param \Papaya\Template $template
     * @param string $image
     * @param array|string $caption
     * @param string $className
     * @param null|int $permission
     */
    public function __construct(\Papaya\Template $template, $image, $caption, $className, $permission = NULL) {
      parent::__construct($template);
      $this->_image = $image;
      $this->_caption = $caption;
      $this->_className = $className;
      $this->_permission = $permission;
    }

    /**
     * @param Router $router
     * @param Router\Path $address
     * @param int $level
     * @return null|\Papaya\Response|callable
     * @throws \ReflectionException
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      if (
        NULL === $this->_permission ||
        $router->papaya()->administrationUser->hasPerm($this->_permission)
      ) {
        $this->setTitle($this->_image, $this->_caption);
        $reflection = new \ReflectionClass($this->_className);
        if ($reflection->isSubclassOf(Administration\Page::class)) {
          $page = $reflection->newInstance($router);
          /** @noinspection PhpUndefinedMethodInspection */
          $page->execute();
          return $this->getOutput();
        }
        if ($reflection->hasMethod('getXML')) {
          $page = $reflection->newInstance();
          $page->administrationUI = $router;
          $page->layout = $this->getTemplate();
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
          return $this->getOutput();
        }
      } else {
        return new Router\Route\Error('Can not access route - permission denied.', 403);
      }
      return NULL;
    }
  }
}
