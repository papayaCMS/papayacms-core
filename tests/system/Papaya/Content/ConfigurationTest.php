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

namespace Papaya\Content;

require_once __DIR__.'/../../../bootstrap.php';

class ConfigurationTest extends \Papaya\TestCase {

  /**
   * @covers Configuration::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'opt_name' => 'SAMPLE_OPTION',
            'opt_value' => '42'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_options'))
      ->will($this->returnValue($databaseResult));
    $configuration = new Configuration();
    $configuration->setDatabaseAccess($databaseAccess);
    $this->assertTrue($configuration->load());
    $this->assertAttributeEquals(
      array(
        'SAMPLE_OPTION' => array(
          'name' => 'SAMPLE_OPTION',
          'value' => '42'
        )
      ),
      '_records',
      $configuration
    );
  }
}
