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

namespace Papaya\CMS\Configuration\Storage {

  use Papaya\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Configuration\Storage\Database
   */
  class DatabaseTest extends TestCase {

    public function testRecordsGetAfterSet() {
      $records = $this->createMock(\Papaya\CMS\Content\Configuration::class);
      $storage = new Database();
      $this->assertSame($records, $storage->records($records));
    }

    public function testRecordsGetImplicitCreate() {
      $storage = new Database();
      $this->assertInstanceOf(\Papaya\CMS\Content\Configuration::class, $storage->records());
    }

    public function testLoad() {
      $databaseAccess = $this
        ->getMockBuilder(\Papaya\Database\Access::class)
        ->disableOriginalConstructor()
        ->getMock();
      $databaseAccess
        ->expects($this->once())
        ->method('errorHandler')
        ->with($this->isType('callable'));

      $records = $this->createMock(\Papaya\CMS\Content\Configuration::class);
      $records
        ->expects($this->once())
        ->method('getDatabaseAccess')
        ->willReturn($databaseAccess);
      $records
        ->expects($this->once())
        ->method('load')
        ->willReturn(TRUE);
      $storage = new Database();
      $storage->records($records);
      $this->assertTrue($storage->load());
    }

    public function testHandleErrorDevelopmentMode() {
      $options = $this->mockPapaya()->options(
        [
          'PAPAYA_DBG_DEVMODE' => TRUE
        ]
      );
      $response = $this->createMock(\Papaya\Response::class);
      $response
        ->expects($this->once())
        ->method('sendHeader')
        ->with('X-Papaya-Error: Papaya\Database\Exception\QueryFailed: Sample Error Message');

      $storage = new Database();
      $storage->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $options,
            'response' => $response
          ]
        )
      );

      $exception = new \Papaya\Database\Exception\QueryFailed(
        'Sample Error Message', 0, \Papaya\Message::SEVERITY_ERROR, ''
      );
      $storage->handleError($exception);
    }

    public function testHandleErrorNoDevelopmentModeSilent() {
      $response = $this->createMock(\Papaya\Response::class);
      $response
        ->expects($this->never())
        ->method('sendHeader');

      $storage = new Database();
      $storage->papaya(
        $this->mockPapaya()->application(
          [
            'response' => $response
          ]
        )
      );

      $exception = new \Papaya\Database\Exception\QueryFailed(
        'Sample Error Message', 0, \Papaya\Message::SEVERITY_ERROR, ''
      );
      $storage->handleError($exception);
    }

    public function testGetIterator() {
      $records = $this->createMock(\Papaya\CMS\Content\Configuration::class);
      $records
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn(
          new \ArrayIterator(
            [
              'SAMPLE_NAME' => [
                'name' => 'SAMPLE_NAME',
                'value' => 'sample value'
              ]
            ]
          )
        );
      $storage = new Database();
      $storage->records($records);
      $this->assertEquals(
        ['SAMPLE_NAME' => 'sample value'],
        \Papaya\Utility\Arrays::ensure($storage)
      );
    }
  }
}
