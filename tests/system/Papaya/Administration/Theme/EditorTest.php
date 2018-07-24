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

use Papaya\Administration\Page\Part;
use Papaya\Administration\Theme\Editor;
use Papaya\Template;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationThemeEditorTest extends PapayaTestCase {

  /**
   * @covers Editor::createContent
   */
  public function testCreateContent() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->createMock(Template::class);
    $page = new PapayaAdministrationThemeEditor_TestProxy($template);
    $this->assertInstanceOf(
      Part::class, $page->createContent()
    );
  }

  /**
   * @covers Editor::createNavigation
   */
  public function testCreateNavigation() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->createMock(Template::class);
    $page = new PapayaAdministrationThemeEditor_TestProxy($template);
    $this->assertInstanceOf(
      Part::class, $page->createNavigation()
    );
  }
}

class PapayaAdministrationThemeEditor_TestProxy extends Editor {

  public function createContent() {
    return parent::createContent();
  }

  public function createNavigation() {
    return parent::createNavigation();
  }
}
