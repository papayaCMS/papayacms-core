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
use Papaya\Administration\Pages\Dependency\Changer;
use Papaya\Administration\Pages\Dependency\Listview;
use Papaya\Administration\Pages\Dependency\Synchronizations;
use Papaya\Content\Page\Dependencies;
use Papaya\Content\Page\Dependency;
use Papaya\Content\Page\Reference;
use Papaya\Content\Page\References;
use Papaya\Content\Pages;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyChangerTest extends \PapayaTestCase {

  /**
  * @covers Changer::getPageId
  * @covers Changer::prepare
  */
  public function testGetPageId() {
    $changer = $this->getChangerFixture(42);
    $changer->prepare();
    $this->assertEquals(42, $changer->getPageId());
  }

  /**
  * @covers Changer::getOriginId
  * @covers Changer::prepare
  */
  public function testGetWithoutDependency() {
    $changer = $this->getChangerFixture(21);
    $changer->prepare();
    $this->assertEquals(21, $changer->getOriginId());
  }

  /**
  * @covers Changer::getOriginId
  * @covers Changer::prepare
  */
  public function testGetWithDependency() {
    $changer = $this->getChangerFixture(42, array('originId' => 21));
    $changer->prepare();
    $this->assertEquals(21, $changer->getOriginId());
  }

  /**
  * @covers Changer::prepare
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
  * @covers Changer::appendTo
  */
  public function testAppendToWithoutPageId() {
    $changer = $this->getChangerFixture();
    $this->assertEquals('', $changer->getXml());
  }

  /**
  * @covers Changer::appendTo
  * @covers Changer::appendButtons
  */
  public function testAppendToWithOriginPage() {
    $changer = $this->getChangerFixture(21);
    $dependencies = $this->createMock(Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->createMock(References::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(Listview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->references($references);
    $changer->listview($listview);
    $this->assertEquals('', $changer->getXml());
  }

  /**
  * @covers Changer::appendTo
  * @covers Changer::appendButtons
  */
  public function testAppendToWithDependency() {
    $changer = $this->getChangerFixture(42, array('id' => 42, 'originId' => 21));
    $dependencies = $this->createMock(Dependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->createMock(References::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(Listview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
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
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));

    $changer->menu()->elements($elements);
    $changer->commands($commands);

    $this->assertEquals('', $changer->getXml());
  }

  /**
  * @covers Changer::appendTo
  * @covers Changer::appendButtons
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

    $dependencies = $this->createMock(Dependencies::class);
    $references = $this->createMock(References::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 0, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(Listview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
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
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));

    $changer->menu()->elements($elements);
    $changer->commands($commands);

    $this->assertEquals(
      '', $changer->getXml()
    );
  }

  /**
  * @covers Changer::dependency
  */
  public function testDependencyGetAfterSet() {
    $dependency = $this->getDependencyFixture();
    $changer = new Changer();
    $this->assertSame(
      $dependency, $changer->dependency($dependency)
    );
  }

  /**
  * @covers Changer::dependency
  */
  public function testDependencyGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      Dependency::class, $changer->dependency()
    );
  }

  /**
  * @covers Changer::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $dependencies = $this->createMock(Dependencies::class);
    $changer = new Changer();
    $this->assertSame(
      $dependencies, $changer->dependencies($dependencies)
    );
  }

  /**
  * @covers Changer::dependencies
  */
  public function testDependenciesGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      Dependencies::class, $changer->dependencies()
    );
  }

  /**
  * @covers Changer::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(Reference::class);
    $changer = new Changer();
    $this->assertSame(
      $reference, $changer->reference($reference)
    );
  }

  /**
  * @covers Changer::reference
  */
  public function testReferenceGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      Reference::class, $changer->reference()
    );
  }

  /**
  * @covers Changer::references
  */
  public function testReferencesGetAfterSet() {
    $references = $this->createMock(References::class);
    $changer = new Changer();
    $this->assertSame(
      $references, $changer->references($references)
    );
  }

  /**
  * @covers Changer::references
  */
  public function testReferencesGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      References::class, $changer->references()
    );
  }

  /**
  * @covers Changer::menu
  */
  public function testMenuGetAfterSet() {
    $menu = $this->createMock(\Papaya\UI\Toolbar::class);
    $changer = new Changer();
    $this->assertSame(
      $menu, $changer->menu($menu)
    );
  }

  /**
  * @covers Changer::menu
  */
  public function testMenuGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      \Papaya\UI\Toolbar::class, $changer->menu()
    );
  }

  /**
  * @covers Changer::commands
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
  * @covers Changer::commands
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
  * @covers Changer::listview
  */
  public function testListviewGetAfterSet() {
    $listview = $this
      ->getMockBuilder(Listview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changer = new Changer();
    $this->assertSame(
      $listview, $changer->listview($listview)
    );
  }

  /**
  * @covers Changer::listview
  */
  public function testListviewGetImplicitCreate() {
    $changer = new Changer();
    $this->assertInstanceOf(
      Listview::class, $changer->listview()
    );
  }

  /**
  * @covers Changer::synchronizations
  */
  public function testSynchronizationsGetAfterSet() {
    $synchronizations = $this->createMock(Synchronizations::class);
    $changer = new Changer();
    $this->assertSame(
      $synchronizations, $changer->synchronizations($synchronizations)
    );
  }

  /**
  * @covers Changer::synchronizations
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
    $language = new stdClass();
    $language->id = 1;
    $switch = $this->createMock(Selector::class);
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|Dependency
   */
  private function getDependencyFixture(array $data = array()) {
    $record = $this->createMock(Dependency::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator($data))
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
        function($name) use ($data) {
          return array_key_exists($name, $data);
        }
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function($name) use ($data) {
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
        function($id) {
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
    $record = $this->createMock(Reference::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator($data))
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
        function($name) use ($data) {
          return array_key_exists($name, $data);
        }
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function($name) use ($data) {
          if (array_key_exists($name, $data)) {
            return $data[$name];
          }
          return NULL;
        }
      );
    return $record;
  }
}
