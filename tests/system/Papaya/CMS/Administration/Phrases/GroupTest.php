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

namespace Papaya\CMS\Administration\Phrases;
require_once __DIR__.'/../../../../../bootstrap.php';

class GroupTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Phrases\Group
   */
  public function testGet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Administration\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\CMS\Administration\Phrases::class);
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('Test', '#default')
      ->will($this->returnValue('Success'));
    $group = new Group($phrases, '#default');
    $phrase = $group->get('Test');
    $this->assertInstanceOf(\Papaya\UI\Text\Translated::class, $phrase);
    $this->assertEquals('Success', (string)$phrase);
  }

  /**
   * @covers \Papaya\CMS\Administration\Phrases\Group
   */
  public function testGetList() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Administration\Phrases $phrases */
    $phrases = $this->createMock(\Papaya\CMS\Administration\Phrases::class);
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('Test', '#default')
      ->will($this->returnValue('Success'));
    $group = new Group($phrases, '#default');
    $phraseList = $group->getList(array('One' => 'Test'));
    $this->assertInstanceOf(\Papaya\UI\Text\Translated\Collection::class, $phraseList);
    $list = iterator_to_array($phraseList);
    $this->assertEquals('Success', (string)$list['One']);
  }
}
