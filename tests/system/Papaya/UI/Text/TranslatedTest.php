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

namespace Papaya\UI\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class TranslatedTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Text\Translated::__toString
   * @covers \Papaya\UI\Text\Translated::translate
   */
  public function testMagicMethodToString() {
    $phrases = $this
      ->getMockBuilder(\Papaya\Phrases::class)
      ->disableOriginalConstructor()
      ->getMock();
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with($this->equalTo('Hello %s!'))
      ->will($this->returnValue('Hi %s!'));
    $string = new Translated('Hello %s!', array('World'));
    $string->papaya(
      $this->mockPapaya()->application(array('Phrases' => $phrases))
    );
    $this->assertEquals(
      'Hi World!', (string)$string
    );
  }

  /**
   * @covers \Papaya\UI\Text\Translated::__toString
   * @covers \Papaya\UI\Text\Translated::translate
   */
  public function testMagicMethodToStringWithoutTranslationEngine() {
    $string = new Translated('Hello %s!', array('World'));
    $string->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      'Hello World!', (string)$string
    );
  }

}
