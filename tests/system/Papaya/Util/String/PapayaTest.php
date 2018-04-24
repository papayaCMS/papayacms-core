<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilStringPapayaTest extends PapayaTestCase {

  /**
  * @dataProvider getImageTagDataProvider
  * @covers PapayaUtilStringPapaya::getImageTag
  */
  public function testGetImageTag($expected, $params) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringPapaya::getImageTag(
        $params['str'],
        $params['width'],
        $params['height'],
        $params['alt'],
        $params['resize'],
        $params['subtitle']
      )
    );
  }


  /*********************************
  * Data Provider
  *********************************/

  public static function getImageTagDataProvider() {

    return array(
      'string no match' => array (
        '',
        array(
          'str' => '...InvalidString...',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str only (1)' => array (
        '<papaya:media />',
        array(
          'str' => '<papaya:media />',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str only (2)' => array (
        '<papaya:media ></papaya:media>',
        array(
          'str' => '<papaya:media ></papaya:media>',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str only (3)' => array (
        '<papaya:media url="abc"></papaya:media>',
        array(
          'str' => '<papaya:media url="abc"></papaya:media>',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str only (4)' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306"'.
        ' width="440" height="400" resize="max"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306,440,400,max',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str only (media id)' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str + $width' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306" width="440"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306',
          'width' => 440,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str + $height' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306" height="440"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306',
          'width' => 0,
          'height' => 440,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str + $alt' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306" alt="Test"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306',
          'width' => 0,
          'height' => 0,
          'alt' => 'Test',
          'resize' => NULL,
          'subtitle' => '',
        )
      ),
      '$str + $resize' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306" resize="max"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => 'max',
          'subtitle' => '',
        )
      ),
      '$str + $subtitle' => array (
        '<papaya:media src="98bb0521f5924e7532be1c137497b306" subtitle="SubTitle"/>',
        array(
          'str' => '98bb0521f5924e7532be1c137497b306',
          'width' => 0,
          'height' => 0,
          'alt' => '',
          'resize' => NULL,
          'subtitle' => 'SubTitle',
        )
      ),
    );
  }
}
