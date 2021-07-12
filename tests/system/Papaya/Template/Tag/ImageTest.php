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

namespace Papaya\Template\Tag {

  use Papaya\TestFramework\TestCase;
  use Papaya\XML\Document as XMLDocument;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Tag\Image
   */
  class ImageTest extends TestCase {

    public function testAppendTo() {
      $image = new Image('d74f6d0324f5d90b23bb3771200ddf7d,60,96,max');
      $document = new XMLDocument();
      $container = $document->appendElement('container');
      $image->appendTo($container);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<container>
         <papaya:media 
           xmlns:papaya="http://www.papaya-cms.com/ns/papayacms" 
           src="d74f6d0324f5d90b23bb3771200ddf7d" width="60" height="96" resize="max"/>
       </container>',
        $document->saveHTML()
      );
    }

    public function testAppendToWithExistingTag() {
      $image = new Image(
        '<papaya:media src="d74f6d0324f5d90b23bb3771200ddf7d" width="60" height="96" resize="max"/>'
      );
      $document = new XMLDocument();
      $container = $document->appendElement('container');
      $image->appendTo($container);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<container>
         <papaya:media 
           xmlns:papaya="http://www.papaya-cms.com/ns/papayacms" 
           src="d74f6d0324f5d90b23bb3771200ddf7d" width="60" height="96" resize="max"/>
       </container>',
        $document->saveHTML()
      );
    }


    public function testAppendToWithConstructorArguments() {
      $image = new Image(
        'd74f6d0324f5d90b23bb3771200ddf7d',
        160,
        196,
        'alternative',
        'min',
        'a subtitle'
      );
      $document = new XMLDocument();
      $container = $document->appendElement('container');
      $image->appendTo($container);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<container>
         <papaya:media 
           xmlns:papaya="http://www.papaya-cms.com/ns/papayacms" 
           src="d74f6d0324f5d90b23bb3771200ddf7d" 
           width="160" 
           height="196" 
           resize="min" 
           alt="alternative" 
           subtitle="a subtitle"/>
       </container>',
        $document->saveHTML()
      );
    }

    public function testAppendToWithEmptyMediaSource() {
      $image = new Image('');
      $document = new XMLDocument();
      $container = $document->appendElement('container');
      $image->appendTo($container);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<container/>',
        $document->saveHTML()
      );
    }

    public function testToString() {
      $image = new Image('d74f6d0324f5d90b23bb3771200ddf7d,60,96,max');
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<papaya:media 
           xmlns:papaya="http://www.papaya-cms.com/ns/papayacms" 
           src="d74f6d0324f5d90b23bb3771200ddf7d" width="60" height="96" resize="max"/>',
        (string)$image
      );
    }

    public function testToStringWithEmptyMediaSource() {
      $image = new Image('');
      $this->assertEmpty(
        (string)$image
      );
    }
  }
}
