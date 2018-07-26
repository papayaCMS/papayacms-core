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

use Papaya\Configuration\Storage\Database;
use Papaya\Content\Configuration;
use Papaya\Database\Exception\Query;
use Papaya\Database\Access;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaConfigurationStorageDatabaseTest extends \PapayaTestCase {

  /**
  * @covers Database::records
  */
  public function testRecordsGetAfterSet() {
    $records = $this->createMock(Configuration::class);
    $storage = new Database();
    $this->assertSame($records, $storage->records($records));
  }

  /**
  * @covers Database::records
  */
  public function testRecordsGetImplicitCreate() {
    $storage = new Database();
    $this->assertInstanceOf(Configuration::class, $storage->records());
  }

  /**
  * @covers Database::load
  */
  public function testLoad() {
    $databaseAccess = $this
      ->getMockBuilder(Access::class)
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('errorHandler')
      ->with($this->isType('array'));

    $records = $this->createMock(Configuration::class);
    $records
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $records
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage = new Database();
    $storage->records($records);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers Database::handleError
  */
  public function testHandleErrorDevmode() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_DBG_DEVMODE' => TRUE
      )
    );
    $response = $this->createMock(\PapayaResponse::class);
    $response
      ->expects($this->once())
      ->method('sendHeader')
      ->with('X-Papaya-Error: Papaya\Database\Exception\Query: Sample Error Message');

    $storage = new Database();
    $storage->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $options,
          'response' => $response
        )
      )
    );

    $exception = new Query(
      'Sample Error Message', 0, Papaya\Message::SEVERITY_ERROR, ''
    );
    $storage->handleError($exception);
  }

  /**
  * @covers Database::handleError
  */
  public function testHandleErrorNoDevmodeSilent() {
    $response = $this->createMock(\PapayaResponse::class);
    $response
      ->expects($this->never())
      ->method('sendHeader');

    $storage = new Database();
    $storage->papaya(
      $this->mockPapaya()->application(
        array(
          'response' => $response
        )
      )
    );

    $exception = new Query(
      'Sample Error Message', 0, Papaya\Message::SEVERITY_ERROR, ''
    );
    $storage->handleError($exception);
  }

  /**
  * @covers Database::getIterator
  */
  public function testGetIterator() {
    $records = $this->createMock(Configuration::class);
    $records
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'SAMPLE_NAME' => array(
                'name' => 'SAMPLE_NAME',
                'value' => 'sample value'
              )
            )
          )
        )
      );
    $storage = new Database();
    $storage->records($records);
    $this->assertEquals(
      array('SAMPLE_NAME' => 'sample value'),
      \PapayaUtilArray::ensure($storage)
    );
  }

}
