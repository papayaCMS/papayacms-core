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

namespace Papaya\Template\Tag;
require_once __DIR__.'/../../../../bootstrap.php';

class ImageTest extends \Papaya\TestCase {
  /**
   * @covers \Papaya\Template\Tag\Image::appendTo
   */
  public function testAppendTo() {
    $image = new Image('d74f6d0324f5d90b23bb3771200ddf7d,60,96,max');
    $document = new \Papaya\XML\Document();
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

  /**
   * @covers \Papaya\Template\Tag\Image::appendTo
   */
  public function testAppendToWithExistingTag() {
    $image = new Image(
      '<papaya:media src="d74f6d0324f5d90b23bb3771200ddf7d" width="60" height="96" resize="max"/>'
    );
    $document = new \Papaya\XML\Document();
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
}
