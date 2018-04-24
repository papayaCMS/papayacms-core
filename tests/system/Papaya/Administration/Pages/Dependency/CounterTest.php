<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyCounterTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencyCounter::__construct
  */
  public function testConstructor() {
    $counter = new PapayaAdministrationPagesDependencyCounter(42);
    $this->assertAttributeEquals(42, '_pageId', $counter);
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCounter::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'name' => 'dependencies',
            'counter' => 21
          ),
          array(
            'name' => 'references',
            'counter' => 23
          )
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGE_DEPENDENCIES,
          PapayaContentTables::PAGE_REFERENCES,
          42
        )
      )
      ->will(
        $this->returnValue($databaseResult)
      );

    $counter = new PapayaAdministrationPagesDependencyCounter(42);
    $counter->setDatabaseAccess($databaseAccess);
    $this->assertTrue($counter->load());
    $this->assertAttributeEquals(
      array(
        'dependencies' => 21,
        'references' => 23,
      ),
      '_countings',
      $counter
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCounter::load
  */
  public function testLoadFailedExpectingFalse() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));

    $counter = new PapayaAdministrationPagesDependencyCounter(42);
    $counter->setDatabaseAccess($databaseAccess);
    $this->assertFalse($counter->load());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCounter::getDependencies
  * @covers PapayaAdministrationPagesDependencyCounter::lazyLoad
  */
  public function testGetDependencies() {
    $counter = new PapayaAdministrationPagesDependencyCounter_TestProxy(42);
    $this->assertEquals(
      21, $counter->getDependencies()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCounter::getReferences
  * @covers PapayaAdministrationPagesDependencyCounter::lazyLoad
  */
  public function testGetReferences() {
    $counter = new PapayaAdministrationPagesDependencyCounter_TestProxy(42);
    $this->assertEquals(
      23, $counter->getReferences()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCounter::getLabel
  * @covers PapayaAdministrationPagesDependencyCounter::lazyLoad
  * @dataProvider provideCountingsForGetLabel
  */
  public function testGetLabel($expected, $dependencies, $references) {
    $counter = new PapayaAdministrationPagesDependencyCounter_TestProxy(42);
    $counter->countingSamples = array(
      'dependencies' => $dependencies,
      'references' => $references
    );
    $this->assertEquals(
      $expected, $counter->getLabel()
    );
  }

  public function testGetLabelWithAllParameters() {
    $counter = new PapayaAdministrationPagesDependencyCounter_TestProxy(42);
    $counter->countingSamples = array(
      'dependencies' => 21,
      'references' => 23
    );
    $this->assertEquals(
      '_{21:23}', $counter->getLabel(':', '_{', '}')
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideCountingsForGetLabel() {
    return array(
      'no dependencies, no references' => array('', 0, 0),
      'no dependencies, 5 references' => array(' (5)', 0, 5),
      '2 dependencies, no references' => array(' (2/0)', 2, 0),
      '3 dependencies, 7 references' => array(' (3/7)', 3, 7)
    );
  }
}

class PapayaAdministrationPagesDependencyCounter_TestProxy
  extends PapayaAdministrationPagesDependencyCounter {

  public $countingSamples = array(
    'dependencies' => 21,
    'references' => 23
  );

  public function load() {
    if (is_array($this->countingSamples)) {
      $this->_countings = $this->countingSamples;
      return TRUE;
    }
    return FALSE;
  }
}
