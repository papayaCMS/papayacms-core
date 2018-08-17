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

namespace Papaya\UI\Dialog\Field\Input;
require_once __DIR__.'/../../../../../../bootstrap.php';

class PasswordTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Password::__construct
   */
  public function testConstructorCreatesDefaultFilter() {
    $field = new Password('Caption', 'fieldname');
    $field->setMandatory(TRUE);
    $this->assertInstanceOf(\Papaya\Filter\Password::class, $field->getFilter());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Password::__construct
   */
  public function testConstructorAttachingFilter() {
    $filter = $this->createMock(\Papaya\Filter::class);
    $field = new Password('Caption', 'fieldname', 42, $filter);
    $field->setMandatory(TRUE);
    $this->assertSame($filter, $field->getFilter());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Password::getCurrentValue
   */
  public function testGetCurrentValueIgnoresDefaultValue() {
    $field = new Password('Caption', 'fieldname');
    $field->setDefaultValue('not ok');
    $this->assertEmpty($field->getCurrentValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Password::getCurrentValue
   */
  public function testGetCurrentValueIgnoreData() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(1))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array())));
    $dialog
      ->expects($this->never())
      ->method('data');
    $field = new Password('Caption', 'foo');
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEmpty($field->getCurrentValue());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Password::getCurrentValue
   */
  public function testGetCurrentValueReadParameter() {
    $dialog = $this
      ->getMockBuilder(\Papaya\UI\Dialog::class)
      ->setConstructorArgs(array(new \stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => 'success'))));
    $field = new Password('Caption', 'foo');
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEquals('success', $field->getCurrentValue());
  }

  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(\Papaya\UI\Dialog\Fields::class);
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
