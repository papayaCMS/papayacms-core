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

namespace Papaya\Message\Display;
require_once __DIR__.'/../../../../bootstrap.php';

class TranslatedTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Display\Translated::__construct
   */
  public function testConstructor() {
    $message = new Translated(\Papaya\Message::SEVERITY_INFO, 'Test');
    $string = $this->readAttribute($message, '_message');
    $this->assertInstanceOf(
      \Papaya\UI\Text\Translated::class, $string
    );
    $this->assertAttributeEquals(
      'Test', '_pattern', $string
    );
  }

  /**
   * @covers \Papaya\Message\Display\Translated::__construct
   */
  public function testConstructorWithArguments() {
    $message = new Translated(\Papaya\Message::SEVERITY_INFO, 'Test', array(1, 2, 3));
    $string = $this->readAttribute($message, '_message');
    $this->assertAttributeEquals(
      array(1, 2, 3), '_values', $string
    );
  }
}
