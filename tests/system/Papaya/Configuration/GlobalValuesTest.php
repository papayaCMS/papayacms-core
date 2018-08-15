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

namespace Papaya\Configuration;

require_once __DIR__.'/../../../bootstrap.php';

class GlobalValuesTest extends \PapayaTestCase {

  /**
   * @covers GlobalValues::get
   */
  public function testGetReadingConstant() {
    $config = new GlobalValues_TestProxy();
    $this->assertNotEquals(
      'failed', $config->get('PAPAYA_INCLUDE_PATH')
    );
  }

  /**
   * @covers GlobalValues::get
   */
  public function testGetCallingParentMethod() {
    $config = new GlobalValues_TestProxy();
    $this->assertEquals(
      42, $config->get('SAMPLE_INT')
    );
  }

  /**
   * @covers GlobalValues::get
   */
  public function testSetConstantShouldBeIgnored() {
    $config = new GlobalValues_TestProxy();
    $config->set('PAPAYA_INCLUDE_PATH', 21);
    $this->assertNotEquals(
      21, $config->get('PAPAYA_INCLUDE_PATH')
    );
  }

  /**
   * @covers GlobalValues::has
   */
  public function testHasWithConstantExpectingTrue() {
    $config = new GlobalValues_TestProxy();
    $this->assertTrue($config->has('PAPAYA_INCLUDE_PATH'));
  }

  /**
   * @covers GlobalValues::has
   */
  public function testHasExpectingTrue() {
    $config = new GlobalValues_TestProxy();
    $this->assertTrue($config->has('SAMPLE_INT'));
  }

  /**
   * @covers GlobalValues::defineConstants
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testDefineConstants() {
    $config = new GlobalValues_TestProxy();
    $this->assertFalse(defined('SAMPLE_INT'));
    $config->defineConstants();
    $this->assertTrue(defined('SAMPLE_INT'));
  }
}

class GlobalValues_TestProxy extends GlobalValues {

  public function __construct() {
    parent::__construct(
      array(
        'SAMPLE_INT' => 42,
        'PAPAYA_INCLUDE_PATH' => 'failed'
      )
    );
  }
}
