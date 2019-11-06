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

namespace Papaya {
  require_once __DIR__.'/../../bootstrap.php';

  /**
   * @covers \Papaya\Phrases
   */
  class PhrasesTest extends TestCase {

    public function testConstructor() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $this->assertSame($storage, $phrases->getStorage());
      $this->assertSame($language, $phrases->getLanguage());
    }

    public function testGetGroupsAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $groups = $this
        ->getMockBuilder(Phrases\Groups::class)
        ->disableOriginalConstructor()
        ->getMock();
      $phrases->groups = $groups;
      $this->assertSame($groups, $phrases->groups);
    }

    public function testGetGroupsImplicitCreate() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $this->assertInstanceOf(Phrases\Groups::class, $phrases->groups);
    }

    public function testDefaultGroupGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $phrases->defaultGroup('TestGroup');
      $this->assertEquals('TestGroup', $phrases->defaultGroup());
    }

    public function testDefaultGroupImplicitInit() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $phrases->papaya($this->mockPapaya()->application());
      $this->assertEquals('test.html', $phrases->defaultGroup());
    }

    /**
     * @backupGlobals
     */
    public function testDefaultGroupImplicitInitFromScriptFileName() {
      $_SERVER['SCRIPT_FILENAME'] = '/path/html/directory/index.php';
      $request = $this->mockPapaya()->request([], 'http://www.test.tld/');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $phrases->papaya(
        $this->mockPapaya()->application(
          ['request' => $request]
        )
      );
      $this->assertEquals('index.php', $phrases->defaultGroup());
    }

    /**
     * @param string $expectedGroup
     * @param string $href
     * @testWith
     *   ["administration.views", "http://example.com/papaya/administration.views"]
     *   ["jquery.papayaDialogFieldColor.js", "http://example.com/papaya/script/jquery.papayaDialogFieldColor.js"]
     */
    public function testDefaultGroupFromRequestFile($expectedGroup, $href) {
      $request = $this->mockPapaya()->request([], $href);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $phrases = new Phrases($storage, $language);
      $phrases->papaya(
        $this->mockPapaya()->application(
          ['request' => $request]
        )
      );
      $this->assertEquals($expectedGroup, $phrases->defaultGroup());
    }

    public function testGetCreatesStringObject() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn(1);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      $storage
        ->expects($this->once())
        ->method('get')
        ->with('Some Phrase', 'TestGroup', 1)
        ->willReturn('Success');
      $phrases = new Phrases($storage, $language);
      $phrases->papaya($this->mockPapaya()->application());
      $phrases->defaultGroup('TestGroup');
      $phrase = $phrases->get('Some Phrase');
      $this->assertInstanceOf(UI\Text::class, $phrase);
      $this->assertEquals('Success', (string)$phrase);
    }

    public function testGetListCreatesListObject() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn(1);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      $storage
        ->expects($this->once())
        ->method('get')
        ->with('Some Phrase', 'TestGroup', 1)
        ->willReturn('Success');
      $phrases = new Phrases($storage, $language);
      $phrases->papaya($this->mockPapaya()->application());
      $phrases->defaultGroup('TestGroup');
      $list = iterator_to_array($phrases->getList(['Some Phrase']));
      $this->assertInstanceOf(UI\Text::class, $list[0]);
      $this->assertEquals('Success', (string)$list[0]);
    }

    public function testGetText() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn(1);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      $storage
        ->expects($this->once())
        ->method('get')
        ->with('Some Phrase', 'TestGroup', 1)
        ->willReturn('Success');
      $phrases = new Phrases($storage, $language);
      $phrases->papaya($this->mockPapaya()->application());
      $phrases->defaultGroup('TestGroup');
      $this->assertEquals('Success', $phrases->getText('Some Phrase'));
    }

    public function testGetTextFmt() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Content\Language $language */
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn(1);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Phrases\Storage $storage */
      $storage = $this->createMock(Phrases\Storage::class);
      $storage
        ->expects($this->once())
        ->method('get')
        ->with('Hello %s!', 'TestGroup', 1)
        ->willReturn('Hello %s!');
      $phrases = new Phrases($storage, $language);
      $phrases->papaya($this->mockPapaya()->application());
      $phrases->defaultGroup('TestGroup');
      $this->assertEquals('Hello World!', $phrases->getTextFmt('Hello %s!', ['World']));
    }
  }
}
