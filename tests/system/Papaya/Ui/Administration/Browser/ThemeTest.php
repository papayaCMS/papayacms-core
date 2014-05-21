<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiAdministrationBrowserThemeTest extends PapayaTestCase {

  private $_themeConfiguration = array();

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXmlListButtons
  */
  public function testGetXmlListButtons() {
    $expected = '<buttons>'.LF;
    $expected .= '<right>'.LF;
    $expected .= '<button hint="List" glyph="/categories-view-list.png"'.
      ' href="test.html?test[mode_view]=list" down="down"/>'.LF;
    $expected .= '<button hint="Tiles" glyph="/categories-view-tiles.png"'.
      ' href="test.html?test[mode_view]=tile" />'.LF;
    $expected .= '<button hint="Thumbnails" glyph="/categories-view-icons.png"'.
      ' href="test.html?test[mode_view]=thumbs" />'.LF;
    $expected .= '</right>'.LF.'</buttons>'.LF;
    $themeObject = $this->_getThemeObjectFixture();
    $this->assertEquals($expected, $themeObject->getXmlListButtons('list'));
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXmlListViewElement
  * @dataProvider getXmlListViewElementDataProvider
  */
  public function testGetXmlListViewElement($currentMode, $thumbType, $params, $data, $selected) {
    $directory = 'theme3';
    $configuration = array(
      'name' => 'Theme 3',
      'templates' => 'theme-3',
      'version' => '1.0',
      'date' => '2010-04-07',
      'author' => 'Papaya Software GmbH',
      'description' => 'This is the 3rd test theme.',
      'thumbMedium' => 'thumb1_theme3.jpg',
      'thumbLarge' => 'thumb2_theme3.jpg'
    );
    $expected = '<listitem'.
      ' href="test.html?test[mode_view]='.$currentMode.'&amp;test[theme]=theme3"'.
      ' title="Theme 3" subtitle="theme3" hint="Theme 3" ';
    if ($currentMode == 'list') {
      $expected .= 'image="/categories-content.png"';
    } else {
      $expected .= sprintf('image="/%s"', $configuration[$thumbType]);
    }
    $expected .= ($selected) ? ' selected="selected"' : '';
    if ($currentMode == 'list') {
      $expected .= ' >'.LF.'<subitem>2010-04-07</subitem>'.LF.'<subitem>1.0</subitem>'.LF;
      $expected .= '<subitem>Papaya Software GmbH</subitem>'.LF;
      $expected .= '<subitem>This is the 3rd test theme.</subitem>'.LF;
    } else {
      $expected .= ' >'.LF;
    }
    $expected .= '</listitem>'.LF;
    $path = dirname(__FILE__).'/TestData';
    $themeObject = $this->_getThemeObjectFixture($params, $data);
    $themeObject->setThemesPath($path);
    $themeHandlerObject = $this->getMock('PapayaThemeHandler', array('getUrl'));
    $themeHandlerObject
      ->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('/'));
    $themeObject->setThemeHandlerObject($themeHandlerObject);
    $result = $themeObject->getXmlListViewElement($directory, $configuration, $currentMode);
    $this->assertEquals($expected, $result);
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXmlListView
  */
  public function testGetXmlListView() {
    $themes = array(
      'theme3' => array(
        'name' => 'Theme 3',
        'thumbMedium' => 'thumb1_theme3.jpg',
        'thumbLarge' => 'thumb2_theme3.jpg'
      )
    );
    $path = dirname(__FILE__).'/TestData';
    $expected = '<listview mode="list">'.LF;
    $expected .= '<cols>'.LF.'<col>Title / Directory</col>'.LF.'<col>Date</col>'.LF;
    $expected .= '<col>Version</col>'.LF.'<col>Author</col>'.LF.'<col>Description</col>'.LF;
    $expected .= '</cols>'.LF.'<items>'.LF.'</items>'.LF;
    $expected .= '</listview>'.LF;
    $themeObject = new PapayaUiAdministrationBrowserTheme_ProxyForListView(
      $this->_getMockOwnerObjectFixture(), array(), 'test'
    );
    $themeObject->papaya($this->mockPapaya()->application());
    $themeObject->setThemesPath($path);
    $this->assertEquals($expected, $themeObject->getXmlListView($themes, 'list'));
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXmlDialog
  */
  public function testGetXmlDialog() {
    $themes = array(
      'theme3' => array(
        'name' => 'Theme 3',
        'templates' => 'theme-3',
        'thumbMedium' => 'thumb1_theme3.jpg',
        'thumbLarge' => 'thumb2_theme3.jpg'
      )
    );
    $hiddenFields = array(
      'test' => 1
    );
    $expected = '<dialog title="Themes (browser)" action="test.html" id="themeBrowser">'.LF;
    $expected .= '<input type="hidden" name="test[test]" value="1" />'.LF;
    $expected .= '<dlgbutton value="Save"/>'.LF.'</dialog>'.LF;
    $themeObject = new PapayaUiAdministrationBrowserTheme_ProxyForDialog(
      $this->_getMockOwnerObjectFixture(), array(), 'test', array(), NULL, $hiddenFields
    );
    $themeObject->papaya($this->mockPapaya()->application());
    $this->assertEquals($expected, $themeObject->getXmlDialog($themes, 'thumbs'));
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXmlThemeDetails
  */
  public function testGetXmlThemeDetails() {
    $themes = array(
      'theme3' => array(
        'name' => 'Theme 3',
        'author' => 'Papaya Software GmbH',
        'date' => '2010-04-08',
        'version' => '1.0',
        'description' => 'This is a test template.',
        'templates' => 'theme-3'
      )
    );
    $expected = '<sheet>'.LF.'<header>'.LF.'<lines>'.LF;
    $expected .= '<line class="headertitle">Theme 3</line>'.LF.'</lines>'.LF.'</header>'.LF;
    $expected .= '<text>'.LF.'<div style="padding: 10px;">'.LF;
    $expected .= '<p><strong>Version</strong>: 1.0</p>'.LF;
    $expected .= '<p><strong>Date</strong>: 2010-04-08</p>'.LF;
    $expected .= '<p><strong>Author</strong>: Papaya Software GmbH</p>'.LF;
    $expected .= '<p><strong>Template folder</strong>: theme-3</p>'.LF;
    $expected .= '<p><strong>Description</strong>: This is a test template.</p>'.LF;
    $expected .= '</div>'.LF.'</text>'.LF.'</sheet>'.LF;
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemes($themes);
    $this->assertEquals($expected, $themeObject->getXmlThemeDetails('theme3'));
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXml
  */
  public function testGetXmlWithEmptyThemeResult() {
    $path = dirname(__FILE__).'/TestData/theme1/';
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemesPath($path);
    $this->assertEquals('', $themeObject->getXml());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXml
  * @medium
  */
  public function testGetXmlWithThemeInParamsDefaultViewMode() {
    $path = dirname(__FILE__).'/TestData/';
    $expected =
      '<sheet>'.LF.'<header>'.LF.'<lines>'.LF.
      '<line class="headertitle">Theme 3</line>'.LF.'</lines>'.LF.'</header>'.LF.
      '<text>'.LF.'<div style="padding: 10px;">'.LF.
      '<p><strong>Template folder</strong>: theme3</p>'.LF.'</div>'.LF.'</text>'.LF.
      '</sheet>'.LF.
      '<dialog title="Themes (browser)" action="test.html" id="themeBrowser">'.LF.
      '<input type="hidden" name="test[browser]" value="theme3" />'.LF.
      '<input type="hidden" name="test[PAPAYA_LAYOUT_TEMPLATES]" value="theme3" />'.LF.
      '<listview mode="thumbs">'.LF.'<buttons>'.LF.'<right>'.LF.
      '<button hint="List" glyph="/categories-view-list.png"'.
      ' href="test.html?test[mode_view]=list&amp;test[theme]=theme3" />'.LF.
      '<button hint="Tiles" glyph="/categories-view-tiles.png"'.
      ' href="test.html?test[mode_view]=tile&amp;test[theme]=theme3" />'.LF.
      '<button hint="Thumbnails" glyph="/categories-view-icons.png"'.
      ' href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme3"'.
      ' down="down"/>'.LF.
      '</right>'.LF.'</buttons>'.LF.'<cols>'.LF.'<col>Title / Directory</col>'.LF.
      '</cols>'.LF.'<items>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme1"'.
      ' title="Theme 1" subtitle="theme1" hint="Theme 1" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme2"'.
      ' title="Theme 2" subtitle="theme2" hint="Theme 2" image="/thumb2_theme2.jpg" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme3"'.
      ' title="Theme 3" subtitle="theme3" hint="Theme 3" image="/thumb2_theme3.jpg"'.
      ' selected="selected" >'.LF.
      '</listitem>'.LF.'</items>'.LF.'</listview>'.LF.
      '<dlgbutton value="Save"/>'.LF.'</dialog>'.LF;

    $themeObject = $this->_getThemeObjectFixture(array('theme' => 'theme3'), array());
    $themeObject->setThemesPath($path);
    $configurationObject = $this->getMock('PapayaUiAdministrationBrowserThemeConfiguration');
    $configurationObject
      ->expects($this->any())
      ->method('getThemeConfiguration')
      ->will(
        $this->returnCallback(array($this, 'callbackGetThemeConfiguration'))
      );
    $themeObject->setThemeConfigurationObject($configurationObject);
    $themeHandlerObject = $this->getMock('PapayaThemeHandler');
    $themeHandlerObject
      ->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('/'));
    $themeObject->setThemeHandlerObject($themeHandlerObject);
    $this->assertEquals($expected, $themeObject->getXml());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXml
  * @medium
  */
  public function testGetXmlWithThemeInDataDefaultViewMode() {
    $path = dirname(__FILE__).'/TestData/';
    $expected =
      '<sheet>'.LF.'<header>'.LF.'<lines>'.LF.
      '<line class="headertitle">Theme 3</line>'.LF.'</lines>'.LF.'</header>'.LF.
      '<text>'.LF.'<div style="padding: 10px;">'.LF.
      '<p><strong>Template folder</strong>: theme3</p>'.LF.'</div>'.LF.'</text>'.LF.
      '</sheet>'.LF.
      '<dialog title="Themes (browser)" action="test.html" id="themeBrowser">'.LF.
      '<input type="hidden" name="test[browser]" value="theme3" />'.LF.
      '<input type="hidden" name="test[PAPAYA_LAYOUT_TEMPLATES]" value="theme3" />'.LF.
      '<listview mode="thumbs">'.LF.'<buttons>'.LF.'<right>'.LF.
      '<button hint="List" glyph="/categories-view-list.png"'.
      ' href="test.html?test[mode_view]=list" />'.LF.
      '<button hint="Tiles" glyph="/categories-view-tiles.png"'.
      ' href="test.html?test[mode_view]=tile" />'.LF.
      '<button hint="Thumbnails" glyph="/categories-view-icons.png"'.
      ' href="test.html?test[mode_view]=thumbs"'.
      ' down="down"/>'.LF.
      '</right>'.LF.'</buttons>'.LF.'<cols>'.LF.'<col>Title / Directory</col>'.LF.
      '</cols>'.LF.'<items>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme1"'.
      ' title="Theme 1" subtitle="theme1" hint="Theme 1" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme2"'.
      ' title="Theme 2" subtitle="theme2" hint="Theme 2" image="/thumb2_theme2.jpg" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme3"'.
      ' title="Theme 3" subtitle="theme3" hint="Theme 3" image="/thumb2_theme3.jpg"'.
      ' selected="selected" >'.LF.
      '</listitem>'.LF.'</items>'.LF.'</listview>'.LF.
      '<dlgbutton value="Save"/>'.LF.'</dialog>'.LF;

    $themeObject = $this->_getThemeObjectFixture(array(), array('opt_value' => 'theme3'));
    $themeObject->setThemesPath($path);
    $configurationObject = $this->getMock('PapayaUiAdministrationBrowserThemeConfiguration');
    $configurationObject
      ->expects($this->any())
      ->method('getThemeConfiguration')
      ->will(
        $this->returnCallback(array($this, 'callbackGetThemeConfiguration'))
      );
    $themeObject->setThemeConfigurationObject($configurationObject);
    $themeHandlerObject = $this->getMock('PapayaThemeHandler');
    $themeHandlerObject
      ->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('/'));
    $themeObject->setThemeHandlerObject($themeHandlerObject);
    $this->assertEquals($expected, $themeObject->getXml());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXml
  * @medium
  */
  public function testGetXmlWithoutThemeDefaultViewMode() {
    $path = dirname(__FILE__).'/TestData/';
    $expected =
      '<dialog title="Themes (browser)" action="test.html" id="themeBrowser">'.LF.
      '<input type="hidden" name="test[browser]" value="" />'.LF.
      '<listview mode="thumbs">'.LF.'<buttons>'.LF.'<right>'.LF.
      '<button hint="List" glyph="/categories-view-list.png"'.
      ' href="test.html?test[mode_view]=list" />'.LF.
      '<button hint="Tiles" glyph="/categories-view-tiles.png"'.
      ' href="test.html?test[mode_view]=tile" />'.LF.
      '<button hint="Thumbnails" glyph="/categories-view-icons.png"'.
      ' href="test.html?test[mode_view]=thumbs" down="down"/>'.LF.
      '</right>'.LF.'</buttons>'.LF.'<cols>'.LF.'<col>Title / Directory</col>'.LF.
      '</cols>'.LF.'<items>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme1"'.
      ' title="Theme 1" subtitle="theme1" hint="Theme 1" >'.LF.'</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme2"'.
      ' title="Theme 2" subtitle="theme2" hint="Theme 2" image="/thumb2_theme2.jpg" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme3"'.
      ' title="Theme 3" subtitle="theme3" hint="Theme 3" image="/thumb2_theme3.jpg" >'.LF.
      '</listitem>'.LF.'</items>'.LF.'</listview>'.LF.
      '<dlgbutton value="Save"/>'.LF.'</dialog>'.LF;

    $themeObject = $this->_getThemeObjectFixture(array(), array());
    $themeObject->setThemesPath($path);
    $configurationObject = $this->getMock('PapayaUiAdministrationBrowserThemeConfiguration');
    $configurationObject
      ->expects($this->any())
      ->method('getThemeConfiguration')
      ->will(
        $this->returnCallback(array($this, 'callbackGetThemeConfiguration'))
      );
    $themeObject->setThemeConfigurationObject($configurationObject);
    $themeHandlerObject = $this->getMock('PapayaThemeHandler');
    $themeHandlerObject
      ->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('/'));
    $themeObject->setThemeHandlerObject($themeHandlerObject);
    $this->assertEquals($expected, $themeObject->getXml());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getXml
  * @medium
  */
  public function testGetXmlWithViewModeSet() {
    $params = array('mode_view' => 'thumbs');
    $path = dirname(__FILE__).'/TestData/';
    $themeObject = $this->_getThemeObjectFixture($params);
    $themeObject->setThemesPath($path);
    $expected =
      '<dialog title="Themes (browser)" action="test.html" id="themeBrowser">'.LF.
      '<input type="hidden" name="test[browser]" value="" />'.LF.
      '<listview mode="thumbs">'.LF.
      '<buttons>'.LF.
      '<right>'.LF.
      '<button hint="List" glyph="/categories-view-list.png"'.
      ' href="test.html?test[mode_view]=list" />'.LF.
      '<button hint="Tiles" glyph="/categories-view-tiles.png"'.
      ' href="test.html?test[mode_view]=tile" />'.LF.
      '<button hint="Thumbnails" glyph="/categories-view-icons.png"'.
      ' href="test.html?test[mode_view]=thumbs" down="down"/>'.LF.
      '</right>'.LF.
      '</buttons>'.LF.
      '<cols>'.LF.
      '<col>Title / Directory</col>'.LF.
      '</cols>'.LF.
      '<items>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme1"'.
      ' title="Theme 1" subtitle="theme1" hint="Theme 1" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme2"'.
      ' title="Theme 2" subtitle="theme2" hint="Theme 2" image="/thumb2_theme2.jpg" >'.LF.
      '</listitem>'.LF.
      '<listitem href="test.html?test[mode_view]=thumbs&amp;test[theme]=theme3"'.
      ' title="Theme 3" subtitle="theme3" hint="Theme 3" image="/thumb2_theme3.jpg" >'.LF.
      '</listitem>'.LF.
      '</items>'.LF.
      '</listview>'.LF.
      '<dlgbutton value="Save"/>'.LF.
      '</dialog>'.LF;

    $configurationObject = $this->getMock('PapayaUiAdministrationBrowserThemeConfiguration');
    $configurationObject
      ->expects($this->any())
      ->method('getThemeConfiguration')
      ->will(
        $this->returnCallback(array($this, 'callbackGetThemeConfiguration'))
      );
    $themeObject->setThemeConfigurationObject($configurationObject);
    $themeHandlerObject = $this->getMock('PapayaThemeHandler');
    $themeHandlerObject
      ->expects($this->any())
      ->method('getUrl')
      ->will($this->returnValue('/'));
    $themeObject->setThemeHandlerObject($themeHandlerObject);
    $this->assertEquals($expected, $themeObject->getXml());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getThemesPath
  */
  public function testGetThemesPathWithThemesPathAlreadySet() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemesPath('test');
    $this->assertEquals('test', $themeObject->getThemesPath());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getThemesPath
  */
  public function testGetThemesPath() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeHandlerObject = $this->getMock('PapayaThemeHandler');
    $themeHandlerObject
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue('/test/'));
    $themeObject->setThemeHandlerObject($themeHandlerObject);
    $this->assertEquals('/test/', $themeObject->getThemesPath());
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::setThemesPath
  */
  public function testSetThemesPath() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemesPath('test');
    $this->assertAttributeEquals('test', '_themesPath', $themeObject);
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getThemes
  */
  public function testGetThemes() {
    $expected = array(
      'theme1' => array(
        'name' => 'Theme 1',
        'templates' => 'theme1',
        'thumbMedium' => '',
        'thumbLarge' => ''
      ),
      'theme2' => array(
        'name' => 'Theme 2',
        'templates' => 'theme2',
        'thumbMedium' => 'thumb1_theme2.jpg',
        'thumbLarge' => 'thumb2_theme2.jpg'
      ),
      'theme3' => array(
        'name' => 'Theme 3',
        'templates' => 'theme3',
        'thumbMedium' => 'thumb1_theme3.jpg',
        'thumbLarge' => 'thumb2_theme3.jpg'
      )
    );
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemesPath(dirname(__FILE__).'/TestData/');
    $configurationObject = $this->getMock('PapayaUiAdministrationBrowserThemeConfiguration');
    $configurationObject
      ->expects($this->exactly(3))
      ->method('getThemeConfiguration')
      ->with($this->isType('string'))
      ->will(
        $this->returnCallback(array($this, 'callbackGetThemeConfiguration'))
      );
    $themeObject->setThemeConfigurationObject($configurationObject);
    $this->assertEquals($expected, $themeObject->getThemes());
  }

  public function callbackGetThemeConfiguration($directory) {
    $themes = array(
      'theme3' => array(
        'name' => 'Theme 3',
        'templates' => 'theme3',
        'thumbMedium' => 'thumb1_theme3.jpg',
        'thumbLarge' => 'thumb2_theme3.jpg'
      ),
      'theme2' => array(
        'name' => 'Theme 2',
        'templates' => 'theme2',
        'thumbMedium' => 'thumb1_theme2.jpg',
        'thumbLarge' => 'thumb2_theme2.jpg'
      ),
      'theme1' => array(
        'name' => 'Theme 1',
        'templates' => 'theme1',
        'thumbMedium' => '',
        'thumbLarge' => ''
      )
    );
    return $themes[basename($directory)];
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::setThemes
  */
  public function testSetThemes() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemes(array(1, 2, 3));
    $this->assertAttributeEquals(array(1, 2, 3), '_themes', $themeObject);
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getThemeConfigurationObject
  */
  public function testGetThemeConfigurationObject() {
    $themeObject = $this->_getThemeObjectFixture();
    $configuration = $themeObject->getThemeConfigurationObject();
    $this->assertTrue(
      $configuration instanceof PapayaUiAdministrationBrowserThemeConfiguration
    );
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::setThemeConfigurationObject
  */
  public function testSetThemeConfigurationObject() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemeConfigurationObject('test');
    $this->assertAttributeEquals('test', '_configurationObject', $themeObject);
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::getThemeHandlerObject
  */
  public function testGetThemeHandlerObject() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeHandler = $themeObject->getThemeHandlerObject();
    $this->assertTrue($themeHandler instanceof PapayaThemeHandler);
  }

  /**
  * @covers PapayaUiAdministrationBrowserTheme::setThemeHandlerObject
  */
  public function testSetThemeHandlerObject() {
    $themeObject = $this->_getThemeObjectFixture();
    $themeObject->setThemeHandlerObject('test');
    $this->assertAttributeEquals('test', '_themeHandlerObject', $themeObject);
  }

  /***************************************************************************/
  /** DataProvider                                                           */
  /***************************************************************************/

  public static function getXmlListViewElementDataProvider() {
    return array(
      'list view with small thumb' => array(
        'list', 'thumbMedium', array('theme' => 'theme3'), array(), TRUE
      ),
      'tiles view with small thumb' => array(
        'tile', 'thumbMedium', array(), array('opt_value' => 'theme3'), TRUE
      ),
      'thumbnails view with larger thumb' => array('thumbs', 'thumbLarge', array(), array(), FALSE)
    );
  }

  /***************************************************************************/
  /* Fixtures                                                                */
  /***************************************************************************/

  private function _getThemeObjectFixture($params = NULL, $data = NULL) {
    $owner = $this->_getMockOwnerObjectFixture();
    $params = (isset($params)) ? $params : array();
    $themeObject = new PapayaUiAdministrationBrowserTheme($owner, $params, 'test', $data);
    $themeObject->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->_getMockImagesObjectFixture()
        )
      )
    );
    return $themeObject;
  }

  private function _getMockOwnerObjectFixture() {
    $owner = $this->getMock('base_object', array('_gt', 'addMsg'));
    $owner
      ->expects($this->any())
      ->method('_gt')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $owner
      ->expects($this->any())
      ->method('addMsg')
      ->withAnyParameters();
    return $owner;
  }

  private function _getMockImagesObjectFixture() {
    $images = $this->getMock('PapayaUiImages', array('offsetGet'));
    $images
      ->expects($this->any())
      ->method('offsetGet')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackImageFile')));
    return $images;
  }

  public function callbackImageFile($index) {
    return '/'.$index.'.png';
  }
}

class PapayaUiAdministrationBrowserTheme_ProxyForListView
  extends PapayaUiAdministrationBrowserTheme {

  public function getXmlListButtons() {
    return '';
  }

  public function getXmlListViewElement() {
    return '';
  }
}

class PapayaUiAdministrationBrowserTheme_ProxyForDialog
  extends PapayaUiAdministrationBrowserTheme {

  public function getXmlListButtons() {
    return '';
  }

  public function getXmlListViewElement() {
    return '';
  }

  public function getXmlListView() {
    return '';
  }
}