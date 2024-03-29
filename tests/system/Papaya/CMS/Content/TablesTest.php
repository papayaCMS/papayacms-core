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

namespace Papaya\CMS\Content;

require_once __DIR__.'/../../../../bootstrap.php';

class TablesTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Tables::get
   */
  public function testGetWithoutOptions() {
    $tables = new Tables();
    $this->assertEquals(
      'topic', $tables->get(\Papaya\CMS\Content\Tables::PAGES)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Tables::get
   */
  public function testGetWithOptionsButDefaultValue() {
    $tables = new Tables();
    $tables->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'papaya_topic', $tables->get(\Papaya\CMS\Content\Tables::PAGES)
    );
  }


  /**
   * @covers \Papaya\CMS\Content\Tables::get
   */
  public function testGetWithOptionsPrefixAlreadyAdded() {
    $tables = new Tables();
    $tables->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'papaya_topic', $tables->get('papaya_'.\Papaya\CMS\Content\Tables::PAGES)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Tables::get
   */
  public function testGetWithOptions() {
    $tables = new Tables();
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
      'foo_topic', $tables->get(\Papaya\CMS\Content\Tables::PAGES)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Tables::get
   */
  public function testGetWithOptionsIsEmptyString() {
    $tables = new Tables();
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
      'topic', $tables->get(\Papaya\CMS\Content\Tables::PAGES)
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Tables::getTables
   */
  public function testGetTables() {
    $this->assertIsArray(\Papaya\CMS\Content\Tables::getTables());
  }
}
