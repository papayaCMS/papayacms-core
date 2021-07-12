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

require_once __DIR__.'/../bootstrap.php';

class simple_xmltreeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers simple_xmltree::unserializeArrayFromXML
   */
  public function testUnserializeArrayFromXML() {
    $xmlStr = /** @lang XML */
      '<data>
         <data-element name="PAPAYA_LAYOUT_THEME"><![CDATA[theme]]></data-element>
         <data-element name="PAPAYA_LAYOUT_TEMPLATES"><![CDATA[tpl]]></data-element>
       </data>';
    $expected = array('PAPAYA_LAYOUT_THEME' => 'theme', 'PAPAYA_LAYOUT_TEMPLATES' => 'tpl');
    $actual = null;
    simple_xmltree::unserializeArrayFromXML('', $actual, $xmlStr);
    $this->assertEquals($expected, $actual);
  }

}
