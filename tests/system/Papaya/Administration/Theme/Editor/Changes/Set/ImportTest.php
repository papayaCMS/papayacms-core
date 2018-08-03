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

use Papaya\Administration\Theme\Editor\Changes\Set\Import;
use Papaya\Content\Structure;
use Papaya\Content\Theme\Set;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesSetImportTest extends \PapayaTestCase {

  /**
   * @covers Import
   */
  public function testCreateDialog() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(
            array(
              'theme' => 'themename'
            )
          )
        )
      )
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post" enctype="multipart/form-data">
        <title caption="Import"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="yes"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="set_import"/>
        <input type="hidden" name="theme" value="themename"/>
        <input type="hidden" name="set_id" value="0"/>
        <input type="hidden" name="confirmation" value="ed3242472cac221a5561cc07245f38b4"/>
        <input type="hidden" name="token"/>
        <field caption="File" class="DialogFieldFileTemporary" error="no" mandatory="yes">
          <input type="file" name="values[file]"/>
        </field>
        <button type="submit" align="right">Upload</button>
      </dialog-box>',
      $import->dialog()->getXml()
    );
  }

  /**
   * @covers Import
   */
  public function testCreateDialogWithSelectedSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(
            array(
              'theme' => 'themename',
              'set_id' => 42
            )
          )
        )
      )
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post" enctype="multipart/form-data">
        <title caption="Import"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="yes"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="set_import"/>
        <input type="hidden" name="theme" value="themename"/>
        <input type="hidden" name="set_id" value="42"/>
        <input type="hidden" name="confirmation" value="19a554e689a9367114447ffa7c40bfa3"/>
        <input type="hidden" name="token"/>
        <field caption="File" class="DialogFieldFileTemporary" error="no" mandatory="yes">
          <input type="file" name="values[file]"/>
        </field>
        <field caption="Replace current set." class="DialogFieldSelectRadio" error="no" mandatory="yes">
          <select name="values[confirm_replace]" type="radio">
            <option value="1">Yes</option><option value="0" selected="selected">No</option>
          </select>
        </field>
        <button type="submit" align="right">Upload</button>
      </dialog-box>',
      $import->dialog()->getXml()
    );
  }

  /**
   * @covers Import
   */
  public function testOnValidationSuccessWithoutTheme() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog\Field\File\Temporary $uploadField */
    $uploadField = $this
      ->getMockBuilder(\Papaya\UI\Dialog\Field\File\Temporary::class)
      ->disableOriginalConstructor()
      ->getMock();
    $import = new Import($themeSet, $themeHandler);
    $import->papaya($this->mockPapaya()->application());
    $this->assertFalse(
      $import->onValidationSuccess($uploadField)
    );
  }

  /**
   * @covers Import
   */
  public function testOnValidationSuccessWithInvalidXml() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog\Field\File\Temporary $uploadField */
    $uploadField = $this->getUploadFieldFixture();
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array('request' => $this->mockPapaya()->request(array('theme' => 'themename')))
      )
    );
    $this->assertFalse(
      $import->onValidationSuccess($uploadField)
    );
  }

  /**
   * @covers Import
   */
  public function testOnValidationSuccessWithValidXml() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(Structure::class)));
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    $themeSet
      ->expects($this->once())
      ->method('assign')
      ->with(array('title' => '* Imported Set', 'theme' => 'themename'));
    $themeSet
      ->expects($this->once())
      ->method('setValuesXml');
    $themeSet
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode(/** @lang XML */'<theme/>'));
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(array('theme' => 'themename')),
          'messages' => $messages
        )
      )
    );
    $this->assertTrue(
      $import->onValidationSuccess($uploadField)
    );
  }

  /**
   * @covers Import
   */
  public function testOnValidationSuccessWithValidXmlNotSaved() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(Structure::class)));
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    $themeSet
      ->expects($this->once())
      ->method('assign')
      ->with(array('title' => '* Imported Set', 'theme' => 'themename'));
    $themeSet
      ->expects($this->once())
      ->method('setValuesXml');
    $themeSet
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode(/** @lang XML */'<theme/>'));
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(array('theme' => 'themename'))
        )
      )
    );
    $this->assertFalse(
      $import->onValidationSuccess($uploadField)
    );
  }

  /**
   * @covers Import
   */
  public function testOnValidationSuccessWithValidXmlImportingIntoExistingSet() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(Structure::class)));
    /** @var PHPUnit_Framework_MockObject_MockObject|Set $themeSet */
    $themeSet = $this->createMock(Set::class);
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $themeSet
      ->expects($this->once())
      ->method('setValuesXml');
    $themeSet
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode(/** @lang XML */'<theme/>'));
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(
            array(
              'theme' => 'themename',
              'set_id' => 42,
              'values' => array('confirm_replace' => '1')
            )
          ),
          'messages' => $messages
        )
      )
    );
    $this->assertTrue(
      $import->onValidationSuccess($uploadField)
    );
  }

  /**
   * @param string $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog\Field\File\Temporary
   */
  public function getUploadFieldFixture($data = 'data://text/xml,') {
    $file = $this
      ->getMockBuilder(\Papaya\Request\Parameter\File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('offsetGet')
      ->with('temporary')
      ->will($this->returnValue($data));
    $uploadField = $this
      ->getMockBuilder(\Papaya\UI\Dialog\Field\File\Temporary::class)
      ->disableOriginalConstructor()
      ->getMock();
    $uploadField
      ->expects($this->once())
      ->method('file')
      ->will($this->returnValue($file));
    return $uploadField;
  }
}
