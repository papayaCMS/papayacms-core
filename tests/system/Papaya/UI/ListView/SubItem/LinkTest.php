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

namespace Papaya\UI\ListView\SubItem {

  use Papaya\TestCase;
  use Papaya\UI\ListView;
  use Papaya\XML\Element;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\ListView\SubItem\Link
   */
  class LinkTest extends TestCase {

    public function testGetURLSetsProvidedParameters() {
      $reference = $this->mockPapaya()->reference();
      $reference
        ->expects($this->once())
        ->method('setParameters')
        ->with(['foo' => 'bar']);

      $link = new Link_TestProxy(['foo' => 'bar']);
      $link->reference($reference);
      $this->assertSame('http://www.example.html', $link->getURL());
    }

    public function testGetURLWithReferenceFromListView() {
      $reference = $this->mockPapaya()->reference();
      $reference
        ->expects($this->once())
        ->method('setParameters')
        ->with(['foo' => 'bar'], 'group');

      $listView = $this->createMock(ListView::class);
      $listView
        ->method('reference')
        ->willReturn($reference);
      $listView
        ->method('parameterGroup')
        ->willReturn('group');

      $subItems = $this->createMock(ListView\SubItems::class);
      $subItems
        ->method('getListView')
        ->willReturn($listView);

      $link = new Link_TestProxy(['foo' => 'bar']);
      $link->collection($subItems);

      $this->assertSame('http://www.example.html', $link->getURL());
    }
  }

  class Link_TestProxy extends Link {

    /**
     * Create dom node structure of the given object and append it to the given xml
     * element node.
     *
     * @param Element $parent
     */
    public function appendTo(Element $parent) {
    }

    public function getURL() {
      return parent::getURL();
    }
  }
}

