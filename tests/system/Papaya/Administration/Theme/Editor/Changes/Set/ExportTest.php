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

use Papaya\Administration\Theme\Editor\Changes\Set\Export;
use Papaya\Content\Structure;
use Papaya\Content\Theme\Set;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesSetExportTest extends \PapayaTestCase {

  /**
   * @covers Export
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
      ->with('Content-Disposition: attachment; filename="theme set.xml"');
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

    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(\PapayaThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme')
      ->will($this->returnValue($this->createMock(Structure::class)));

    $document = $this->createMock(\PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue(/** @lang XML */'<theme/>'));

    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
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
            array('title', 'set')
          )
        )
      );
    $themeSet
      ->expects($this->once())
      ->method('getValuesXml')
      ->with($this->isInstanceOf(Structure::class))
      ->will($this->returnValue($document));

    $export = new Export($themeSet, $themeHandler);
    $export->papaya(
      $this->mockPapaya()->application(
        array(
          'response' => $response
        )
      )
    );
    $export->getXml();
  }
}
