<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\Interfaces\Access {

  use Papaya\Database\Accessible;
  use Papaya\Database\Access as Accessor;
  use Papaya\Database\Accessible\Aggregation;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Accessible\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testGetDatabaseAccess() {
      $test = new Aggregation_TestProxy('read-sample');
      $access = $test->getDatabaseAccess();
      $this->assertSame(
        ['read-sample', NULL],
        $access->getDatabaseURIs()
      );
    }

    public function testGetDatabaseAccessWithWriteURI() {
      $test = new Aggregation_TestProxy('read-sample', 'write-sample');
      $access = $test->getDatabaseAccess();
      $this->assertSame(
        ['read-sample', 'write-sample'],
        $access->getDatabaseURIs()
      );
    }

    public function testGetDatabaseAccessWithApplicationObject() {
      $test = new AggregationWithApplication_TestProxy('read-sample');
      $test->papaya($application = $this->mockPapaya()->application());
      $access = $test->getDatabaseAccess();
      $this->assertSame(
        ['read-sample', NULL],
        $access->getDatabaseURIs()
      );
      $this->assertSame(
        $application,
        $access->papaya()
      );
    }

    public function testGetDatabaseAccessAfterSet() {
      $test = new Aggregation_TestProxy('read-sample');
      $test->setDatabaseAccess($databaseAccess = $this->createMock(Accessor::class));
      $this->assertSame($databaseAccess, $test->getDatabaseAccess());
    }
  }

  class Aggregation_TestProxy implements Accessible {
    use Aggregation;

    public function __construct($readURI, $writeURI = NULL) {
      $this->setDatabaseURIs($readURI, $writeURI);
    }
  }

  class AggregationWithApplication_TestProxy extends Aggregation_TestProxy implements \Papaya\Application\Access {
    use \Papaya\Application\Access\Aggregation;
  }

}
