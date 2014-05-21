<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaAdministrationThemeEditorChangesSetImportTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testCreateDialog() {
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeSet = $this->getMock('PapayaContentThemeSet');
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
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post" enctype="multipart/form-data">'.
        '<title caption="Import"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="yes"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="1"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="cmd" value="set_import"/>'.
        '<input type="hidden" name="theme" value="themename"/>'.
        '<input type="hidden" name="set_id" value="0"/>'.
        '<input type="hidden" name="confirmation" value="ed3242472cac221a5561cc07245f38b4"/>'.
        '<input type="hidden" name="token"/>'.
        '<field caption="File" class="DialogFieldFileTemporary" error="no" mandatory="yes">'.
          '<input type="file" name="values[file]"/>'.
        '</field>'.
        '<button type="submit" align="right">Upload</button>'.
      '</dialog-box>',
      $import->dialog()->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testCreateDialogWithSelectedSet() {
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeSet = $this->getMock('PapayaContentThemeSet');
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
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post" enctype="multipart/form-data">'.
        '<title caption="Import"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="yes"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="1"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="cmd" value="set_import"/>'.
        '<input type="hidden" name="theme" value="themename"/>'.
        '<input type="hidden" name="set_id" value="42"/>'.
        '<input type="hidden" name="confirmation" value="19a554e689a9367114447ffa7c40bfa3"/>'.
        '<input type="hidden" name="token"/>'.
        '<field caption="File" class="DialogFieldFileTemporary" error="no" mandatory="yes">'.
          '<input type="file" name="values[file]"/>'.
        '</field>'.
        '<field caption="Replace current set." class="DialogFieldSelectRadio" error="no" mandatory="yes">'.
          '<select name="values[confirm_replace]" type="radio">'.
            '<option value="1">Yes</option><option value="0" selected="selected">No</option>'.
          '</select>'.
        '</field>'.
        '<button type="submit" align="right">Upload</button>'.
      '</dialog-box>',
      $import->dialog()->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetImport
   */
  public function testOnValidationSuccessWithoutTheme() {
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeSet = $this->getMock('PapayaContentThemeSet');
    $uploadField = $this
      ->getMockBuilder('PapayaUiDialogFieldFileTemporary')
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
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeSet = $this->getMock('PapayaContentThemeSet');
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
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->getMock('PapayaContentStructure')));
    $themeSet = $this->getMock('PapayaContentThemeSet');
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
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->getMock('PapayaContentStructure')));
    $themeSet = $this->getMock('PapayaContentThemeSet');
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
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('themename')
      ->will($this->returnValue($this->getMock('PapayaContentStructure')));
    $themeSet = $this->getMock('PapayaContentThemeSet');
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

  public function getUploadFieldFixture($data = 'data://text/xml,') {
    $file = $this
      ->getMockBuilder('PapayaRequestParameterFile')
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->once())
      ->method('offsetGet')
      ->with('temporary')
      ->will($this->returnValue($data));
    $uploadField = $this
      ->getMockBuilder('PapayaUiDialogFieldFileTemporary')
      ->disableOriginalConstructor()
      ->getMock();
    $uploadField
      ->expects($this->once())
      ->method('file')
      ->will($this->returnValue($file));
    return $uploadField;
  }
}
