<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaContentTablesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithoutOptions() {
    $tables = new PapayaContentTables();
    $this->assertEquals(
      'topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptionsButDefaultValue() {
    $tables = new PapayaContentTables();
    $tables->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'papaya_topic', $tables->get(PapayaContentTables::PAGES)
    );
  }


  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptionsPrefixAlreadyAdded() {
    $tables = new PapayaContentTables();
    $tables->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'papaya_topic', $tables->get('papaya_'.PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptions() {
    $tables = new PapayaContentTables();
    $tables->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_DB_TABLEPREFIX' => 'foo'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'foo_topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptionsIsEmptyString() {
    $tables = new PapayaContentTables();
    $tables->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_DB_TABLEPREFIX' => ''
            )
          )
        )
      )
    );
    $this->assertEquals(
      'topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::getTables
  */
  public function testGetTables() {
    $this->assertInternalType('array', PapayaContentTables::getTables());
  }
}