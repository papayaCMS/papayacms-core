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

namespace Papaya\UI\Dialog {

  require_once __DIR__.'/../../../../bootstrap.php';

  class PapayaUiDialogElementsTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\UI\Dialog\Elements::__construct
     */
    public function testConstructorWithOwner() {
      $dialog = $this
        ->getMockBuilder(\Papaya\UI\Dialog::class)
        ->setConstructorArgs(array(new \stdClass()))
        ->getMock();
      $elements = new Elements_TestProxy($dialog);
      $this->assertSame(
        $dialog, $elements->owner()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Elements::appendTo
     */
    public function testAppendTo() {
      $document = new \Papaya\XML\Document();
      $node = $document->createElement('dummy');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Element $element */
      $element = $this->createMock(Element::class);
      $element
        ->expects($this->once())
        ->method('appendTo')
        ->with($this->isInstanceOf(\Papaya\XML\Element::class));
      $elements = new Elements_TestProxy();
      $elements->add($element);
      $elements->appendTo($node);
    }

    /**
     * @covers \Papaya\UI\Dialog\Elements::collect
     */
    public function testCollect() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Element $element */
      $element = $this->createMock(Element::class);
      $element
        ->expects($this->once())
        ->method('collect');
      $elements = new Elements_TestProxy();
      $elements->add($element);
      $elements->collect();
    }
  }

  class Elements_TestProxy extends Elements {
  }
}
