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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiLinkAttributesTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiLinkAttributes::isPopup
  */
  public function testIsPopupExpectingFalse() {
    $attributes = new \PapayaUiLinkAttributes();
    $this->assertFalse($attributes->isPopup);
  }

  /**
  * @covers \PapayaUiLinkAttributes::isPopup
  */
  public function testIsPopupExpectingTrue() {
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->setPopup('sample', '80%', '90%');
    $this->assertTrue($attributes->isPopup);
  }

  /**
  * @covers \PapayaUiLinkAttributes::removePopup
  */
  public function testRemovePopup() {
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->setPopup('sample', '80%', '90%');
    $attributes->removePopup();
    $this->assertFalse($attributes->isPopup);
  }

  /**
  * @covers \PapayaUiLinkAttributes::setPopup
  */
  public function testSetPopup() {
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->setPopup('sample', '80%', '90%');
    $this->assertEquals('sample', $attributes->target);
    $this->assertEquals('80%', $attributes->popupWidth);
    $this->assertEquals('90%', $attributes->popupHeight);
  }

  /**
  * @covers \PapayaUiLinkAttributes::setPopup
  * @covers \PapayaUiLinkAttributes::setPopupOptions
  */
  public function testSetPopupWithAllparameters() {
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->setPopup(
      'sample', '80%', '90%', '50', '60', \PapayaUiLinkAttributes::OPTION_SCROLLBARS_AUTO
    );
    $this->assertEquals('50', $attributes->popupTop);
    $this->assertEquals('60', $attributes->popupLeft);
    $this->assertEquals(
      \PapayaUiLinkAttributes::OPTION_SCROLLBARS_AUTO, $attributes->popupOptions
    );
  }

  /**
  * @covers \PapayaUiLinkAttributes::setPopupOptions
  */
  public function testSetPopupOptionsInvalidExceptionException() {
    $attributes = new \PapayaUiLinkAttributes();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid options definition: only one scrollbars option can be set.');
    $attributes->popupOptions = (
      \PapayaUiLinkAttributes::OPTION_SCROLLBARS_ALWAYS |
      \PapayaUiLinkAttributes::OPTION_SCROLLBARS_NEVER
    );
  }

  /**
   * @covers \PapayaUiLinkAttributes::getPopupOptionsArray
   * @dataProvider providePopupLinkOptions
   * @param array $expected
   * @param string|integer $top
   * @param string|integer $left
   * @param integer $options
   */
  public function testGetPopupOptionsArray(array $expected, $top = NULL, $left = NULL, $options = NULL) {
    $document = new \PapayaXmlDocument();
    $document->appendElement('sample');
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->setPopup('sampleTarget', '80%', '300', $top, $left, $options);
    $this->assertEquals(
      $expected, $attributes->getPopupOptionsArray()
    );
  }

  /**
   * @covers \PapayaUiLinkAttributes::appendTo
   * @dataProvider provideSimpleLinkData
   * @param string $expected
   * @param string $class
   * @param string $target
   */
  public function testAppendTo($expected, $class, $target) {
    $document = new \PapayaXmlDocument();
    $node = $document->appendElement('sample');
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->class = $class;
    $attributes->target = $target;
    $node->append($attributes);
    $this->assertEquals(
      $expected, $node->saveXml()
    );
  }

  /**
   * @covers \PapayaUiLinkAttributes::appendTo
   * @dataProvider providePopupLinkData
   * @param string $expected
   * @param string|integer $top
   * @param string|integer $left
   * @param integer $options
   */
  public function testAppendToForPopup($expected, $top = NULL, $left = NULL, $options = NULL) {
    $document = new \PapayaXmlDocument();
    $node = $document->appendElement('sample');
    $attributes = new \PapayaUiLinkAttributes();
    $attributes->setPopup('sampleTarget', '80%', '300', $top, $left, $options);
    $node->append($attributes);
    $this->assertEquals(
      $expected, $node->saveXml()
    );
  }

  /****************************
  * Data Provider
  ****************************/

  public static function provideSimpleLinkData() {
    return array(
      array(
        /** @lang XML */'<sample/>', '', ''
      ),
      array(
        /** @lang XML */'<sample/>', '', '_self'
      ),
      array(
        /** @lang XML */'<sample class="sampleClass"/>', 'sampleClass', ''
      ),
      array(
        /** @lang XML */'<sample class="sampleClass" target="sampleTarget"/>', 'sampleClass', 'sampleTarget'
      ),
      array(
        /** @lang XML */'<sample target="_top"/>', '', '_top'
      ),
    );
  }

  public static function providePopupLinkData() {
    return array(
      'default' => array(
        '<sample target="sampleTarget"'.
        ' data-popup="{&quot;width&quot;:&quot;80%&quot;,&quot;height&quot;:&quot;300&quot;,'.
          '&quot;resizeable&quot;:false,&quot;toolBar&quot;:false,&quot;menuBar&quot;:false,'.
          '&quot;locationBar&quot;:false,&quot;statusBar&quot;:false,'.
          '&quot;scrollBars&quot;:&quot;no&quot;}"/>'
      ),
      'all elements active' => array(
        '<sample target="sampleTarget"'.
        ' data-popup="{&quot;width&quot;:&quot;80%&quot;,&quot;height&quot;:&quot;300&quot;,'.
          '&quot;resizeable&quot;:true,&quot;toolBar&quot;:true,&quot;menuBar&quot;:true,'.
          '&quot;locationBar&quot;:true,&quot;statusBar&quot;:true,'.
          '&quot;scrollBars&quot;:&quot;yes&quot;}"/>',
        NULL,
        NULL,
        \PapayaUiLinkAttributes::OPTION_RESIZEABLE |
        \PapayaUiLinkAttributes::OPTION_SCROLLBARS_ALWAYS |
        \PapayaUiLinkAttributes::OPTION_TOOLBAR |
        \PapayaUiLinkAttributes::OPTION_MENUBAR |
        \PapayaUiLinkAttributes::OPTION_LOCATIONBAR |
        \PapayaUiLinkAttributes::OPTION_STATUSBAR
      ),
      'scrollbars auto' => array(
        '<sample target="sampleTarget"'.
        ' data-popup="{&quot;width&quot;:&quot;80%&quot;,&quot;height&quot;:&quot;300&quot;,'.
          '&quot;resizeable&quot;:false,&quot;toolBar&quot;:false,&quot;menuBar&quot;:false,'.
          '&quot;locationBar&quot;:false,&quot;statusBar&quot;:false,'.
          '&quot;scrollBars&quot;:&quot;auto&quot;}"/>',
        NULL,
        NULL,
        \PapayaUiLinkAttributes::OPTION_SCROLLBARS_AUTO
      ),
      'position' => array(
        '<sample target="sampleTarget"'.
        ' data-popup="{&quot;width&quot;:&quot;80%&quot;,&quot;height&quot;:&quot;300&quot;,'.
          '&quot;top&quot;:100,&quot;left&quot;:80,'.
          '&quot;resizeable&quot;:false,&quot;toolBar&quot;:false,&quot;menuBar&quot;:false,'.
          '&quot;locationBar&quot;:false,&quot;statusBar&quot;:false,'.
          '&quot;scrollBars&quot;:&quot;no&quot;}"/>',
        100,
        80
      )
    );
  }

  public static function providePopupLinkOptions() {
    return array(
      'default' => array(
        array(
          'width' => '80%',
          'height' => '300',
          'resizeable' => false,
          'toolBar' => false,
          'menuBar' => false,
          'locationBar' => false,
          'statusBar' => false,
          'scrollBars' => 'no'
        )
      ),
      'all elements active' => array(
        array(
          'width' => '80%',
          'height' => '300',
          'resizeable' => true,
          'toolBar' => true,
          'menuBar' => true,
          'locationBar' => true,
          'statusBar' => true,
          'scrollBars' => 'yes'
        ),
        NULL,
        NULL,
        \PapayaUiLinkAttributes::OPTION_RESIZEABLE |
        \PapayaUiLinkAttributes::OPTION_SCROLLBARS_ALWAYS |
        \PapayaUiLinkAttributes::OPTION_TOOLBAR |
        \PapayaUiLinkAttributes::OPTION_MENUBAR |
        \PapayaUiLinkAttributes::OPTION_LOCATIONBAR |
        \PapayaUiLinkAttributes::OPTION_STATUSBAR
      ),
      'scrollbars auto' => array(
        array(
          'width' => '80%',
          'height' => '300',
          'resizeable' => false,
          'toolBar' => false,
          'menuBar' => false,
          'locationBar' => false,
          'statusBar' => false,
          'scrollBars' => 'auto'
        ),
        NULL,
        NULL,
        \PapayaUiLinkAttributes::OPTION_SCROLLBARS_AUTO
      ),
      'position' => array(
        array(
          'width' => '80%',
          'height' => '300',
          'top' => 100,
          'left' => 80,
          'resizeable' => false,
          'toolBar' => false,
          'menuBar' => false,
          'locationBar' => false,
          'statusBar' => false,
          'scrollBars' => 'no'
        ),
        100,
        80
      )
    );
  }
}
