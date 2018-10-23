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

  class Page extends Callback {

    /**
     * @param string $image
     * @param array|string $caption
     * @param string $pageClass
     * @param null $permission
     */
    public function __construct($image, $caption, $pageClass, $permission = NULL) {
      parent::__construct(
        $image,
        $caption,
        function(Administration\UI $ui) use ($pageClass, $permission) {
          if (
            NULL === $permission ||
            $ui->papaya()->administrationUser->hasPerm($permission)
          ) {
            $reflection = new \ReflectionClass($pageClass);
            if ($reflection->isSubclassOf(Administration\Page::class)) {
              $page = new $pageClass($ui);
              /** @noinspection PhpUndefinedMethodInspection */
              $page->execute();
              return $ui->getOutput();
            }
            if ($reflection->hasMethod('getXML')) {
              $page = new $pageClass();
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
      );
    }
  }
}
