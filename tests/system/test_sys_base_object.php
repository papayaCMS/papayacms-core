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

use Papaya\Url;

require_once __DIR__.'/../bootstrap.php';
PapayaTestCase::defineConstantDefaults('PAPAYA_URL_EXTENSION');

class PapayaLibSystemBaseObjectTest extends PapayaTestCase {

  /**
   * @group Bug2474
   * @covers base_object::getWebLink
   * @dataProvider dataProviderCategoryIds
   * @param int|NULL $categoryId
   * @param string $expectedLink
   */
  public function testGetWebLinkResetCategory($categoryId, $expectedLink) {
    $request = new PapayaRequest(
      $this->mockPapaya()->options(
        array('PAPAYA_URL_LEVEL_SEPARATOR' => ':')
      )
    );
    $request->load(new Url('http://www.blah.tld/index.3.7.html'));

    $obj = new base_object;
    $obj->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertSame(
      $expectedLink,
      $obj->getWebLink(
        1, NULL, NULL, array('blah' => 'blupp'), 'pn', 'filename', $categoryId
      )
    );
  }

  /************************
  * Data Provider
  ************************/

  public function dataProviderCategoryIds() {
    return array(
      'overriding category id' => array(
        5,
        'filename.5.1.html?pn:blah=blupp'
      ),
      'remove category using NULL' => array(
        NULL,
        'filename.1.html?pn:blah=blupp'
      ),
      'remove category using 0' => array(
        0,
        'filename.1.html?pn:blah=blupp'
      ),
      'remove category using negative value' => array(
        -3,
        'filename.1.html?pn:blah=blupp'
      ),
    );
  }
}
