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

namespace Papaya\CMS\Administration\Theme\Editor\Changes\Skin;

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class ImportTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testCreateDialog() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
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
        <input type="hidden" name="cmd" value="skin_import"/>
        <input type="hidden" name="theme" value="themename"/>
        <input type="hidden" name="skin_id" value="0"/>
        <input type="hidden" name="confirmation" value="26f3db711901a5e90466f71dad832a08"/>
        <input type="hidden" name="token"/>
        <field caption="File" class="DialogFieldFileTemporary" error="no" mandatory="yes">
          <input type="file" name="values[file]"/>
        </field>
        <button type="submit" align="right">Upload</button>
      </dialog-box>',
      $import->dialog()->getXML()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testCreateDialogWithSelectedSkin() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(
            array(
              'theme' => 'themename',
              'skin_id' => 42
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
        <input type="hidden" name="cmd" value="skin_import"/>
        <input type="hidden" name="theme" value="themename"/>
        <input type="hidden" name="skin_id" value="42"/>
        <input type="hidden" name="confirmation" value="57eb4c9c7deff8a7d5025395cd1ef83a"/>
        <input type="hidden" name="token"/>
        <field caption="File" class="DialogFieldFileTemporary" error="no" mandatory="yes">
          <input type="file" name="values[file]"/>
        </field>
        <field caption="Replace current skin." class="DialogFieldSelectRadio" error="no" mandatory="yes">
          <select name="values[confirm_replace]" type="radio">
            <option value="1">Yes</option><option value="0" selected="selected">No</option>
          </select>
        </field>
        <button type="submit" align="right">Upload</button>
      </dialog-box>',
      $import->dialog()->getXML()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testOnValidationSuccessWithoutTheme() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
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
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testOnValidationSuccessWithInvalidXml() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
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
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testOnValidationSuccessWithValidXml() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('displayInfo')
      ->with('Values imported.');
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(\Papaya\CMS\Content\Structure::class)));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
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
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode(/** @lang XML */
        '<theme/>'));
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
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testOnValidationSuccessWithValidXmlNotSaved() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(\Papaya\CMS\Content\Structure::class)));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
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
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode(/** @lang XML */
        '<theme/>'));
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
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Import
   */
  public function testOnValidationSuccessWithValidXmlImportingIntoExistingSet() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('displayInfo')
      ->with('Values imported.');
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Theme\Handler $themeHandler */
    $themeHandler = $this->createMock(\Papaya\CMS\Theme\Handler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(\Papaya\CMS\Content\Structure::class)));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Theme\Skin $themeSet */
    $themeSet = $this->createMock(\Papaya\CMS\Content\Theme\Skin::class);
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
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode(/** @lang XML */
        '<theme/>'));
    $import = new Import($themeSet, $themeHandler);
    $import->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(
            array(
              'theme' => 'themename',
              'skin_id' => 42,
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
