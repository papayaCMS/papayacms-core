<?php
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
    $dependencies = $this->getMock('PapayaContentPageDependencies');
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->getMock('PapayaContentPageReferences');
    $references
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $pages = $this->getMock('PapayaContentPages');
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder('PapayaAdministrationPagesDependencyListview')
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
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
    $dependencies = $this->getMock('PapayaContentPageDependencies');
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(21);
    $references = $this->getMock('PapayaContentPageReferences');
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->getMock('PapayaContentPages');
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder('PapayaAdministrationPagesDependencyListview')
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->listview($listview);
    $changer->references($references);

    $elements = $this
      ->getMockBuilder('PapayaUiToolbarElements')
      ->disableOriginalConstructor()
      ->getMock();
    $elements
      ->expects($this->exactly(3))
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiToolbarElement'));
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

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

    $dependencies = $this->getMock('PapayaContentPageDependencies');
    $references = $this->getMock('PapayaContentPageReferences');
    $references
      ->expects($this->once())
      ->method('load')
      ->with(42);
    $pages = $this->getMock('PapayaContentPages');
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 0, 'language_id' => 1));
    $listview = $this
      ->getMockBuilder('PapayaAdministrationPagesDependencyListview')
      ->disableOriginalConstructor()
      ->getMock();
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $listview
      ->expects($this->once())
      ->method('pages')
      ->will($this->returnValue($pages));
    $changer->dependencies($dependencies);
    $changer->references($references);
    $changer->listview($listview);

    $elements = $this
      ->getMockBuilder('PapayaUiToolbarElements')
      ->disableOriginalConstructor()
      ->getMock();
    $elements
      ->expects($this->exactly(4))
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiToolbarElement'));
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

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
      'PapayaContentPageDependency', $changer->dependency()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $dependencies = $this->getMock('PapayaContentPageDependencies');
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
      'PapayaContentPageDependencies', $changer->dependencies()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaContentPageReference');
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
      'PapayaContentPageReference', $changer->reference()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::references
  */
  public function testReferencesGetAfterSet() {
    $references = $this->getMock('PapayaContentPageReferences');
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
      'PapayaContentPageReferences', $changer->references()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::menu
  */
  public function testMenuGetAfterSet() {
    $menu = $this->getMock('PapayaUiToolbar');
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
      'PapayaUiToolbar', $changer->menu()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::commands
  */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
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
      'PapayaUiControlCommandController', $commands = $changer->commands()
    );
    $this->assertNotNull($commands['dependency_show']);
    $this->assertNotNull($commands['dependency_delete']);
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::listview
  */
  public function testListviewGetAfterSet() {
    $listview = $this
      ->getMockBuilder('PapayaAdministrationPagesDependencyListview')
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
      'PapayaAdministrationPagesDependencyListview', $changer->listview()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyChanger::synchronizations
  */
  public function testSynchronizationsGetAfterSet() {
    $synchronizations = $this->getMock('PapayaAdministrationPagesDependencySynchronizations');
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
      'PapayaAdministrationPagesDependencySynchronizations', $changer->synchronizations()
    );
  }

  /************************************
  * Fixtures
  ************************************/

  private function getChangerFixture($pageId = 0, $data = array()) {
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
    $switch = $this->getMock('PapayaAdministrationLanguagesSwitch');
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }

  private function getDependencyFixture($data = array()) {
    $this->_recordData['dependency'] = $data;
    $record = $this->getMock('PapayaContentPageDependency');
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
      ->will($this->returnCallback(array($this, 'callbackDependencyDataExists')));
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackDependencyData')));
    $record
      ->expects($this->any())
      ->method('isOrigin')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackOriginIdStatus')));
    return $record;
  }

  public function callbackDependencyData($name) {
    if (array_key_exists($name, $this->_recordData['dependency'])) {
      return $this->_recordData['dependency'][$name];
    }
    return NULL;
  }

  public function callbackDependencyDataExists($name) {
    return array_key_exists($name, $this->_recordData['dependency']);
  }

  public function callbackOriginIdStatus($id) {
    $isOrigin = array(
      21 => TRUE,
      42 => FALSE
    );
    return $isOrigin[$id];
  }

  private function getReferenceFixture($data = array()) {
    $this->_recordData['reference'] = $data;
    $record = $this->getMock('PapayaContentPageReference');
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
      ->will($this->returnCallback(array($this, 'callbackReferenceDataExists')));
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackReferenceData')));
    return $record;
  }

  public function callbackReferenceData($name) {
    if (array_key_exists($name, $this->_recordData['reference'])) {
      return $this->_recordData['reference'][$name];
    }
    return NULL;
  }

  public function callbackReferenceDataExists($name) {
    return array_key_exists($name, $this->_recordData['reference']);
  }

}
