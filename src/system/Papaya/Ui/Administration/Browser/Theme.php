<?php
/**
* Papaya Interface Administration Browser for themes
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Theme.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya Interface Administration Browser for themes
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiAdministrationBrowserTheme extends PapayaUiAdministrationBrowser {

  /**
  * Theme's path.
  * @var string
  */
  private $_themesPath;

  /**
  * Theme configuration object.
  * @var PapayaUiAdministrationBrowserThemeConfiguration
  */
  private $_configurationObject;

  /**
  * Theme handler object.
  * @var PapayaThemeHandler
  */
  private $_themeHandlerObject;

  /**
  * Loaded available themes.
  * @var array
  */
  private $_themes = array();

  /*******************
  * Output
  ************************************************************/

  /**
   * Main output method for theme browser.
   *
   * @internal param string $mode list|tile|thumbs
   * @return string xml output
   */
  public function getXml() {
    $result = '';
    if (isset($this->params['mode_view']) &&
        in_array($this->params['mode_view'], $this->listModes)) {
      $currentMode = $this->params['mode_view'];
    } else {
      $currentMode = 'thumbs';
    }
    $themes = $this->getThemes();
    if (!empty($themes)) {
      $currentTheme = NULL;
      if (isset($this->params['theme']) && array_key_exists($this->params['theme'], $themes)) {
        $this->hiddenFields[$this->fieldName] = $this->params['theme'];
        $this->hiddenFields['PAPAYA_LAYOUT_TEMPLATES'] =
          $themes[$this->params['theme']]['templates'];
        $currentTheme = $this->params['theme'];
      } elseif (isset($this->data['opt_value']) &&
          array_key_exists($this->data['opt_value'], $themes)) {
        $this->hiddenFields[$this->fieldName] = $this->data['opt_value'];
        $this->hiddenFields['PAPAYA_LAYOUT_TEMPLATES'] =
          $themes[$this->data['opt_value']]['templates'];
        $currentTheme = $this->data['opt_value'];
      } else {
        $this->hiddenFields[$this->fieldName] = '';
      }
      if (isset($currentTheme) && !empty($currentTheme)) {
        $result .= $this->getXmlThemeDetails($currentTheme);
      }
      $result .= $this->getXmlDialog($themes, $currentMode);
    } else {
      $this->owner->addMsg(MSG_WARNING, $this->owner->_gt('No themes found.'));
    }
    return $result;
  }

  /**
  * Retrieves the details output as sheet.
  *
  * @param <type> $currentTheme
  * @return string output xml
  */
  public function getXmlThemeDetails($currentTheme) {
    $result = '';
    if (array_key_exists($currentTheme, $this->_themes)) {
      $result .= '<sheet>'.LF;
      $result .= '<header>'.LF;
      $result .= '<lines>'.LF;
      $result .= sprintf(
        '<line class="headertitle">%s</line>'.LF,
        PapayaUtilStringXml::escape($this->_themes[$currentTheme]['name'])
      );
      $result .= '</lines>'.LF;
      $result .= '</header>'.LF;
      $result .= '<text>'.LF;
      $result .= '<div style="padding: 10px;">'.LF;
      $details = array(
        'version' => 'Version',
        'date' => 'Date',
        'author' => 'Author',
        'templates' => 'Template folder',
        'description' => 'Description'
      );
      foreach ($details as $detailKey => $detailCaption) {
        if (!empty($this->_themes[$currentTheme][$detailKey])) {
          $result .= sprintf(
            '<p><strong>%s</strong>: %s</p>'.LF,
            $this->owner->_gt($detailCaption),
            PapayaUtilStringXml::escape($this->_themes[$currentTheme][$detailKey])
          );
        }
      }
      $result .= '</div>'.LF;
      $result .= '</text>'.LF;
      $result .= '</sheet>'.LF;
    }
    return $result;
  }

  /**
  * Returns the dialog output including list view output xml.
  *
  * @param array $themes
  * @param string $currentMode
  * @return string xml output
  */
  public function getXmlDialog($themes, $currentMode) {
    $result = '';
    if (!empty($themes)) {
      $action = new PapayaUiReference();
      $action->papaya($this->papaya());
      $result .= sprintf(
        '<dialog title="%s (%s)" action="%s" id="themeBrowser">'.LF,
        PapayaUtilStringXml::escapeAttribute($this->owner->_gt('Themes')),
        PapayaUtilStringXml::escapeAttribute($this->fieldName),
        PapayaUtilStringXml::escapeAttribute(
          $action->getRelative($this->papaya()->request->getUrl())
        )
      );
      foreach ($this->hiddenFields as $hiddenFieldName => $hiddenFieldValue) {
        $result .= sprintf(
          '<input type="hidden" name="%s[%s]" value="%s" />'.LF,
          $this->paramName,
          $hiddenFieldName,
          $hiddenFieldValue
        );
      }
      $result .= $this->getXmlListView($themes, $currentMode);
      $result .= sprintf('<dlgbutton value="%s"/>'.LF, $this->owner->_gt('Save'));
      $result .= '</dialog>'.LF;
    }
    return $result;
  }

  /**
  * Returns the list view output xml.
  *
  * @param array $themes
  * @param string $currentMode
  * @return string xml output
  */
  public function getXmlListView($themes, $currentMode) {
    $result = '';
    $result .= sprintf('<listview mode="%s">'.LF, $currentMode);
    $result .= $this->getXmlListButtons($currentMode);
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s / %s</col>'.LF,
      $this->owner->_gt('Title'),
      $this->owner->_gt('Directory')
    );
    if ($currentMode == 'list') {
      $result .= sprintf('<col>%s</col>'.LF, $this->owner->_gt('Date'));
      $result .= sprintf('<col>%s</col>'.LF, $this->owner->_gt('Version'));
      $result .= sprintf('<col>%s</col>'.LF, $this->owner->_gt('Author'));
      $result .= sprintf('<col>%s</col>'.LF, $this->owner->_gt('Description'));
    }
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($themes as $directoryName => $themeConfiguration) {
      $result .= $this->getXmlListViewElement($directoryName, $themeConfiguration, $currentMode);
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
   * Returns the xml output for a theme as list view element.
   *
   * @param string $themeName
   * @param array $themeConfiguration
   * @param string $currentMode
   * @return string
   */
  public function getXmlListViewElement($themeName, $themeConfiguration, $currentMode) {
    $result = '';
    $thumbImage = '';
    $selected = '';
    if (isset($this->params['theme'])) {
      $selected = ($this->params['theme'] == $themeName) ? 'selected="selected" ' : '';
    } elseif (isset($this->data['opt_value'])) {
      $selected = ($this->data['opt_value'] == $themeName) ? 'selected="selected" ' : '';
    }
    switch ($currentMode) {
    case 'thumbs':
      $thumbField = 'thumbLarge';
      break;
    default:
      $thumbField = 'thumbMedium';
      break;
    }
    if (!empty($themeConfiguration[$thumbField])) {
      if ($currentMode != 'list') {
        $thumbImage = sprintf(
          'image="%s" ',
          PapayaUtilStringXml::escapeAttribute(
            $this->getThemeHandlerObject()->getUrl($themeName).$themeConfiguration[$thumbField]
          )
        );
      } else {
        $thumbImage = sprintf('image="%s" ', $this->papaya()->images['categories-content']);
      }
    }
    $result .= sprintf(
      '<listitem href="%1$s" title="%2$s" subtitle="%3$s" hint="%2$s" %4$s%5$s>'.LF,
      PapayaUtilStringXml::escapeAttribute(
        $this->getLink(array('theme' => $themeName, 'mode_view' => $currentMode))
      ),
      PapayaUtilStringXml::escapeAttribute($themeConfiguration['name']),
      PapayaUtilStringXml::escapeAttribute($themeName),
      $thumbImage,
      $selected
    );
    if ($currentMode == 'list') {
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        PapayaUtilStringXml::escape($themeConfiguration['date'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        PapayaUtilStringXml::escape($themeConfiguration['version'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        PapayaUtilStringXml::escape($themeConfiguration['author'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        PapayaUtilStringXml::escape($themeConfiguration['description'])
      );
    }
    $result .= '</listitem>'.LF;
    return $result;
  }

  /**
  * Retrieves the output for listview buttons.
  *
  * @param string $currentMode
  * @return string xml output
  */
  public function getXmlListButtons($currentMode) {
    $down = 'down="down"';
    $result = '<buttons>'.LF;
    $result .= '<right>'.LF;
    $result .= sprintf(
      '<button hint="%s" glyph="%s" href="%s" %s/>'.LF,
      PapayaUtilStringXml::escapeAttribute($this->owner->_gt("List")),
      PapayaUtilStringXml::escapeAttribute(
        $this->papaya()->images['categories-view-list']
      ),
      PapayaUtilStringXml::escapeAttribute($this->getLink(array('mode_view' => 'list'))),
      ($currentMode == 'list') ? $down : ''
    );
    $result .= sprintf(
      '<button hint="%s" glyph="%s" href="%s" %s/>'.LF,
      PapayaUtilStringXml::escapeAttribute($this->owner->_gt("Tiles")),
      PapayaUtilStringXml::escapeAttribute(
        $this->papaya()->images['categories-view-tiles']
      ),
      PapayaUtilStringXml::escapeAttribute($this->getLink(array('mode_view' => 'tile'))),
      ($currentMode == 'tile') ? $down : ''
    );
    $result .= sprintf(
      '<button hint="%s" glyph="%s" href="%s" %s/>'.LF,
      PapayaUtilStringXml::escapeAttribute($this->owner->_gt("Thumbnails")),
      PapayaUtilStringXml::escapeAttribute(
        $this->papaya()->images['categories-view-icons']
      ),
      PapayaUtilStringXml::escapeAttribute($this->getLink(array('mode_view' => 'thumbs'))),
      ($currentMode == 'thumbs') ? $down : ''
    );
    $result .= '</right>'.LF;
    $result .= '</buttons>'.LF;
    return $result;
  }

  /*******************
  * Helper
  ************************************************************/

  /**
  * Sets private property _themesPath.
  * @param string $themesPath
  */
  public function setThemesPath($themesPath) {
    $this->_themesPath = $themesPath;
  }

  /**
  * Retrieves the papaya absolute local themes path
  * @return string
  */
  public function getThemesPath() {
    if (isset($this->_themesPath) && !empty($this->_themesPath)) {
      $result = $this->_themesPath;
    } else {
      $result = $this->getThemeHandlerObject()->getLocalPath();
    }
    return $result;
  }

  /**
  * Get available themes.
  *
  * @return array
  */
  public function getThemes() {
    $result = array();
    $themesPath = PapayaUtilFilePath::cleanup($this->getThemesPath());
    $configuration = $this->getThemeConfigurationObject();
    if ($directory = opendir($themesPath)) {
      while ($directoryName = readdir($directory)) {
        if (is_dir($themesPath.$directoryName) && substr($directoryName, 0, 1) != '.') {
          $currentConfiguration = $configuration->getThemeConfiguration(
            $themesPath . $directoryName
          );
          if (!empty($currentConfiguration)) {
            $result[$directoryName] = $currentConfiguration;
          }
        }
      }
      closedir($directory);
    }
    if (count($result) > 0) {
      ksort($result);
      $this->_themes = $result;
    }
    return $result;
  }

  /**
  * Sets themes array.
  * @param array $themes
  */
  public function setThemes($themes) {
    $this->_themes = $themes;
  }

  /**
  * Sets a configuration object.
  * @param PapayaUiAdministrationBrowserThemeConfiguration $configurationObject
  */
  public function setThemeConfigurationObject($configurationObject) {
    $this->_configurationObject = $configurationObject;
  }

  /**
  * Retrieves a configuration object.
  * @return PapayaUiAdministrationBrowserThemeConfiguration
  */
  public function getThemeConfigurationObject() {
    if (!(isset($this->_configurationObject) && is_object($this->_configurationObject))) {
      $this->_configurationObject = new PapayaUiAdministrationBrowserThemeConfiguration;
    }
    return $this->_configurationObject;
  }

  /**
  * Retrieves a configuration object.
  * @return PapayaThemeHandler
  */
  public function getThemeHandlerObject() {
    if (!(isset($this->_themeHandlerObject) && is_object($this->_themeHandlerObject))) {
      $this->_themeHandlerObject = new PapayaThemeHandler;
    }
    return $this->_themeHandlerObject;
  }

  /**
  * Sets a theme handler object.
  * @param PapayaThemeHandler $themeHandlerObject
  */
  public function setThemeHandlerObject($themeHandlerObject) {
    $this->_themeHandlerObject = $themeHandlerObject;
  }
}