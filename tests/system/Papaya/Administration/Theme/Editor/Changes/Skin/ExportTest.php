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

namespace Papaya\Administration\Theme\Editor\Changes\Skin;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class ExportTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Administration\Theme\Editor\Changes\Skin\Export
   */
  public function testAppendTo() {
    $response = $this->createMock(\Papaya\Response::class);
    $response
      ->expects($this->once())
      ->method('setStatus')
      ->with(200);
    $response
      ->expects($this->once())
      ->method('sendHeader')
      ->with('Content-Disposition: attachment; filename="theme skin.xml"');
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with('application/octet-stream');
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with('application/octet-stream');
    $response
      ->expects($this->once())
      ->method('content')
      ->with($this->isInstanceOf(\Papaya\Response\Content\Text::class));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme')
      ->will($this->returnValue($this->createMock(\Papaya\Content\Structure::class)));

    $document = $this->createMock(\Papaya\XML\Document::class);
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue(/** @lang XML */
        '<theme/>'));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\Content\Theme\Skin::class);
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->with(0);
    $themeSet
      ->expects($this->any())
      ->method('offsetGet')
      ->will(
        $this->returnValueMap(
          array(
            array('theme', 'theme'),
            array('title', 'skin')
          )
        )
      );
    $themeSet
      ->expects($this->once())
      ->method('getValuesXml')
      ->with($this->isInstanceOf(\Papaya\Content\Structure::class))
      ->will($this->returnValue($document));

    $export = new Export($themeSet, $themeHandler);
    $export->papaya(
      $this->mockPapaya()->application(
        array(
          'response' => $response
        )
      )
    );
    $export->getXML();
  }
}
