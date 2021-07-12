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

namespace Papaya\UI\Dialog\Button {
  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Dialog\Button\Submit
   */
  class SubmitTest extends \Papaya\TestFramework\TestCase {

    public function testAppendTo() {
      $button = new Submit('Test Caption');
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<button type="submit" align="right">Test Caption</button>',
        $button->getXML()
      );
    }

    public function testAppendToWithInterfaceStringObject() {
      $caption = $this
        ->getMockBuilder(\Papaya\UI\Text::class)
        ->setConstructorArgs(['.'])
        ->getMock();
      $caption
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('Test Caption');
      $button = new Submit(
        $caption, \Papaya\UI\Dialog\Button::ALIGN_LEFT
      );
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<button type="submit" align="left">Test Caption</button>',
        $button->getXML()
      );
    }

    public function testAppendToWithHint() {
      $button = new Submit(
        'Test Caption', \Papaya\UI\Dialog\Button::ALIGN_LEFT
      );
      $button->setHint('test hint');
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<button type="submit" align="left" hint="test hint">Test Caption</button>',
        $button->getXML()
      );
    }
  }
}
