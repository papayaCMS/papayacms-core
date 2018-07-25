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

use Papaya\Content\Domains;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentDomainsTest extends PapayaTestCase {

  /**
  * @covers Domains::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'domain_id' => 21,
            'domain_hostname' => 'www.sample.tld',
            'domain_protocol' => 0,
            'domain_mode' => 4
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_'.PapayaContentTables::DOMAINS)
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Domains();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load());
    $this->assertEquals(
      array(
        21 => array('id' => 21, 'host' => 'www.sample.tld', 'scheme' => 0, 'mode' => 4)
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers Domains::load
  */
  public function testLoadWithFilter() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'domain_id' => 21,
            'domain_hostname' => 'www.sample.tld',
            'domain_protocol' => 0,
            'domain_mode' => 4
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('domain_mode' => 4))
      ->will($this->returnValue(" domain_mode = '4'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_'.PapayaContentTables::DOMAINS)
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Domains();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('mode' => 4)));
    $this->assertEquals(
      array(
        21 => array('id' => 21, 'host' => 'www.sample.tld', 'scheme' => 0, 'mode' => 4)
      ),
      $pages->toArray()
    );
  }
}
