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

namespace Papaya\UI\ListView\SubItem {
  require_once __DIR__.'/../../../../../bootstrap.php';

  class PagingTest extends \Papaya\TestCase {

    public function testGetXML() {
      $subitem = new Paging('test', 30);
      $subitem->papaya(
        $this->mockPapaya()->application()
      );
      $this->assertXmlFragmentEqualsXmlFragment(
        '<subitem align="left">
          <paging count="3">
            <page href="http://www.test.tld/test.html?test=1" number="1" selected="selected"/>
            <page href="http://www.test.tld/test.html?test=2" number="2"/>
            <page href="http://www.test.tld/test.html?test=3" number="3"/>
            <page href="http://www.test.tld/test.html?test=2" number="2" type="next"/>
            <page href="http://www.test.tld/test.html?test=3" number="3" type="last"/>
          </paging>
        </subitem>',
        $subitem->getXML()
      );
    }

    public function testGetXMLWithPageParameter() {
      $subitem = new Paging('test', 30);
      $subitem->papaya(
        $this->mockPapaya()->application(
          ['request' => $this->mockPapaya()->request(['test' => 3])]
        )
      );
      $this->assertXmlFragmentEqualsXmlFragment(
        '<subitem align="left">
          <paging count="3">
            <page href="http://www.test.tld/test.html?test=1" number="1" type="first"/>
            <page href="http://www.test.tld/test.html?test=2" number="2" type="previous"/>
            <page href="http://www.test.tld/test.html?test=1" number="1"/>
            <page href="http://www.test.tld/test.html?test=2" number="2"/>
            <page href="http://www.test.tld/test.html?test=3" number="3" selected="selected"/>
          </paging>
        </subitem>',
        $subitem->getXML()
      );
    }
  }
}
