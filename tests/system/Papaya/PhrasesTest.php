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

use Papaya\Content\Language;

require_once __DIR__.'/../../bootstrap.php';

class PapayaPhrasesTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Phrases
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage = $this->createMock(\Papaya\Phrases\Storage::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $phrases = new \Papaya\Phrases($storage, $language);
    $this->assertSame($storage, $phrases->getStorage());
    $this->assertSame($language, $phrases->getLanguage());
  }

  /**
   * @covers \Papaya\Phrases
   */
  public function testGetGroupsAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage = $this->createMock(\Papaya\Phrases\Storage::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $phrases = new \Papaya\Phrases($storage, $language);
    $groups = $this
      ->getMockBuilder(\Papaya\Phrases\Groups::class)
      ->disableOriginalConstructor()
      ->getMock();
    $phrases->groups = $groups;
    $this->assertSame($groups, $phrases->groups);
  }

  /**
   * @covers \Papaya\Phrases
   */
  public function testGetGroupsImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage = $this->createMock(\Papaya\Phrases\Storage::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $phrases = new \Papaya\Phrases($storage, $language);
    $this->assertInstanceOf(\Papaya\Phrases\Groups::class, $phrases->groups);
  }

  /**
   * @covers \Papaya\Phrases::defaultGroup
   */
  public function testDefaultGroupGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage = $this->createMock(\Papaya\Phrases\Storage::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $phrases = new \Papaya\Phrases($storage, $language);
    $phrases->defaultGroup('TestGroup');
    $this->assertEquals('TestGroup', $phrases->defaultGroup());
  }

  /**
   * @covers \Papaya\Phrases::defaultGroup
   */
  public function testDefaultGroupImplicitInit() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage = $this->createMock(\Papaya\Phrases\Storage::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $phrases = new \Papaya\Phrases($storage, $language);
    $phrases->papaya($this->mockPapaya()->application());
    $this->assertEquals('test.html', $phrases->defaultGroup());
  }

  /**
   * @covers \Papaya\Phrases::get
   */
  public function testGetCreatesStringObject() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue(1));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage =$this->createMock(\Papaya\Phrases\Storage::class);
    $storage
      ->expects($this->once())
      ->method('get')
      ->with('Some Phrase', 'TestGroup', 1)
      ->will($this->returnValue('Success'));
    $phrases = new \Papaya\Phrases($storage, $language);
    $phrases->papaya($this->mockPapaya()->application());
    $phrases->defaultGroup('TestGroup');
    $phrase = $phrases->get('Some Phrase');
    $this->assertInstanceOf(\Papaya\Ui\Text::class, $phrase);
    $this->assertEquals('Success', (string)$phrase);
  }

  /**
   * @covers \Papaya\Phrases::getList
   */
  public function testGetListCreatesListObject() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue(1));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage =$this->createMock(\Papaya\Phrases\Storage::class);
    $storage
      ->expects($this->once())
      ->method('get')
      ->with('Some Phrase', 'TestGroup', 1)
      ->will($this->returnValue('Success'));
    $phrases = new \Papaya\Phrases($storage, $language);
    $phrases->papaya($this->mockPapaya()->application());
    $phrases->defaultGroup('TestGroup');
    $list = iterator_to_array($phrases->getList(array('Some Phrase')));
    $this->assertInstanceOf(\Papaya\Ui\Text::class, $list[0]);
    $this->assertEquals('Success', (string)$list[0]);
  }

  /**
   * @covers \Papaya\Phrases::getList
   */
  public function testGetText() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Language $language */
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue(1));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Phrases\Storage $storage */
    $storage =$this->createMock(\Papaya\Phrases\Storage::class);
    $storage
      ->expects($this->once())
      ->method('get')
      ->with('Some Phrase', 'TestGroup', 1)
      ->will($this->returnValue('Success'));
    $phrases = new \Papaya\Phrases($storage, $language);
    $phrases->papaya($this->mockPapaya()->application());
    $phrases->defaultGroup('TestGroup');
    $this->assertEquals('Success', $phrases->getText('Some Phrase'));
  }
}
