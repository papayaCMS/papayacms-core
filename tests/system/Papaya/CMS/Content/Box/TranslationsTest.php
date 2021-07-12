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

namespace Papaya\CMS\Content\Box;

require_once __DIR__.'/../../../../../bootstrap.php';

class TranslationsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Box\Translations::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'box_id' => '42',
            'lng_id' => '1',
            'box_title' => 'Translated box title',
            'box_trans_modified' => '123',
            'box_trans_published' => NULL,
            'view_title' => 'Box view title'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    $list = new Translations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load(42));
    $this->assertEquals(
      array(
        '1' => array(
          'id' => '42',
          'language_id' => '1',
          'title' => 'Translated box title',
          'modified' => '123',
          'published' => NULL,
          'view' => 'Box view title'
        )
      ),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Box\Translations::getTranslation
   */
  public function testGetTranslation() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_box_trans', 'table_views', 'table_modules', 42, 21))
      ->will($this->returnValue(FALSE));
    $list = new Translations();
    $list->setDatabaseAccess($databaseAccess);
    $translation = $list->getTranslation(42, 21);
    $this->assertInstanceOf(
      Translation::class, $translation
    );
  }
}
