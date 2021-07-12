<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Administration {

  use Papaya\Router\Path as RouterAddress;
  use Papaya\Router\Route;
  use Papaya\Template;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\CMS\Administration\UI
   */
  class UITest extends TestCase {

    public function testCreateRoute() {
      $ui = new UI(__DIR__, $this->mockPapaya()->application());
      $route = $ui->createRoute();
      $this->assertInstanceOf(Route::class, $route);
    }

    public function testAddressGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|RouterAddress $routerAddress */
      $routerAddress = $this->createMock(RouterAddress::class);

      $ui = new UI(__DIR__, $this->mockPapaya()->application());
      $this->assertSame($routerAddress, $ui->address($routerAddress));
    }

    public function testAddressImplicitCreate() {
      $options = $this->mockPapaya()->options(['PAPAYA_PATH_ADMIN' => '/test/']);
      $ui = new UI(__DIR__, $this->mockPapaya()->application(['options' => $options]));
      $this->assertInstanceOf(RouterAddress::class, $ui->address());
    }

    public function testTemplateGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = $this->createMock(Template::class);

      $ui = new UI(__DIR__, $this->mockPapaya()->application());
      $this->assertSame($template, $ui->template($template));
    }

    public function testTemplateImplicitCreate() {
      $ui = new UI(__DIR__, $this->mockPapaya()->application());
      $this->assertInstanceOf(Template\XSLT::class, $ui->template());
      $this->assertStringEndsWith('style.xsl', $ui->template()->getXSLFile());
    }
  }

}
