<?php
require_once(dirname(__FILE__).'/../bootstrap.php');
PapayaTestCase::defineConstantDefaults('PAPAYA_URL_EXTENSION');

class PapayaLibSystemBaseObjectTest extends PapayaTestCase {

  /**
  * @group Bug2474
  * @covers base_object::getWebLink
  * @dataProvider dataProviderCategoryIds
  */
  public function testGetWebLinkResetCategory($categoryId, $expectedLink) {
    $request = new PapayaRequest(
      $this->mockPapaya()->options(
        array('PAPAYA_URL_LEVEL_SEPARATOR' => ':')
      )
    );
    $request->load(new PapayaUrl('http://www.blah.tld/index.3.7.html'));

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
