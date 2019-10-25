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

namespace Papaya\UI\Text {

  use Papaya\Phrases;
  use Papaya\TestCase;
  use \LogicException;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Text\Translated
   */
  class TranslatedTest extends TestCase {

    public function testMagicMethodToString() {
      $phrases = $this
        ->getMockBuilder(Phrases::class)
        ->disableOriginalConstructor()
        ->getMock();
      $phrases
        ->expects($this->once())
        ->method('getText')
        ->with($this->equalTo('Hello %s!'))
        ->willReturn('Hi %s!');
      $string = new Translated('Hello %s!', ['World']);
      $string->papaya(
        $this->mockPapaya()->application(['AdministrationPhrases' => $phrases])
      );
      $this->assertEquals(
        'Hi World!', (string)$string
      );
    }

    public function testMagicMethodToStringWithoutTranslationEngine() {
      $string = new Translated('Hello %s!', ['World']);
      $string->papaya(
        $this->mockPapaya()->application()
      );
      $this->assertEquals(
        'Hello World!', (string)$string
      );
    }

    public function testMagicMethodToStringWithTranslationEngine() {
      $phrases = $this
        ->getMockBuilder(Phrases::class)
        ->disableOriginalConstructor()
        ->getMock();
      $phrases
        ->expects($this->once())
        ->method('getText')
        ->with($this->equalTo('Hello %s!'))
        ->willReturn('Hi %s!');
      $string = new Translated('Hello %s!', ['World'], $phrases);
      $this->assertEquals(
        'Hi World!', (string)$string
      );
    }

    public function testMagicMethodToStringCatchesExceptions() {
      $phrases = $this
        ->getMockBuilder(Phrases::class)
        ->disableOriginalConstructor()
        ->getMock();
      $phrases
        ->expects($this->once())
        ->method('getText')
        ->with($this->equalTo('Hello %s!'))
        ->willThrowException(new LogicException());
      $string = new Translated('Hello %s!', ['World'], $phrases);
      $this->assertEquals(
        'Hello World!', (string)$string
      );
    }

  }
}
