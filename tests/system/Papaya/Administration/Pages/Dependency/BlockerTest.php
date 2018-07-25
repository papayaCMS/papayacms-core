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

use Papaya\Administration\Languages\Selector;
use Papaya\Administration\Pages\Dependency\Blocker;
use Papaya\Administration\Pages\Dependency\Counter;
use Papaya\Content\Page\Dependencies;
use Papaya\Content\Page\Dependency;
use Papaya\Content\Pages;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyBlockerTest extends PapayaTestCase {

  /**
  * @covers Blocker::__construct
  */
  public function testConstructor() {
    $blocker = new Blocker(42);
    $this->assertAttributeSame(
      42, '_pageId', $blocker
    );
  }

  /**
  * @covers Blocker::dependency
  */
  public function testDependencyGetAfterSet() {
    $dependency = $this->createMock(Dependency::class);
    $blocker = new Blocker(42);
    $this->assertSame($dependency, $blocker->dependency($dependency));
  }

  /**
  * @covers Blocker::dependency
  */
  public function testDependencyImplicitCreate() {
    $blocker = new Blocker(42);
    $this->assertInstanceOf(Dependency::class, $blocker->dependency());
  }

  /**
   * @covers Blocker::isSynchronized
   * @covers Blocker::prepare
   * @dataProvider provideSynchronizationData
   * @param bool $expected
   * @param int $checkFor
   * @param int $synchronizations
   */
  public function testDependencyIsSynchronized($expected, $checkFor, $synchronizations) {
    $dependency = $this->getRecordFixture(
      array('synchronization' => $synchronizations)
    );
    $blocker = new Blocker(42);
    $blocker->dependency($dependency);
    $this->assertEquals($expected, $blocker->isSynchronized($checkFor));
  }

  /**
  * @covers Blocker::isSynchronized
  * @covers Blocker::prepare
  */
  public function testDependencyLoadsOnlyOnce() {
    $dependency = $this->getRecordFixture(
      array(
        'synchronization' =>
           Dependency::SYNC_PROPERTIES | Dependency::SYNC_CONTENT
      ),
      1
    );
    $blocker = new Blocker(42);
    $blocker->dependency($dependency);
    $this->assertTrue($blocker->isSynchronized(Dependency::SYNC_PROPERTIES));
    $this->assertTrue($blocker->isSynchronized(Dependency::SYNC_CONTENT));
  }

  /**
  * @covers Blocker::isSynchronized
  * @covers Blocker::prepare
  */
  public function testDependencyLoadsAgainIfRequested() {
    $dependency = $this->getRecordFixture(
      array(
        'synchronization' =>
           Dependency::SYNC_PROPERTIES | Dependency::SYNC_CONTENT
      ),
      2
    );
    $blocker = new Blocker(42);
    $blocker->dependency($dependency);
    $this->assertTrue($blocker->isSynchronized(Dependency::SYNC_PROPERTIES, TRUE));
    $this->assertTrue($blocker->isSynchronized(Dependency::SYNC_CONTENT, TRUE));
  }

  /**
  * @covers Blocker::appendTo
  */
  public function testAppendTo() {
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $dependency = $this->getRecordFixture(
      array('originId' => 21)
    );
    $blocker = new Blocker(42);
    $blocker->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array('status-system-locked' => 'locked.png'),
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    $blocker->dependency($dependency);
    $blocker->pages($pages);
    $blocker->parameterGroup('tt');
    $this->assertXmlStringEqualsXmlString(
       /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Page dependency"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="tt[page_id]" value="21"/>
        <input type="hidden" name="tt[confirmation]" value="3736585d7485423c0f483f7aff32ef68"/>
        <field class="DialogFieldInformation" error="no">
          <message image="locked.png">This part of the page is synchronized with page "[...] #21".</message>
        </field>
        <button type="submit" align="right">GoTo Origin Page</button>
      </dialog-box>',
      $blocker->getXml()
    );
  }

  /**
  * @covers Blocker::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $dependencies = $this->createMock(Dependencies::class);
    $blocker = new Blocker(42);
    $this->assertSame(
      $dependencies, $blocker->dependencies($dependencies)
    );
  }

  /**
  * @covers Blocker::dependencies
  */
  public function testDependenciesGetImplicitCreate() {
    $blocker = new Blocker(42);
    $this->assertInstanceOf(
      Dependencies::class, $blocker->dependencies()
    );
  }


  /**
  * @covers Blocker::views
  */
  public function testViewsGetAfterSet() {
    $views = $this->createMock(PapayaContentViews::class);
    $blocker = new Blocker(42);
    $this->assertSame(
      $views, $blocker->views($views)
    );
  }

  /**
  * @covers Blocker::views
  */
  public function testViewsGetImplicitCreate() {
    $blocker = new Blocker(42);
    $this->assertInstanceOf(
      PapayaContentViews::class, $blocker->views()
    );
  }

  /**
  * @covers Blocker::pages
  */
  public function testPagesGetAfterSet() {
    $pages = $this->createMock(Pages::class);
    $blocker = new Blocker(42);
    $this->assertSame(
      $pages, $blocker->pages($pages)
    );
  }

  /**
  * @covers Blocker::pages
  */
  public function testPagesGetImplicitCreate() {
    $blocker = new Blocker(42);
    $this->assertInstanceOf(
      Pages::class, $blocker->pages()
    );
  }

  /**
  * @covers Blocker::counter
  */
  public function testCounterGetAfterSet() {
    $counter = $this
      ->getMockBuilder(Counter::class)
      ->disableOriginalConstructor()
      ->getMock();
    $blocker = new Blocker(42);
    $this->assertSame(
      $counter, $blocker->counter($counter)
    );
  }

  /**
  * @covers Blocker::counter
  */
  public function testCounterGetImplicitCreate() {
    $blocker = new Blocker(42);
    $this->assertInstanceOf(
      Counter::class, $blocker->counter()
    );
  }

  /**
  * @covers Blocker::getSynchronizedViews
  */
  public function testGetSynchronizedViews() {
    $dependency = $this->getRecordFixture();
    $dependency
      ->expects($this->once())
      ->method('isOrigin')
      ->with(42)
      ->will($this->returnValue(TRUE));

    $dependencies = $this->createMock(Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(42, 2)
      ->will($this->returnValue(TRUE));
    $dependencies
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              21 => array(
                'id' => 21,
                'view_id' => 30,
                'synchronization' => Dependency::SYNC_VIEW
              ),
              42 => array(
                'id' => 42,
                'view_id' => 31,
                'synchronization' => Dependency::SYNC_CONTENT
              ),
              84 => array(
                'id' => 84,
                'view_id' => 32,
                'synchronization' =>
                  Dependency::SYNC_VIEW &
                  Dependency::SYNC_CONTENT
              )
            )
          )
        )
      );

    $views = $this->createMock(PapayaContentViews::class);
    $views
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => array(30, 31)))
      ->will($this->returnValue(TRUE));
    $views
      ->expects($this->any())
      ->method('offsetExists')
      ->will(
        $this->onConsecutiveCalls(TRUE, TRUE, FALSE)
      );
    $views
      ->expects($this->any())
      ->method('offsetGet')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'id' => 30,
            'module_guid' => 'ab123456789012345678901234567890'
          ),
          array(
            'id' => 31,
            'module_guid' => 'ef123456789012345678901234567890'
          )
        )
      );


    $blocker = new Blocker(42);
    $blocker->dependency($dependency);
    $blocker->dependencies($dependencies);
    $blocker->views($views);
    $this->assertEquals(
      array(
        21 => array(
          'id' => 30,
          'module_guid' => 'ab123456789012345678901234567890'
        ),
        42 => array(
          'id' => 31,
          'module_guid' => 'ef123456789012345678901234567890'
        )
      ),
      $blocker->getSynchronizedViews(2)
    );
  }

  /************************************
  * Fixtures
  ************************************/

  private function getLanguageSwitchFixture() {
    $language = new stdClass();
    $language->id = 1;
    $switch = $this->createMock(Selector::class);
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }

  public function getRecordFixture(array $data = array(), $loadCounter = 0) {
    $record = $this->createMock(Dependency::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator($data))
      );
    $record
      ->expects($loadCounter ? $this->exactly($loadCounter) : $this->any())
      ->method('load')
      ->will(
        $this->returnValue(!empty($data))
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($data) {
          return $data[$name];
        }
      );
    return $record;
  }

  /************************************
  * Data Provider
  ************************************/

  public static function provideSynchronizationData() {
    return array(
      'single value - TRUE' => array(
        TRUE,
        Dependency::SYNC_PROPERTIES,
        Dependency::SYNC_PROPERTIES
      ),
      'single value - FALSE' => array(
        FALSE,
        Dependency::SYNC_PROPERTIES,
        Dependency::SYNC_CONTENT
      ),
      'multiple values - TRUE' => array(
        TRUE,
        Dependency::SYNC_PROPERTIES,
        Dependency::SYNC_PROPERTIES | Dependency::SYNC_CONTENT
      ),
      'multiple values - FALSE' => array(
        FALSE,
        Dependency::SYNC_PROPERTIES,
        Dependency::SYNC_CONTENT | Dependency::SYNC_BOXES
      ),
    );
  }
}
