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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaObjectOptionsDefinedTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\BaseObject\Options\Defined::toArray
  */
  public function testToArray() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->assertEquals(
      array(
       'VALID_OPTION' => TRUE
      ),
      $options->toArray()
    );
  }
  /**
  * @covers \Papaya\BaseObject\Options\Defined::count
  */
  public function testCount() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->assertCount(1, $options);
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_write
  */
  public function testSetOption() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $options->validOption = FALSE;
    $this->assertAttributeEquals(
      array(
       'VALID_OPTION' => FALSE
      ),
      '_options',
      $options
    );
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_write
  */
  public function testSetOptionExpectingException() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->expectException(InvalidArgumentException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $options->invalidOption = FALSE;
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_read
  */
  public function testGetOptionAfterSet() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $options->validOption = FALSE;
    $this->assertFalse($options->validOption);
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_read
  */
  public function testGetOptionReadingDefault() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->assertTrue($options->validOption);
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_read
  */
  public function testGetOptionExpectingException() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->expectException(InvalidArgumentException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $options->invalidOption;
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_exists
  */
  public function testIssetOptionExpectingTrue() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->assertTrue(isset($options->validOption));
  }

  /**
  * @covers \Papaya\BaseObject\Options\Defined::_exists
  */
  public function testIssetOptionExpectingFalse() {
    $options = new \PapayaObjectOptionsDefined_TestProxy();
    $this->assertFalse(isset($options->invalidOption));
  }

}

/**
 * @property bool validOption
 */
class PapayaObjectOptionsDefined_TestProxy extends \Papaya\BaseObject\Options\Defined {

  protected $_definitions = array(
    'VALID_OPTION' => array(TRUE, FALSE)
  );

}
