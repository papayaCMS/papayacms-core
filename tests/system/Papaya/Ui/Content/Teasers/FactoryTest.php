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

use Papaya\Content\Pages\Publications;
use Papaya\Content\Pages;
use Papaya\Database\Interfaces\Order;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiContentTeasersFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByFilterWithParentIdAndViewId() {
    $orderBy = $this->createMock(Order::class);

    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byFilter(
      array('parent' => 21, 'view_id' => 42, 'language_id' => 1), $orderBy
    );
    $this->assertInstanceOf(PapayaUiContentTeasers::class, $teasers);
    $this->assertInstanceOf(Publications::class, $teasers->pages());

  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByParentWithOnePageIdInPreviewMode() {
    $request = $this->mockPapaya()->request();
    $request
      ->expects($this->any())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('isPreview', true),
            array('languageId', 9)
          )
        )
      );

    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array('request' => $request)
      )
    );

    $teasers = $factory->byParent(42);
    $this->assertInstanceOf(PapayaUiContentTeasers::class, $teasers);
    $this->assertInstanceOf(Pages::class, $teasers->pages());
    $this->assertNotInstanceOf(Publications::class, $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByParentWithTwoPageIdsWithIndividualOrderBy() {
    $orderBy = $this->createMock(Order::class);

    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byParent(array(21, 42), $orderBy);
    $this->assertInstanceOf(PapayaUiContentTeasers::class, $teasers);
    $this->assertInstanceOf(Publications::class, $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByParentWithTwoPageIdsWithInvalidOrderBy() {
    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byParent(array(21, 42), 'invalid');
    $this->assertInstanceOf(PapayaUiContentTeasers::class, $teasers);
    $this->assertInstanceOf(Publications::class, $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasersFactory
   */
  public function testByPageIdWithOnePageId() {
    $factory = new PapayaUiContentTeasersFactory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byPageId(42);
    $this->assertInstanceOf(PapayaUiContentTeasers::class, $teasers);
    $this->assertInstanceOf(Publications::class, $teasers->pages());
  }

}
