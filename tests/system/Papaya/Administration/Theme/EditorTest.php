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

namespace Papaya\Administration\Theme {

  require_once __DIR__.'/../../../../bootstrap.php';

  class EditorTest extends \PapayaTestCase {

    /**
     * @covers Editor::createContent
     */
    public function testCreateContent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $template */
      $template = $this->createMock(\Papaya\Template::class);
      $page = new Editor_TestProxy($template);
      $this->assertInstanceOf(
        \Papaya\Administration\Page\Part::class, $page->createContent()
      );
    }

    /**
     * @covers Editor::createNavigation
     */
    public function testCreateNavigation() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $template */
      $template = $this->createMock(\Papaya\Template::class);
      $page = new Editor_TestProxy($template);
      $this->assertInstanceOf(
        \Papaya\Administration\Page\Part::class, $page->createNavigation()
      );
    }
  }

  class Editor_TestProxy extends Editor {

    public function createContent() {
      return parent::createContent();
    }

    public function createNavigation() {
      return parent::createNavigation();
    }
  }
}
