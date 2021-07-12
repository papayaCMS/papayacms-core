<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Administration\Phrases\Storage {

  use Papaya\CMS\Content\Phrase as ContentPhrase;
  use Papaya\CMS\Content\Phrases as ContentPhrases;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\CMS\Administration\Phrases\Storage\Database
   */
  class DatabaseTest extends TestCase {


    public function testLoadPhrase() {
      $data = [
        [
          'identifier' => 'foo',
          'group' => 'en',
          'language_id' => 1,
          'translation' => 'bar'
        ]
      ];
      $phrases = $this->createContentPhrasesFixture($data);

      $storage = new Database();
      $storage->phrases($phrases);

      $this->assertSame('bar', $storage->get('foo', 'group', 1));
      $this->assertSame('bar', $storage->get('foo', 'group', 1));
    }

    public function testLoadPhraseLoadSeparate() {
      $phrase = $this->createContentPhraseFixture(
        [
          'identifier' => 'foo',
          'group' => 'en',
          'language_id' => 1,
          'translation' => 'bar'
        ]
      );
      $phrases = $this->createContentPhrasesFixture([], $phrase);

      $storage = new Database();
      $storage->phrases($phrases);

      $this->assertSame('bar', $storage->get('foo', 'group', 1));
      $this->assertSame('bar', $storage->get('foo', 'group', 1));
    }

    public function testLoadPhraseLoadFails() {
      $phrases = $this->createContentPhrasesFixture(NULL);

      $storage = new Database();
      $storage->phrases($phrases);

      $this->assertSame('foo', $storage->get('foo', 'group', 1));
      $this->assertSame('foo', $storage->get('foo', 'group', 1));
    }

    public function testLoadPhraseLoadNotFound() {
      $phrase = $this->createContentPhraseFixture(NULL);
      $phrases = $this->createContentPhrasesFixture([], $phrase);

      $storage = new Database();
      $storage->phrases($phrases);

      $this->assertSame('foo', $storage->get('foo', 'group', 1));
      $this->assertSame('foo', $storage->get('foo', 'group', 1));
    }

    public function testLoadPhraseLoadNotFoundLogMessage() {
      $options = $this->mockPapaya()->options(['PAPAYA_DEBUG_LANGUAGE_PHRASES' => TRUE]);
      $messages = $this->createMock(ContentPhrase\Messages::class);
      $messages
        ->expects($this->once())
        ->method('add');

      $phrase = $this->createContentPhraseFixture(NULL);
      $phrases = $this->createContentPhrasesFixture([], $phrase);

      $storage = new Database();
      $storage->papaya($this->mockPapaya()->application(['options' => $options]));
      $storage->phrases($phrases);
      $storage->messages($messages);

      $this->assertSame('foo', $storage->get('foo', 'group', 1));
      $this->assertSame('foo', $storage->get('foo', 'group', 1));
    }

    public function testMessagesGetAfterSet() {
      $messages = $this->createMock(ContentPhrase\Messages::class);
      $storage = new Database();
      $this->assertSame($messages, $storage->messages($messages));
    }

    public function testMessagesImplicitCreate() {
      $storage = new Database();
      $storage->papaya($papaya = $this->mockPapaya()->application());
      $this->assertSame($papaya, $storage->messages()->papaya());
    }

    public function testPhrasesGetAfterSet() {
      $phrases = $this->createMock(ContentPhrases::class);
      $storage = new Database();
      $this->assertSame($phrases, $storage->phrases($phrases));
    }

    public function testPhrasesImplicitCreate() {
      $storage = new Database();
      $storage->papaya($papaya = $this->mockPapaya()->application());
      $this->assertSame($papaya, $storage->phrases()->papaya());
    }

    /**
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentPhrases
     */
    private function createContentPhrasesFixture(array $data = NULL, ContentPhrase $phrase = NULL) {
      $phrases = $this->createMock(ContentPhrases::class);
      $phrases
        ->expects($this->once())
        ->method('load')
        ->with(['group' => 'group', 'language_id' => 1])
        ->willReturn(is_array($data));
      $phrases
        ->method('getItem')
        ->willReturn($phrase);
      if (is_array($data)) {
        $phrases
          ->method('getIterator')
          ->willReturn(new \ArrayIterator($data));
      }

      return $phrases;
    }

    /**
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentPhrase
     */
    private function createContentPhraseFixture(array $data = NULL) {
      $phrase = $this->createMock(ContentPhrase::class);
      $phrase
        ->expects($this->atLeastOnce())
        ->method('isLoaded')
        ->willReturn($data !== NULL);
      $phrase
        ->method('load')
        ->willReturn(FALSE);
      $phrase
        ->method('offsetGet')
        ->willReturnCallback(
          static function($key) use ($data) {
            return isset($data[$key]) ? $data[$key] : NULL;
          }
        );
      $phrase
        ->method('getIterator')
        ->willReturn(new \ArrayIterator(NULL !== $data ? $data : []));
      return $phrase;
    }
  }

}
