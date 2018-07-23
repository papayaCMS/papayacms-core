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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyChangerTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::getPageId
  * @covers PapayaAdministrationPagesDependencyChanger::prepare
  */
  public function testGetPageId() {
    $changer = $this->getChangerFixture(42);
    $changer->prepare();
    $this->assertEquals(42, $changer->getPageId());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::getOriginId
  * @covers PapayaAdministrationPagesDependencyChanger::prepare
  */
  public function testGetWithoutDependency() {
    $changer = $this->getChangerFixture(21);
    $changer->prepare();
    $this->assertEquals(21, $changer->getOriginId());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::getOriginId
  * @covers PapayaAdministrationPagesDependencyChanger::prepare
  */
  public function testGetWithDependency() {
    $changer = $this->getChangerFixture(42, array('originId' => 21));
    $changer->prepare();
    $this->assertEquals(21, $changer->getOriginId());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::prepare
  */
  public function testPrepareLoadsReferenceIfTargetIsDefined() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $changer->parameters(
      new PapayaRequestParameters(
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
  * @covers PapayaAdministrationPagesDependencyChanger::appendTo
  */
  public function testAppendToWithoutPageId() {
    $changer = $this->getChangerFixture();
    $this->assertEquals('', $changer->getXml());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::appendTo
  * @covers PapayaAdministrationPagesDependencyChanger::appendButtons
  */
  public function testAppendToWithOriginPage() {
    $changer = $this->getChangerFixture(21);
    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->createMock(PapayaContentPageReferences::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $pages = $this->createMock(PapayaContentPages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(PapayaAdministrationPagesDependencyListview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
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
  * @covers PapayaAdministrationPagesDependencyChanger::appendTo
  * @covers PapayaAdministrationPagesDependencyChanger::appendButtons
  */
  public function testAppendToWithDependency() {
    $changer = $this->getChangerFixture(42, array('id' => 42, 'originId' => 21));
    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->createMock(PapayaContentPageReferences::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->createMock(PapayaContentPages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(PapayaAdministrationPagesDependencyListview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->listview($listview);
    $changer->references($references);

    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->disableOriginalConstructor()
      ->getMock();
    $elements
      ->expects($this->exactly(3))
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiToolbarElement::class));
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $changer->menu()->elements($elements);
    $changer->commands($commands);

    $this->assertEquals('', $changer->getXml());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::appendTo
  * @covers PapayaAdministrationPagesDependencyChanger::appendButtons
  */
  public function testAppendToWithReference() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $changer->parameters(
      new PapayaRequestParameters(
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

    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $references = $this->createMock(PapayaContentPageReferences::class);
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->createMock(PapayaContentPages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 0, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder(PapayaAdministrationPagesDependencyListview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->references($references);
    $changer->listview($listview);

    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->disableOriginalConstructor()
      ->getMock();
    $elements
      ->expects($this->exactly(4))
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiToolbarElement::class));
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $changer->menu()->elements($elements);
    $changer->commands($commands);

    $this->assertEquals(
      '', $changer->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::dependency
  */
  public function testDependencyGetAfterSet() {
    $dependency = $this->getDependencyFixture();
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $dependency, $changer->dependency($dependency)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::dependency
  */
  public function testDependencyGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaContentPageDependency::class, $changer->dependency()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $dependencies, $changer->dependencies($dependencies)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::dependencies
  */
  public function testDependenciesGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaContentPageDependencies::class, $changer->dependencies()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(PapayaContentPageReference::class);
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $reference, $changer->reference($reference)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::reference
  */
  public function testReferenceGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaContentPageReference::class, $changer->reference()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::references
  */
  public function testReferencesGetAfterSet() {
    $references = $this->createMock(PapayaContentPageReferences::class);
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $references, $changer->references($references)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::references
  */
  public function testReferencesGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaContentPageReferences::class, $changer->references()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::menu
  */
  public function testMenuGetAfterSet() {
    $menu = $this->createMock(PapayaUiToolbar::class);
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $menu, $changer->menu($menu)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::menu
  */
  public function testMenuGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaUiToolbar::class, $changer->menu()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::commands
  */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $commands, $changer->commands($commands)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::commands
  */
  public function testCommandsGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaUiControlCommandController::class, $commands = $changer->commands()
    );
    $this->assertNotNull($commands['dependency_show']);
    $this->assertNotNull($commands['dependency_delete']);
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::listview
  */
  public function testListviewGetAfterSet() {
    $listview = $this
      ->getMockBuilder(PapayaAdministrationPagesDependencyListview::class)
      ->disableOriginalConstructor()
      ->getMock();
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $listview, $changer->listview($listview)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::listview
  */
  public function testListviewGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaAdministrationPagesDependencyListview::class, $changer->listview()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::synchronizations
  */
  public function testSynchronizationsGetAfterSet() {
    $synchronizations = $this->createMock(PapayaAdministrationPagesDependencySynchronizations::class);
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertSame(
      $synchronizations, $changer->synchronizations($synchronizations)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::synchronizations
  */
  public function testSynchronizationsGetImplicitCreate() {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $this->assertInstanceOf(
      PapayaAdministrationPagesDependencySynchronizations::class, $changer->synchronizations()
    );
  }

  /************************************
   * Fixtures
   ***********************************/

   /**
   * @param int $pageId
   * @param array $data
   * @return PapayaAdministrationPagesDependencyChanger
   */
  private function getChangerFixture($pageId = 0, array $data = array()) {
    $changer = new PapayaAdministrationPagesDependencyChanger();
    $changer->parameters(new PapayaRequestParameters(array('page_id' => $pageId)));
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
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageDependency
   */
  private function getDependencyFixture(array $data = array()) {
    $record = $this->createMock(PapayaContentPageDependency::class);
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
    $record = $this->createMock(PapayaContentPageReference::class);
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
