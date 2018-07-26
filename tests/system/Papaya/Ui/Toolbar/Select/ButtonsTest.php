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

class PapayaUiToolbarSelectButtonsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiToolbarSelectButtons::appendTo
  */
  public function testAppendToWithCurrentValue() {
    $document = new \PapayaXmlDocument;
    $document->appendElement('sample');
    $select = new \PapayaUiToolbarSelectButtons('foo', array(10 => '10', 20 => '20', 50 => '50'));
    $select->papaya($this->mockPapaya()->application());
    $select->currentValue = 20;
    $select->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <button href="http://www.test.tld/test.html?foo=10" title="10"/>
        <button href="http://www.test.tld/test.html?foo=20" title="20" down="down"/>
        <button href="http://www.test.tld/test.html?foo=50" title="50"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiToolbarSelectButtons::appendTo
  */
  public function testAppendToWithAdditionalParameters() {
    $document = new \PapayaXmlDocument;
    $document->appendElement('sample');
    $select = new \PapayaUiToolbarSelectButtons(
      'foo/size', array(10 => '10', 20 => '20', 50 => '50')
    );
    $select->papaya($this->mockPapaya()->application());
    $select->reference->setParameters(array('page' => 3), 'foo');
    $select->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <button href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10" title="10"/>
        <button href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=20" title="20"/>
        <button href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=50" title="50"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithImages() {
    $select = new \PapayaUiToolbarSelectButtons(
      'foo/size',
      array(
        'first' => array('caption' => 'First', 'image' => 'first-image'),
        'second' => array('caption' => 'Second', 'image' => 'second-image')
      )
    );
    $select->papaya(
      $this->mockPapaya()->application(
        array(
          'images' => array('first-image' => 'first.png', 'second-image' => 'second.png')
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      // language=XML prefix=<fragment> suffix=</fragment>
      '<button href="http://www.test.tld/test.html?foo[size]=first"
        title="First" image="first.png"/>
       <button href="http://www.test.tld/test.html?foo[size]=second"
        title="Second" image="second.png"/>',
      $select
    );
  }
}
