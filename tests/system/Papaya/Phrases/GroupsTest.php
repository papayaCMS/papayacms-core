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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaPhrasesGroupsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetExistsExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $this->assertFalse(isset($groups['example']));
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetExistsAfterOffsetSetExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $groups['example'] = $this
      ->getMockBuilder(\Papaya\Phrases\Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertTrue(isset($groups['example']));
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetExistsAfterOffsetGetExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $groups['example'];
    $this->assertTrue(isset($groups['example']));
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetGetAfterOffsetSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $groups['example'] = $group = $this
      ->getMockBuilder(\Papaya\Phrases\Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($group, $groups['example']);
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testGetAfterOffsetSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $groups['example'] = $group = $this
      ->getMockBuilder(\Papaya\Phrases\Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($group, $groups->get('example'));
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetGetLazyCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $this->assertInstanceOf(\Papaya\Phrases\Group::class, $group = $groups['example']);
    $this->assertSame($group, $groups['example']);
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetUnset() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    $groups['example'] = $this
      ->getMockBuilder(\Papaya\Phrases\Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    unset($groups['example']);
    $this->assertFalse(isset($groups['example']));
  }

  /**
   * @covers \Papaya\Phrases\Groups
   */
  public function testOffsetUnsetOnNonExistingGroup() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\Phrases::class);
    $groups = new \Papaya\Phrases\Groups($phrases);
    unset($groups['example']);
    $this->assertFalse(isset($groups['example']));
  }


}
