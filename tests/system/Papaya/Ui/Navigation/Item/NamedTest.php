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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiNavigationItemNamedTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Navigation\Item\Named::appendTo
  */
  public function testAppendTo() {
    $item = new \Papaya\UI\Navigation\Item\Named('sample');
    $item->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<link href="http://www.test.tld/index.html" name="sample"/>',
      $item->getXml()
    );
  }
}
