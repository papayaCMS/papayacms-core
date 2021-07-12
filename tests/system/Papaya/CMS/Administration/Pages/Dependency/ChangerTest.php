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

namespace Papaya\CMS\Administration\Pages\Dependency;

require_once __DIR__.'/../../../../../../bootstrap.php';

class ChangerTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::getPageId
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::prepare
   */
  public function testGetPageId() {
    $changer = $this->getChangerFixture(42);
    $changer->prepare();
    $this->assertEquals(42, $changer->getPageId());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::getOriginId
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::prepare
   */
  public function testGetWithoutDependency() {
    $changer = $this->getChangerFixture(21);
    $changer->prepare();
    $this->assertEquals(21, $changer->getOriginId());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::getOriginId
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::prepare
   */
  public function testGetWithDependency() {
    $changer = $this->getChangerFixture(42, array('originId' => 21));
    $changer->prepare();
    $this->assertEquals(21, $changer->getOriginId());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::prepare
   */
  public function testPrepareLoadsReferenceIfTargetIsDefined() {
    $changer = new Changer();
    $changer->parameters(
      new \Papaya\Request\Parameters(
        array(
          'page_id' => 42,
          'target_id' => 84
        )
      )
    );
    $changer->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    $changer->dependency($this->getDependencyFixture());
    $changer->reference(
      $this->getReferenceFixture(
        array('source_id' => 42, 'target_id' => 84)
      )
    );
    $changer->prepare();
    $this->assertEquals(42, $changer->getPageId());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendTo
   */
  public function testAppendToWithoutPageId() {
    $changer = $this->getChangerFixture();
    $this->assertEquals('', $changer->getXML());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendTo
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendButtons
   */
  public function testAppendToWithOriginPage() {
    $changer = $this->getChangerFixture(21);
    $dependencies = $this->createMock(\Papaya\CMS\Content\Page\Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->createMock(\Papaya\CMS\Content\Page\References::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $pages = $this->createMock(\Papaya\CMS\Content\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(ListView::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->references($references);
    $changer->listview($listview);
    $this->assertEquals('', $changer->getXML());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendTo
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendButtons
   */
  public function testAppendToWithDependency() {
    $changer = $this->getChangerFixture(42, array('id' => 42, 'originId' => 21));
    $dependencies = $this->createMock(\Papaya\CMS\Content\Page\Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->createMock(\Papaya\CMS\Content\Page\References::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->createMock(\Papaya\CMS\Content\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(ListView::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->listview($listview);
    $changer->references($references);

    $elements = $this
      ->getMockBuilder(\Papaya\UI\Toolbar\Elements::class)
      ->disableOriginalConstructor()
      ->getMock();
    $elements
      ->expects($this->exactly(3))
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\Papaya\UI\Toolbar\Element::class));
    $commands = $this
      ->getMockBuilder(\Papaya\UI\Control\Command\Controller::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $changer->menu()->elements($elements);
    $changer->commands($commands);

    $this->assertEquals('', $changer->getXML());
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendTo
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::appendButtons
   */
  public function testAppendToWithReference() {
    $changer = new Changer();
    $changer->parameters(
      new \Papaya\Request\Parameters(
        array(
          'cmd' => 'reference_change',
          'page_id' => 42,
          'target_id' => 84
        )
      )
    );
    $changer->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    $changer->dependency($this->getDependencyFixture());
    $changer->reference(
      $this->getReferenceFixture(
        array(
          'sourceId' => 42,
          'targetId' => 84
        )
      )
    );

    $dependencies = $this->createMock(\Papaya\CMS\Content\Page\Dependencies::class);
    $references = $this->createMock(\Papaya\CMS\Content\Page\References::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->createMock(\Papaya\CMS\Content\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 0, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(ListView::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->references($references);
    $changer->listview($listview);

    $elements = $this
      ->getMockBuilder(\Papaya\UI\Toolbar\Elements::class)
      ->disableOriginalConstructor()
      ->getMock();
    $elements
      ->expects($this->exactly(4))
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\Papaya\UI\Toolbar\Element::class));
    $commands = $this
      ->getMockBuilder(\Papaya\UI\Control\Command\Controller::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $changer->menu()->elements($elements);
    $changer->commands($commands);

    $this->assertEquals(
      '', $changer->getXML()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::dependency
   */
  public function testDependencyGetAfterSet() {
    $dependency = $this->getDependencyFixture();
    $changer = new Changer();
    $this->assertSame(
      $dependency, $changer->dependency($dependency)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::dependency
   */
  public function testDependencyGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Dependency::class, $changer->dependency()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::dependencies
   */
  public function testDependenciesGetAfterSet() {
    $dependencies = $this->createMock(\Papaya\CMS\Content\Page\Dependencies::class);
    $changer = new Changer();
    $this->assertSame(
      $dependencies, $changer->dependencies($dependencies)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::dependencies
   */
  public function testDependenciesGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Dependencies::class, $changer->dependencies()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::reference
   */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\CMS\Content\Page\Reference::class);
    $changer = new Changer();
    $this->assertSame(
      $reference, $changer->reference($reference)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::reference
   */
  public function testReferenceGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\Reference::class, $changer->reference()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::references
   */
  public function testReferencesGetAfterSet() {
    $references = $this->createMock(\Papaya\CMS\Content\Page\References::class);
    $changer = new Changer();
    $this->assertSame(
      $references, $changer->references($references)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::references
   */
  public function testReferencesGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Page\References::class, $changer->references()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::menu
   */
  public function testMenuGetAfterSet() {
    $menu = $this->createMock(\Papaya\UI\Toolbar::class);
    $changer = new Changer();
    $this->assertSame(
      $menu, $changer->menu($menu)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::menu
   */
  public function testMenuGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\UI\Toolbar::class, $changer->menu()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::commands
   */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder(\Papaya\UI\Control\Command\Controller::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changer = new Changer();
    $this->assertSame(
      $commands, $changer->commands($commands)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::commands
   */
  public function testCommandsGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\UI\Control\Command\Controller::class, $commands = $changer->commands()
    );
    $this->assertNotNull($commands['dependency_show']);
    $this->assertNotNull($commands['dependency_delete']);
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::listview
   */
  public function testListViewGetAfterSet() {
    $listview = $this
      ->getMockBuilder(ListView::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changer = new Changer();
    $this->assertSame(
      $listview, $changer->listview($listview)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::listview
   */
  public function testListViewGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      ListView::class, $changer->listview()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::synchronizations
   */
  public function testSynchronizationsGetAfterSet() {
    $synchronizations = $this->createMock(Synchronizations::class);
    $changer = new Changer();
    $this->assertSame(
      $synchronizations, $changer->synchronizations($synchronizations)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Dependency\Changer::synchronizations
   */
  public function testSynchronizationsGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      Synchronizations::class, $changer->synchronizations()
    );
  }

  /************************************
   * Fixtures
   ***********************************/

  /**
   * @param int $pageId
   * @param array $data
   * @return Changer
   */
  private function getChangerFixture($pageId = 0, array $data = array()) {
    $changer = new Changer();
    $changer->parameters(new \Papaya\Request\Parameters(array('page_id' => $pageId)));
    $changer->dependency($this->getDependencyFixture($data));
    $changer->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    return $changer;
  }

  private function getLanguageSwitchFixture() {
    $language = new \stdClass();
    $language->id = 1;
    $switch = $this->createMock(\Papaya\CMS\Administration\Languages\Selector::class);
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }

  /**
   * @param array $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Page\Dependency
   */
  private function getDependencyFixture(array $data = array()) {
    $record = $this->createMock(\Papaya\CMS\Content\Page\Dependency::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new \ArrayIterator($data))
      );
    $record
      ->expects($this->any())
      ->method('load')
      ->will(
        $this->returnValue(!empty($data))
      );
    $record
      ->expects($this->any())
      ->method('__isset')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($data) {
          return array_key_exists($name, $data);
        }
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($data) {
          if (array_key_exists($name, $data)) {
            return $data[$name];
          }
          return NULL;
        }
      );
    $record
      ->expects($this->any())
      ->method('isOrigin')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($id) {
          $isOrigin = array(
            21 => TRUE,
            42 => FALSE
          );
          return $isOrigin[$id];
        }
      );
    return $record;
  }

  private function getReferenceFixture(array $data = array()) {
    $record = $this->createMock(\Papaya\CMS\Content\Page\Reference::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new \ArrayIterator($data))
      );
    $record
      ->expects($this->any())
      ->method('load')
      ->will(
        $this->returnValue(!empty($data))
      );
    $record
      ->expects($this->any())
      ->method('__isset')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($data) {
          return array_key_exists($name, $data);
        }
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($data) {
          if (array_key_exists($name, $data)) {
            return $data[$name];
          }
          return NULL;
        }
      );
    return $record;
  }
}
