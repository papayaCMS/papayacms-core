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

namespace Papaya\UI\Sheet {

  use Papaya\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Sheet\Subtitles
   */
  class SubtitlesTest extends TestCase {

    public function testSubtitlesWithStrings() {
      $subTitles = new Subtitles(['one']);
      $subTitles->addString('two');
      $this->assertXmlFragmentEqualsXmlFragment(
        '  <subtitle>one</subtitle><subtitle>two</subtitle>',
        $subTitles->getXML()
      );
    }

    public function testSubtitlesWithSubtitleObjectInConstructor() {
      $subTitles = new Subtitles([new Subtitle('one')]);
      $this->assertXmlFragmentEqualsXmlFragment(
        '  <subtitle>one</subtitle>',
        $subTitles->getXML()
      );
    }

    public function testSubtitlesWithSubtitleAdded() {
      $subTitles = new Subtitles();
      $subTitles->add(new Subtitle('one'));
      $this->assertXmlFragmentEqualsXmlFragment(
        '  <subtitle>one</subtitle>',
        $subTitles->getXML()
      );
    }
  }
}
