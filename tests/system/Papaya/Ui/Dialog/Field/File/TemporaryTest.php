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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldFileTemporaryTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::__construct
   */
  public function testConstructor() {
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $this->assertEquals('Caption', $field->getCaption());
    $this->assertEquals('name', $field->getName());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::appendTo
   */
  public function testAppendTo() {
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldFileTemporary" error="no">
        <input type="file" name="name"/>
      </field>',
      $field->getXml()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::validate
   */
  public function testValidateWithFileExpectingTrue() {
    $file = $this
      ->getMockBuilder(\Papaya\Request\Parameter\File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue(TRUE));
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $field->file($file);
    $this->assertTrue($field->validate());
    $this->assertTrue($field->validate());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::validate
   */
  public function testValidateWithoutOptionalFileExpectingTrue() {
    $file = $this
      ->getMockBuilder(\Papaya\Request\Parameter\File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue(FALSE));
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $field->file($file);
    $this->assertTrue($field->validate());
    $this->assertTrue($field->validate());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::validate
   */
  public function testValidateWithoutManatoryFileExpectingFalse() {
    $file = $this
      ->getMockBuilder(\Papaya\Request\Parameter\File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue(FALSE));
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $field->setMandatory(TRUE);
    $field->file($file);
    $this->assertFalse($field->validate());
    $this->assertFalse($field->validate());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::collect
   */
  public function testCollectReturnsTrue() {
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $this->assertTrue($field->collect());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::file
   */
  public function testFileGetAfterSet() {
    $file = $this
      ->getMockBuilder(\Papaya\Request\Parameter\File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $field->file($file);
    $this->assertSame($file, $field->file());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\File\Temporary::file
   */
  public function testFileGetWithImplicitCreate() {
    $field = new \Papaya\UI\Dialog\Field\File\Temporary('Caption', 'name');
    $this->assertInstanceOf(\Papaya\Request\Parameter\File::class, $field->file());
  }
}
