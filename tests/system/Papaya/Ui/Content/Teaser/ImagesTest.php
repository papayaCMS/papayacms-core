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

class PapayaUiContentTeaserImagesTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiContentTeaserImages::__construct
  */
  public function testConstructorWithAllParameters() {
    $xml =
      /** @lang XML */
      '<subtopics>
        <subtopic no="42">
          <image>
            <img src="test.png"/>
          </image>
        </subtopic>
      </subtopics>';
    $document = new \Papaya\Xml\Document();
    $document->loadXml($xml);
    $images = new \PapayaUiContentTeaserImages($document->documentElement, 21, 42, 'min');
    $this->assertAttributeSame(
      $document->documentElement, '_teasers', $images
    );
    $this->assertAttributeEquals(
      21, '_width', $images
    );
    $this->assertAttributeEquals(
      42, '_height', $images
    );
    $this->assertAttributeEquals(
      'min', '_resizeMode', $images
    );
  }

  /**
  * @covers \PapayaUiContentTeaserImages::appendTo
  */
  public function testAppendToWithTeasers() {
    $xml =
      /** @lang XML */
      '<teasers>
        <teaser page-id="42">
          <image>
            <img src="test.png"/>
          </image>
        </teaser>
      </teasers>';
    $document = new \Papaya\Xml\Document();
    $document->loadXml($xml);

    $images = new \PapayaUiContentTeaserImages($document->documentElement, 100, 100);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<teaser-thumbnails>
        <thumbnail page-id="42">
          <papaya:media xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"
           src="test.png" resize="max" width="100" height="100"/>
        </thumbnail>
      </teaser-thumbnails>',
      $images->getXml()
    );
  }

  /**
  * @covers \PapayaUiContentTeaserImages::appendTo
  */
  public function testAppendToWithSubtopics() {
    $xml =
      /** @lang XML */
      '<subtopics>'.
        '<subtopic no="42">'.
          '<image>'.
            '<img src="test.png"/>'.
          '</image>'.
        '</subtopic>'.
      '</subtopics>';
    $document = new \Papaya\Xml\Document();
    $document->loadXml($xml);

    $images = new \PapayaUiContentTeaserImages($document->documentElement, 100, 100);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<subtopicthumbs>
        <thumb topic="42">
          <papaya:media xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"
           src="test.png" resize="max" width="100" height="100"/>
        </thumb>
      </subtopicthumbs>',
      $images->getXml()
    );
  }

  /**
  * @covers \PapayaUiContentTeaserImages::appendTo
  */
  public function testAppendToWithoutImageData() {
    $xml =
      /** @lang XML */
      '<teasers>
        <teaser page-id="42"/>
      </teasers>';
    $document = new \Papaya\Xml\Document();
    $document->loadXml($xml);

    $images = new \PapayaUiContentTeaserImages($document->documentElement, 100, 100);
    $this->assertEquals(
      '', $images->getXml()
    );
  }
}
