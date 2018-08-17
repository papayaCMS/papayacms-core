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

namespace Papaya\UI\Content\Teasers;
require_once __DIR__.'/../../../../../bootstrap.php';

class FactoryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Content\Teasers\Factory
   */
  public function testByFilterWithParentIdAndViewId() {
    $orderBy = $this->createMock(\Papaya\Database\Interfaces\Order::class);

    $factory = new Factory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byFilter(
      array('parent' => 21, 'view_id' => 42, 'language_id' => 1), $orderBy
    );
    $this->assertInstanceOf(\Papaya\UI\Content\Teasers::class, $teasers);
    $this->assertInstanceOf(\Papaya\Content\Page\Publications::class, $teasers->pages());

  }

  /**
   * @covers \Papaya\UI\Content\Teasers\Factory
   */
  public function testByParentWithOnePageIdInPreviewMode() {
    $request = $this->mockPapaya()->request();
    $request
      ->expects($this->any())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('isPreview', TRUE),
            array('languageId', 9)
          )
        )
      );

    $factory = new Factory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array('request' => $request)
      )
    );

    $teasers = $factory->byParent(42);
    $this->assertInstanceOf(\Papaya\UI\Content\Teasers::class, $teasers);
    $this->assertInstanceOf(\Papaya\Content\Pages::class, $teasers->pages());
    $this->assertNotInstanceOf(\Papaya\Content\Page\Publications::class, $teasers->pages());
  }

  /**
   * @covers \Papaya\UI\Content\Teasers\Factory
   */
  public function testByParentWithTwoPageIdsWithIndividualOrderBy() {
    $orderBy = $this->createMock(\Papaya\Database\Interfaces\Order::class);

    $factory = new Factory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byParent(array(21, 42), $orderBy);
    $this->assertInstanceOf(\Papaya\UI\Content\Teasers::class, $teasers);
    $this->assertInstanceOf(\Papaya\Content\Page\Publications::class, $teasers->pages());
  }

  /**
   * @covers \Papaya\UI\Content\Teasers\Factory
   */
  public function testByParentWithTwoPageIdsWithInvalidOrderBy() {
    $factory = new Factory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byParent(array(21, 42), 'invalid');
    $this->assertInstanceOf(\Papaya\UI\Content\Teasers::class, $teasers);
    $this->assertInstanceOf(\Papaya\Content\Page\Publications::class, $teasers->pages());
  }

  /**
   * @covers \Papaya\UI\Content\Teasers\Factory
   */
  public function testByPageIdWithOnePageId() {
    $factory = new Factory();
    $factory->papaya($this->mockPapaya()->application());

    $teasers = $factory->byPageId(42);
    $this->assertInstanceOf(\Papaya\UI\Content\Teasers::class, $teasers);
    $this->assertInstanceOf(\Papaya\Content\Page\Publications::class, $teasers->pages());
  }

}
