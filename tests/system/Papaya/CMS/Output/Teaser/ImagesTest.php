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

namespace Papaya\CMS\Output\Teaser;
require_once __DIR__.'/../../../../../bootstrap.php';

/**
 * @covers \Papaya\CMS\Output\Teaser\Images
 */
class ImagesTest extends \Papaya\TestFramework\TestCase {

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
    $document = new \Papaya\XML\Document();
    $document->loadXML($xml);

    $images = new Images(100, 100);
    $thumbnails = $images->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<teaser-thumbnails>
        <thumbnail page-id="42">
          <papaya:media xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"
           src="test.png" resize="max" width="100" height="100"/>
        </thumbnail>
      </teaser-thumbnails>',
      $thumbnails->saveXML()
    );
  }

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
    $document = new \Papaya\XML\Document();
    $document->loadXML($xml);

    $images = new Images(100, 100);
    $thumbnails = $images->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<subtopicthumbs>
        <thumb topic="42">
          <papaya:media xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"
           src="test.png" resize="max" width="100" height="100"/>
        </thumb>
      </subtopicthumbs>',
      $thumbnails->saveXML()
    );
  }

  public function testAppendToWithoutImageData() {
    $xml =
      /** @lang XML */
      '<teasers>
        <teaser page-id="42"/>
      </teasers>';
    $document = new \Papaya\XML\Document();
    $document->loadXML($xml);

    $images = new Images(100, 100);
    $thumbnails = $images->appendTo($document->documentElement);
    $this->assertNull($thumbnails);
  }

  public function testReplaceWithTeasers() {
    $xml =
      /** @lang XML */
      '<teasers>
        <teaser page-id="42">
          <image>
            <img src="test.png"/>
          </image>
        </teaser>
      </teasers>';
    $document = new \Papaya\XML\Document();
    $document->loadXML($xml);

    $images = new Images(100, 100);
    $images->replaceIn($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<teasers>
          <teaser page-id="42">
            <image>
              <papaya:media 
                xmlns:papaya="http://www.papaya-cms.com/ns/papayacms" 
                height="100" resize="max" src="test.png" width="100"/>
            </image>
          </teaser>
        </teasers>',
      $document->documentElement->saveXML()
    );
  }
}
