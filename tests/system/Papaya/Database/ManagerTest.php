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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseManagerTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Database\Manager::setConfiguration
  */
  public function testSetConfiguration() {
    $manager = new \Papaya\Database\Manager();
    $options = $this->mockPapaya()->options();
    $manager->setConfiguration($options);
    $this->assertAttributeSame(
      $options, '_configuration', $manager
    );
  }

  /**
  * @covers \Papaya\Database\Manager::getConfiguration
  */
  public function testGetConfigutation() {
    $manager = new \Papaya\Database\Manager();
    $options = $this->mockPapaya()->options();
    $manager->setConfiguration($options);
    $this->assertSame(
      $options, $manager->getConfiguration()
    );
  }

  /**
  * @covers \Papaya\Database\Manager::setConnector
  * @covers \Papaya\Database\Manager::_getConnectorUris
  */
  public function testSetConnector() {
    $manager = new \Papaya\Database\Manager();
    $connector = new db_simple();
    $manager->setConnector($connector, 'READ', 'WRITE');
    $this->assertAttributeSame(
      array("READ\nWRITE" => $connector),
      '_connectors',
      $manager
    );
  }

  /**
  * @covers \Papaya\Database\Manager::setConnector
  * @covers \Papaya\Database\Manager::_getConnectorUris
  */
  public function testSetConnectorWithoutWriteUri() {
    $manager = new \Papaya\Database\Manager();
    $connector = new db_simple();
    $manager->setConnector($connector, 'READ');
    $this->assertAttributeSame(
      array("READ\nREAD" => $connector),
      '_connectors',
      $manager
    );
  }

  /**
  * @covers \Papaya\Database\Manager::setConnector
  * @covers \Papaya\Database\Manager::_getConnectorUris
  */
  public function testSetConnectorWithoutReadUri() {
    $manager = new \Papaya\Database\Manager();
    $connector = new db_simple();
    $manager->setConfiguration(
      $this->mockPapaya()->options(
        array(
          'PAPAYA_DB_URI' => 'read_default',
          'PAPAYA_DB_URI_WRITE' => 'write_default'
        )
      )
    );
    $manager->setConnector($connector);
    $this->assertAttributeSame(
      array("read_default\nwrite_default" => $connector),
      '_connectors',
      $manager
    );
  }

  /**
  * @covers \Papaya\Database\Manager::getConnector
  */
  public function testGetConnector() {
    $manager = new \Papaya\Database\Manager();
    $connector = new db_simple();
    $manager->setConnector($connector, 'READ');
    $this->assertSame(
      $connector,
      $manager->getConnector('READ')
    );
  }

  /**
  * @covers \Papaya\Database\Manager::getConnector
  */
  public function testGetConnectorImplicitCreate() {
    $manager = new \Papaya\Database\Manager();
    $this->assertInstanceOf(
      'db_simple',
      $manager->getConnector('READ')
    );
  }

  /**
  * @covers \Papaya\Database\Manager::close
  */
  public function testClose() {
    $manager = new \Papaya\Database\Manager();
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('close');
    $manager->setConnector($connector, 'READ');
    $manager->close();
  }

  /**
  * @covers \Papaya\Database\Manager::createDatabaseAccess
  */
  public function testCreateDatabaseAccess() {
    $manager = new \Papaya\Database\Manager();
    $manager->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Database\Access::class, $databaseAccess = $manager->createDatabaseAccess(new stdClass)
    );
    $this->assertSame(
      $papaya, $databaseAccess->papaya()
    );
  }

  /**
  * @covers \Papaya\Database\Manager::createDatabaseAccess
  */
  public function testCreateDatabaseAccessWithUris() {
    $manager = new \Papaya\Database\Manager();
    $manager->papaya($papaya = $this->mockPapaya()->application());
    $databaseAccess = $manager->createDatabaseAccess(new stdClass, 'READ_SAMPLE', 'WRITE_SAMPLE');
    $this->assertAttributeEquals(
      'READ_SAMPLE', '_uriRead', $databaseAccess
    );
    $this->assertAttributeEquals(
      'WRITE_SAMPLE', '_uriWrite', $databaseAccess
    );
  }
}

