<?php
/**
* XSLT output filter
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage _Base
* @version $Id: filter_xslt.php 39600 2014-03-18 11:43:38Z weinert $
*/

/**
* XSLT output filter
*
* @package Papaya-Modules
* @subpackage _Base
*/
class filter_xslt extends base_outputfilter {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'xslfile' => array ('XSL stylesheet', 'isFile', TRUE, 'filecombo',
      array('callback:getTemplatePath', '/^\w+\.xsl$/i'), ''),
    'fullpage' => array('Full page', 'isNum', TRUE, 'yesno', '', '', 0),
    'link_outputmode' => array(
      'Link output mode', 'isAlphaNum', TRUE, 'function', 'callbackOutputModes'
    ),
    'Permissions',
    'output_permission' => array(
      'Access', 'isNum', TRUE, 'function', 'getPermsCombo', NULL, -1
    )
  );

  /**
  * default template subdirectory
  * @var string
  */
  var $templatePath = 'xslt';

  /**
  * Native Parse
  *
  * @param PapayaTemplateXslt $layout
  * @access public
  * @return string
  */
  function parseXML($layout) {
    $layout->setXsl($this->getTemplatePath().$this->data['xslfile']);
    return $layout->parse();
  }

  /**
  * Parse page
  *
  * @see PapayaTemplateXslt:getOutput()
  * @param base_topic $topic
  * @param PapayaTemplateXslt $layout
  * @access public
  * @return string xhtml
  */
  function parsePage($topic, $layout) {
    $layout->setXSL($this->getTemplatePath().$this->data['xslfile']);
    return $layout->getOutput();
  }

  /**
  * Parse box
  *
  * @param object base_topic $topic
  * @param array $box box database record
  * @param string $xmlString box output xml
  * @access public
  * @return mixed boolean FALSE or string parsed xsl
  */
  function parseBox($topic, $box, $xmlString) {
    $options = $this->papaya()->options;
    $xsl = new PapayaTemplateXslt($this->getTemplatePath().$this->data['xslfile']);
    $protocol = PapayaUtilServerProtocol::get();
    $url = strtr(
      $protocol.'://'.PapayaUtilServerName::get().$options->get('PAPAYA_PATH_WEB'),
      '\\',
      '/'
    );
    if (!empty($this->data['viewmode_ext'])) {
      $currentViewMode = $this->data['viewmode_ext'];
      $defaultViewMode = $options->get('PAPAYA_URL_EXTENSION', 'html');
    } elseif ($options->get('PAPAYA_URL_EXTENSION') != '') {
      $currentViewMode = $options->get('PAPAYA_URL_EXTENSION');
      $defaultViewMode = $options->get('PAPAYA_URL_EXTENSION');
    } else {
      $currentViewMode = 'html';
      $defaultViewMode = 'html';
    }

    $parameters = $xsl->parameters();
    $parameters->set('PAGE_BASE_URL', $url);
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '?') > 0) {
      $url .= substr($_SERVER['REQUEST_URI'], 1, strpos($_SERVER['REQUEST_URI'], '?') - 1);
    } elseif (!empty($_SERVER['REQUEST_URI'])) {
      $url .= substr($_SERVER['REQUEST_URI'], 1);
    }
    $parameters->set('PAGE_URL', $url);
    $parameters->set('PAGE_TODAY', PapayaUtilDate::timestampToString(time()));
    if (defined('PAPAYA_WEBSITE_REVISION') && trim(PAPAYA_WEBSITE_REVISION) != '') {
      $parameters->set('PAPAYA_WEBSITE_REVISION', PAPAYA_WEBSITE_REVISION);
      $parameters->set('PAGE_WEBSITE_REVISION', PAPAYA_WEBSITE_REVISION);
    }
    $themeHandler = new PapayaThemeHandler();
    $parameters->set('PAGE_THEME', $themeHandler->getTheme());
    $parameters->set('PAGE_THEME_SET', $themeHandler->getThemeSet());
    $parameters->set('PAGE_THEME_PATH', $themeHandler->getUrl());
    $parameters->set('PAGE_THEME_PATH_LOCAL', $themeHandler->getLocalThemePath());
    $parameters->set('PAGE_WEB_PATH', $options->get('PAPAYA_PATH_WEB', '/'));
    if (isset($topic) && isset($topic->currentLanguage['lng_short'])) {
      $parameters->set('PAGE_LANGUAGE', $topic->currentLanguage['lng_short']);
    }
    /** @var papaya_page $page */
    $page = $GLOBALS['PAPAYA_PAGE'];
    $parameters->set('PAGE_MODE_PUBLIC', $page->isPreview() ? 0 : 1);
    $parameters->set('PAGE_OUTPUTMODE_CURRENT', $currentViewMode);
    $parameters->set('PAGE_OUTPUTMODE_DEFAULT', $defaultViewMode);
    $parameters->set('PAGE_URL_LEVEL_SEPARATOR', $options->get('PAPAYA_URL_LEVEL_SEPARATOR', ''));
    $parameters->set('PAPAYA_DBG_DEVMODE', $options->get('PAPAYA_DBG_DEVMODE', FALSE));
    $parameters->set(
      'PAPAYA_DEBUG_LANGUAGE_PHRASES', $options->get('PAPAYA_DEBUG_LANGUAGE_PHRASES', FALSE)
    );

    if (FALSE === strpos($xmlString, '<?xml')) {
      if (defined('PAPAYA_LATIN1_COMPATIBILITY') && PAPAYA_LATIN1_COMPATIBILITY) {
        $xmlString = papaya_strings::ensureUTF8($xmlString);
      }
      $xsl->setXml(
        '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.papaya_strings::entityToXML($xmlString)
      );
    } else {
      $xsl->setXml(papaya_strings::entityToXML($xmlString));
    }
    if (!($result = $xsl->parse(PapayaTemplateXslt::STRIP_ALL))) {
      return FALSE;
    } else {
      return $result;
    }
  }

  /**
  * Check configuration
  *
  * @param boolean $page optional, default value TRUE
  * @param string | NULL $moduleName if set check module support
  *   (only from administration interface)
  * @access public
  * @return boolean
  */
  function checkConfiguration($page = TRUE, $moduleName = NULL) {
    if (!$this-> validatePermission()) {
      $this->errorStatus = 403;
      $this->errorMessage = $this->_gt('Output mode limited.');
      return FALSE;
    }
    if (isset($this->data['xslfile']) && trim($this->data['xslfile']) != '') {
      if (file_exists($this->getTemplatePath().$this->data['xslfile'])) {
        if (empty($moduleName)) {
          return TRUE;
        } else {
          $fileData = file_get_contents($this->getTemplatePath().$this->data['xslfile']);
          if (preg_match('(<!--\s*(@papaya.*?)-->)si', $fileData, $match)) {
            if (preg_match('((?:^|\s)@papaya:modules\s+(.*)(?:$))mi', $match[1], $match)) {
              $moduleNames = explode(',', $match[1]);
              foreach ($moduleNames as $key => $value) {
                $moduleNames[trim($value)] = TRUE;
                unset($moduleNames[$key]);
              }
              if (isset($moduleNames[$moduleName])) {
                return TRUE;
              }
            }
          }
          $this->errorMessage = sprintf(
            $this->_gt('XSLT file "%s" may not support module "%s".'),
            papaya_strings::escapeHTMLChars($this->data['xslfile']),
            papaya_strings::escapeHTMLChars($moduleName)
          );
          return FALSE;
        }
      } else {
        $this->errorStatus = 404;
        $this->errorMessage = 'XSLT file "'.$this->data['xslfile'].'" not found.';
        return FALSE;
      }
    } else {
      $this->errorMessage = 'No XSLT file specified.';
      return FALSE;
    }
  }

  /**
   * Get XHTML for special edit field showing all currunt output modes in a selectbox
   *
   * @param string $name
   * @param array $field
   * @param string $data
   * @return string
   */
  function callbackOutputModes($name, $field, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    if (empty($data) && isset($this->parentObj)) {
      $data = $this->parentObj->viewLink['viewmode_ext'];
    }
    $viewList = new base_viewlist();
    $viewList->loadViewModesList();
    if (isset($viewList->viewModes) && is_array($viewList->viewModes)) {
      foreach ($viewList->viewModes as $viewMode) {
        $selected = ($viewMode['viewmode_ext'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext']),
          $selected,
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  private function validatePermission() {
    if (isset($this->data['output_permission']) && $this->data['output_permission'] > 0) {
      if ($this->papaya()->request->isPreview) {
        return TRUE;
      }
      return $this->papaya()->surfer->hasPerm($this->data['output_permission']);
    }
    return TRUE;
  }
}