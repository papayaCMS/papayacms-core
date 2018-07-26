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

use Papaya\Content\Page\Translation;
use Papaya\Content\Page\Translations;
use Papaya\Database\Result;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageTranslationsTest extends \PapayaTestCase {

  /**
  * @covers Translations::setTranslationsTableName
  */
  public function testSetTranslationsTable() {
    $list = new Translations();
    $list->setTranslationsTableName('success');
    $this->assertAttributeEquals(
      'success', '_translationsTableName', $list
    );
  }

  /**
  * @covers Translations::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(Result::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => '42',
            'lng_id' => '1',
            'topic_title' => 'Translated page title',
            'topic_trans_modified' => '123',
            'topic_trans_published' => NULL,
            'view_title' => 'Page view title'
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
    $this->assertAttributeEquals(
      array(
        '1' => array(
          'id' => '42',
          'language_id' => '1',
          'title' => 'Translated page title',
          'modified' => '123',
          'published' => NULL,
          'view' => 'Page view title'
        )
      ),
      '_records',
      $list
    );
  }

  /**
  * @covers Translations::load
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));
    $list = new Translations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertFalse($list->load(42));
  }

  /**
  * @covers Translations::getTranslation
  */
  public function testGetTranslation() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $list = new Translations();
    $list->setDatabaseAccess($databaseAccess);
    $translation = $list->getTranslation(42, 21);
    $this->assertInstanceOf(
      Translation::class, $translation
    );
  }
}
