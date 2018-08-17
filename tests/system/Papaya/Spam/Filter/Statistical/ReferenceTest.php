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

namespace Papaya\Spam\Filter\Statistical;

require_once __DIR__.'/../../../../../bootstrap.php';

class ReferenceTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Spam\Filter\Statistical\Reference::load
   * @covers \Papaya\Spam\Filter\Statistical\Reference::loadTotals
   * @covers \Papaya\Spam\Filter\Statistical\Reference::getHamCount
   * @covers \Papaya\Spam\Filter\Statistical\Reference::getSpamCount
   */
  public function testLoad() {
    $totalsDatabaseResult = $this->createMock(\Papaya\Database\Result::class);
    $totalsDatabaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'spamcategory_ident' => 'ham',
            'text_count' => '42'
          ),
          array(
            'spamcategory_ident' => 'spam',
            'text_count' => '2142'
          ),
          NULL
        )
      );
    $recordsDatabaseResult = $this->createMock(\Papaya\Database\Result::class);
    $recordsDatabaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'spamword' => 'papaya',
            'spamword_count' => '3',
            'spamcategory_ident' => 'ham'
          ),
          array(
            'spamword' => 'papaya',
            'spamword_count' => '1',
            'spamcategory_ident' => 'spam'
          ),
          array(
            'spamword' => 'poker',
            'spamword_count' => '13',
            'spamcategory_ident' => 'spam'
          ),
          NULL
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('spamword'), $this->equalTo(array('foo', 'bar')))
      ->will($this->returnValue("spamword IN ('foo', 'bar')"));
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('queryFmt')
      ->with($this->isType('string'), $this->isType('array'))
      ->will(
        $this->onConsecutiveCalls(
          $recordsDatabaseResult, $totalsDatabaseResult
        )
      );

    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertTrue($reference->load(array('foo', 'bar'), 2));
    $this->assertAttributeEquals(
      array(
        'papaya' => array(
          'word' => 'papaya',
          'ham' => 3,
          'spam' => 1
        ),
        'poker' => array(
          'word' => 'poker',
          'ham' => 0,
          'spam' => 13
        )
      ),
      '_records',
      $reference
    );
    $this->assertEquals(42, $reference->getHamCount());
    $this->assertEquals(2142, $reference->getSpamCount());
  }

  /**
   * @covers \Papaya\Spam\Filter\Statistical\Reference::load
   */
  public function testLoadWithEmptyWordList() {
    $reference = new Reference();
    $this->assertFalse($reference->load(array(), 2));
  }

  /**
   * @covers \Papaya\Spam\Filter\Statistical\Reference::load
   */
  public function testLoadWithDatabaseError() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('spamword'), $this->equalTo(array('foo', 'bar')))
      ->will($this->returnValue("spamword IN ('foo', 'bar')"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(FALSE));

    $reference = new Reference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->load(array('foo', 'bar'), 2));
  }
}
