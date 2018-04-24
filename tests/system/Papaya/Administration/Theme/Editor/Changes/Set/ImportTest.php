<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesSetImportTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testCreateDialog() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
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
      // language=xml
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
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testCreateDialogWithSelectedSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
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
      // language=xml
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
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testOnValidationSuccessWithoutTheme() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiDialogFieldFileTemporary $uploadField */
    $uploadField = $this
      ->getMockBuilder(PapayaUiDialogFieldFileTemporary::class)
      ->disableOriginalConstructor()
      ->getMock();
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
    $import->papaya($this->mockPapaya()->application());
    $this->assertFalse(
      $import->onValidationSuccess($uploadField)
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testOnValidationSuccessWithInvalidXml() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiDialogFieldFileTemporary $uploadField */
    $uploadField = $this->getUploadFieldFixture();
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
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
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testOnValidationSuccessWithValidXml() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(PapayaContentStructure::class)));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
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
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode('<theme/>'));
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
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
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testOnValidationSuccessWithValidXmlNotSaved() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(PapayaContentStructure::class)));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
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
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode('<theme/>'));
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
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
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testOnValidationSuccessWithValidXmlImportingIntoExistingSet() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplay::class));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaThemeHandler $themeHandler */
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->createMock(PapayaContentStructure::class)));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
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
    $uploadField = $this->getUploadFieldFixture('data://text/xml,'.urlencode('<theme/>'));
    $import = new PapayaAdministrationThemeEditorChangesSetImport($themeSet, $themeHandler);
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
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaUiDialogFieldFileTemporary
   */
  public function getUploadFieldFixture($data = 'data://text/xml,') {
    $file = $this
      ->getMockBuilder(PapayaRequestParameterFile::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('offsetGet')
      ->with('temporary')
      ->will($this->returnValue($data));
    $uploadField = $this
      ->getMockBuilder(PapayaUiDialogFieldFileTemporary::class)
      ->disableOriginalConstructor()
      ->getMock();
    $uploadField
      ->expects($this->once())
      ->method('file')
      ->will($this->returnValue($file));
    return $uploadField;
  }
}
