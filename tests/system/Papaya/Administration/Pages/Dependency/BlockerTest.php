<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyBlockerTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::__construct
  */
  public function testConstructor() {
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertAttributeSame(
      42, '_pageId', $blocker
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::dependency
  */
  public function testDependencyGetAfterSet() {
    $dependency = $this->getMock('PapayaContentPageDependency');
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertSame($dependency, $blocker->dependency($dependency));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::dependency
  */
  public function testDependencyImplicitCreate() {
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertInstanceOf('PapayaContentPageDependency', $blocker->dependency());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::isSynchronized
  * @covers PapayaAdministrationPagesDependencyBlocker::prepare
  * @dataProvider provideSynchronizationData
  */
  public function testDependencyIsSynchronized($expected, $checkFor, $synchronizations) {
    $dependency = $this->getRecordFixture(
      array('synchronization' => $synchronizations)
    );
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $blocker->dependency($dependency);
    $this->assertEquals($expected, $blocker->isSynchronized($checkFor));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::isSynchronized
  * @covers PapayaAdministrationPagesDependencyBlocker::prepare
  */
  public function testDependencyLoadsOnlyOnce() {
    $dependency = $this->getRecordFixture(
      array(
        'synchronization' =>
           PapayaContentPageDependency::SYNC_PROPERTIES | PapayaContentPageDependency::SYNC_CONTENT
      ),
      1
    );
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $blocker->dependency($dependency);
    $this->assertTrue($blocker->isSynchronized(PapayaContentPageDependency::SYNC_PROPERTIES));
    $this->assertTrue($blocker->isSynchronized(PapayaContentPageDependency::SYNC_CONTENT));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::isSynchronized
  * @covers PapayaAdministrationPagesDependencyBlocker::prepare
  */
  public function testDependencyLoadsAgainIfRequested() {
    $dependency = $this->getRecordFixture(
      array(
        'synchronization' =>
           PapayaContentPageDependency::SYNC_PROPERTIES | PapayaContentPageDependency::SYNC_CONTENT
      ),
      2
    );
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $blocker->dependency($dependency);
    $this->assertTrue($blocker->isSynchronized(PapayaContentPageDependency::SYNC_PROPERTIES, TRUE));
    $this->assertTrue($blocker->isSynchronized(PapayaContentPageDependency::SYNC_CONTENT, TRUE));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::appendTo
  */
  public function testAppendTo() {
    $pages = $this->getMock('PapayaContentPages');
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => 21, 'language_id' => 1));
    $dependency = $this->getRecordFixture(
      array('originId' => 21)
    );
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
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
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Page dependency"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="no"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="m"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="tt[page_id]" value="21"/>'.
        '<input type="hidden" name="tt[confirmation]" value="3736585d7485423c0f483f7aff32ef68"/>'.
        '<field class="DialogFieldInformation" error="no">'.
          '<message image="locked.png">'.
            'This part of the page is synchronized with page "[...] #21".'.
          '</message>'.
        '</field>'.
        '<button type="submit" align="right">GoTo Origin Page</button>'.
      '</dialog-box>',
      $blocker->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $dependencies = $this->getMock('PapayaContentPageDependencies');
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertSame(
      $dependencies, $blocker->dependencies($dependencies)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::dependencies
  */
  public function testDependenciesGetImplicitCreate() {
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertInstanceOf(
      'PapayaContentPageDependencies', $blocker->dependencies()
    );
  }


  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::views
  */
  public function testViewsGetAfterSet() {
    $views = $this->getMock('PapayaContentViews');
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertSame(
      $views, $blocker->views($views)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::views
  */
  public function testViewsGetImplicitCreate() {
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertInstanceOf(
      'PapayaContentViews', $blocker->views()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::pages
  */
  public function testPagesGetAfterSet() {
    $pages = $this->getMock('PapayaContentPages');
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertSame(
      $pages, $blocker->pages($pages)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::pages
  */
  public function testPagesGetImplicitCreate() {
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertInstanceOf(
      'PapayaContentPages', $blocker->pages()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::counter
  */
  public function testCounterGetAfterSet() {
    $counter = $this
      ->getMockBuilder('PapayaAdministrationPagesDependencyCounter')
      ->disableOriginalConstructor()
      ->getMock();
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertSame(
      $counter, $blocker->counter($counter)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::counter
  */
  public function testCounterGetImplicitCreate() {
    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
    $this->assertInstanceOf(
      'PapayaAdministrationPagesDependencyCounter', $blocker->counter()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyBlocker::getSynchonizedViews
  */
  public function testGetSynchonizedViews() {
    $dependency = $this->getRecordFixture();
    $dependency
      ->expects($this->once())
      ->method('isOrigin')
      ->with(42)
      ->will($this->returnValue(TRUE));

    $dependencies = $this->getMock('PapayaContentPageDependencies');
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
                'synchronization' => PapayaContentPageDependency::SYNC_VIEW
              ),
              42 => array(
                'id' => 42,
                'view_id' => 31,
                'synchronization' => PapayaContentPageDependency::SYNC_CONTENT
              ),
              84 => array(
                'id' => 84,
                'view_id' => 32,
                'synchronization' =>
                  PapayaContentPageDependency::SYNC_VIEW &
                  PapayaContentPageDependency::SYNC_CONTENT
              )
            )
          )
        )
      );

    $views = $this->getMock('PapayaContentViews');
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


    $blocker = new PapayaAdministrationPagesDependencyBlocker(42);
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
      $blocker->getSynchonizedViews(2)
    );
  }

  /************************************
  * Fixtures
  ************************************/

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

  public function getRecordFixture($data = array(), $loadCounter = 0) {
    $this->_dependencyRecordData = $data;
    $record = $this->getMock('PapayaContentPageDependency');
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
      ->will($this->returnCallback(array($this, 'callbackRecordData')));
    return $record;
  }

  public function callbackRecordData($name) {
    return $this->_dependencyRecordData[$name];
  }

  /************************************
  * Data Provider
  ************************************/

  public static function provideSynchronizationData() {
    return array(
      'single value - TRUE' => array(
        TRUE,
        PapayaContentPageDependency::SYNC_PROPERTIES,
        PapayaContentPageDependency::SYNC_PROPERTIES
      ),
      'single value - FALSE' => array(
        FALSE,
        PapayaContentPageDependency::SYNC_PROPERTIES,
        PapayaContentPageDependency::SYNC_CONTENT
      ),
      'multiple values - TRUE' => array(
        TRUE,
        PapayaContentPageDependency::SYNC_PROPERTIES,
        PapayaContentPageDependency::SYNC_PROPERTIES | PapayaContentPageDependency::SYNC_CONTENT
      ),
      'multiple values - FALSE' => array(
        FALSE,
        PapayaContentPageDependency::SYNC_PROPERTIES,
        PapayaContentPageDependency::SYNC_CONTENT | PapayaContentPageDependency::SYNC_BOXES
      ),
    );
  }
}
