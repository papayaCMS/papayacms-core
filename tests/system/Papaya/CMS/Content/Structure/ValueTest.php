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

namespace Papaya\CMS\Content\Structure;

require_once __DIR__.'/../../../../../bootstrap.php';

class ValueTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Structure\Value::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $value = new Value($group);
    $this->assertSame($group, $value->getGroup());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Value::getIdentifier
   */
  public function testGetIdentifier() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getIdentifier')
      ->will($this->returnValue('PAGE/GROUP'));
    $value = new Value($group);
    $value->name = 'VALUE';
    $this->assertEquals(
      'PAGE/GROUP/VALUE', $value->getIdentifier()
    );
  }
}
