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

namespace Papaya\UI\Dialog\Field\Xhtml;
require_once __DIR__.'/../../../../../../bootstrap.php';

class LinkTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Xhtml\Link::__construct
   */
  public function testConstructor() {
    $link = new Link('http://www.papaya-cms.com', 'PapayaCMS');
    $this->assertAttributeEquals('http://www.papaya-cms.com', '_url', $link);
    $this->assertAttributeEquals('PapayaCMS', '_urlCaption', $link);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Xhtml\Link::appendTo
   */
  public function testAppendTo() {
    $link = new Link('http://www.papaya-cms.com', 'PapayaCMS');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field class="DialogFieldXhtmlLink" error="no">
        <xhtml><a href="http://www.papaya-cms.com">PapayaCMS</a></xhtml>
      </field>',
      $link->getXML()
    );
  }

}
