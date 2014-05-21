<?php
/**
* papaya_options variable
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
* @package Papaya
* @subpackage Core
* @version $Id: papaya_options.php 39818 2014-05-13 13:15:13Z weinert $
*/

/**
* papaya_options variable
*
* @package Papaya
* @subpackage Core
*/
class papaya_options extends base_options {
  /**
  * Papaya database table options
  * @var string $tableOptions
  */
  var $tableOptions = PAPAYA_DB_TBL_OPTIONS;

  /**
  * Input field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'large';

  /**
  * Advanced options
  * @var array $advancedOptions
  */
  var $advancedOptions = array(1, 6);

  /**
   * found option id (search form)
   *
   * @var string $foundOption
   */
  var $foundOption = NULL;

  /**
  * Options
  * @var array $options
  */
  var $options;

  /**
   * search dialog
   *
   * @var base_dialog $searchDialog
   */
  var $searchDialog = NULL;

  /**
   * @var PapayaTemplate
   */
  public $layout = NULL;

  /**
   * @var array
   */
  public $group = array();

  /**
   * @var base_dialog
   */
  private $optionDialog;

  /**
  * get XML Buttons
  *
  * @access public
  */
  function getXMLButtons() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $toolbar->addButton('Install/Upgrade', 'install.php', 'categories-installer', '', FALSE);
    $toolbar->addSeperator();
    $toolbar->addButton('Link types', 'linktypes.php', 'items-link', 'Configure Link types', FALSE);
    $toolbar->addButton(
      'Mime types', 'mediamime.php', 'items-mimetype-group', 'Configure Mime types', FALSE
    );
    $toolbar->addButton('Cronjobs', 'cronjobs.php', 'items-cronjob', '', FALSE);
    $toolbar->addButton('Spamfilter', 'spam.php', 'items-junk', '', FALSE);
    $toolbar->addSeperator();
    /*
     * @todo uncomment after theme sets are implemented
    $toolbar->addButton('Theme sets', 'theme.php', 'items-page', '', FALSE);
    */
    $toolbar->addButton('View icons', 'glyphview.php', 'categories-view-icons', '', FALSE);
    $toolbar->addSeperator();
    $toolbar->addButton(
      'Check paths',
      $this->getLink(array('cmd' => 'opt_paths')),
      'actions-tree-scan',
      'Check and create data paths',
      FALSE
    );
    if (isset($this->optLinks[0]) && is_array($this->optLinks[0])) {
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Remove unknown',
        $this->getLink(array('cmd' => 'opt_clear')),
        'actions-option-delete',
        'Remove unknown options',
        FALSE
      );
    }
    $toolbar->addButton(
      'Export',
      $this->getLink(array('cmd' => 'export')),
      'actions-download',
      'Export options',
      FALSE
    );
    $toolbar->addButton(
      'Import',
      $this->getLink(array('cmd' => 'import')),
      'actions-upload',
      'Import options',
      FALSE
    );
    if ($result = $toolbar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initialize() {
    $this->paramName = 'opt';
    $this->sessionParamName = 'PAPAYA_SESS_user_'.$this->paramName;
    $this->initializeParams($this->sessionParamName);
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->loadOptions();
    $this->loadOptGroups();
  }

  /**
  * Execute - basic function fot handling parameters
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open':
        $this->sessionParams['opened'][$this->params['gid']] = TRUE;
        break;
      case 'close':
        unset($this->sessionParams['opened'][$this->params['gid']]);
        break;
      case 'edit':
        $this->initOptionDialog();
        if ($this->optionDialog->checkDialogInput() &&
            isset($this->params['id']) &&
            $this->checkOptionSpecial($this->params['id'], $this->params[$this->params['id']])) {
          if ($this->save($this->params['id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Option modified.'));
            // if saving theme option was successful, save also templates option
            if ($this->params['id'] == 'PAPAYA_LAYOUT_THEME' &&
                isset($this->params['PAPAYA_LAYOUT_TEMPLATES']) &&
                !empty($this->params['PAPAYA_LAYOUT_TEMPLATES'])) {
              if ($this->save('PAPAYA_LAYOUT_TEMPLATES')) {
                $this->addMsg(MSG_INFO, $this->_gt('Templates option modified.'));
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Cannot change templates option.'));
              }
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Cannot change option.'));
          }
        }
        break;
      case 'opt_clear':
        $this->removeUnknown();
        break;
      case 'opt_paths':
        $this->checkDirectoriesWriteable();
        break;

      case 'search':
        $this->initializeSearchDialog();
        if ($this->searchDialog->checkDialogInput()) {
          $optName = strtoupper($this->searchDialog->data['opt_name']);
          $found = FALSE;
          $this->foundOption = '';
          $foundOffset =
            ((!isset($this->params['search_new'])) && isset($this->params['search_offset']))
              ? (int)$this->params['search_offset'] : 0;
          // search option in groups
          $cntFoundItems = 0;
          $firstOptId = NULL;
          foreach ($this->optionGroups as $groupId => $optionGroup) {
            if (isset($this->optLinks[$groupId])) {
              foreach ($this->optLinks[$groupId] as $optId) {
                if (isset($this->options[$optId])) {
                  if (FALSE !== strpos($this->options[$optId]['opt_name'], $optName)) {
                    if ($cntFoundItems++ != $foundOffset) {
                      if ($cntFoundItems == 1) {
                        $firstOptId = $optId;
                      }
                      continue;
                    }

                    $found = TRUE;
                    if ($this->sessionParams['opened'] &&
                      is_array($this->sessionParams['opened'])) {
                      foreach ($this->sessionParams['opened'] as $gid => $value) {
                        $this->sessionParams['opened'][$gid] = FALSE;
                      }
                    }
                    $this->sessionParams['opened'][$groupId] = TRUE;
                    $this->foundOption = $this->options[$optId]['opt_name'];
                    $this->params['id'] = $this->foundOption;
                    break 2;
                  }
                }
              }
            }
          }
          if (isset($firstOptId) && empty($this->foundOption)) {
            $this->foundOption = $this->options[$firstOptId]['opt_name'];
            $this->params['id'] = $this->foundOption;
            $this->params['search_offset'] = 0;
            $found = TRUE;
            unset($this->searchDialog);
            $this->initializeSearchDialog();
          }
          if ($found) {
            $this->searchDialog->buttonTitle = 'Search next';
            $this->searchDialog->addButton('search_new', $this->_gt('Search new'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Option not found.'));
          }
        }
        break;
      case 'export':
        $this->export();
        break;
      case 'do_import':
        $this->import();
        break;
      }
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
  }

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    $this->layout->parameters()->set('COLUMNWIDTH_RIGHT', '50%');
    $this->layout->parameters()->set('COLUMNWIDTH_CENTER', '50%');
    $this->getXMLButtons();
    $this->getSearchForm();
    $this->getList();
    if (isset($this->params['id']) && $this->params['id'] == 'PAPAYA_LAYOUT_THEME') {
      $this->layout->addRight($this->getLayoutDialogXML());
      $this->layout->addRight($this->getOptionHelp($this->params['id']));
    } else {
      $this->getForm();
    }
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'import' :
        $this->getImportForm();
        break;
      }
    }
  }


  /**
  * Generate the theme browser dialog output.
  *
  * @return string output xml
  */
  public function getLayoutDialogXML() {
    $result = '';
    try {
      // initialize dialog for retrieving hidden fields and token
      $this->initOptionDialog();
      // collect hidden fields for browser dialog
      $hiddenFields = array_merge(
        $this->optionDialog->hidden,
        array('token' => $this->optionDialog->getDialogToken())
      );
      // choose from where data gets its values
      if (isset($this->params['save'])) {
        // select requested data after saving
        $data = array(
          'opt_name' => $this->params['id'],
          'opt_value' => $this->params[$this->params['id']]
        );
      } else {
        // select loaded data from db
        $data = $this->optionDialog->data;
      }
      // use theme browser object to generate output xml
      $themeBrowser = new PapayaUiAdministrationBrowserTheme(
        $this,
        $this->params,
        $this->paramName,
        $data,
        $this->params['id'],
        $hiddenFields
      );
      $result = $themeBrowser->getXml();
    } catch (InvalidArgumentException $e) {
      $this->addMsg(MSG_ERROR, $this->_gt($e->getMessage()));
    }
    return $result;
  }

  /**
  * Load options
  *
  * @access public
  */
  function loadOptions() {
    unset($this->options);
    unset($this->optLinks);
    $sql = "SELECT opt_name, opt_value
              FROM %s
             ORDER BY opt_name";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableOptions))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->options[$row['opt_name']] = $row;
        if (isset(self::$optFields[$row['opt_name']])) {
          $row['opt_group'] = (int)self::$optFields[$row['opt_name']][0];
        } else {
          $row['opt_group'] = 0;
        }
        $this->optLinks[$row['opt_group']][] = $row['opt_name'];
      }
      return $this->checkOptions();
    }
    return FALSE;
  }

  /**
  * Remove Unknown
  *
  * @access public
  */
  function removeUnknown() {
    if (isset($this->optLinks[0]) && is_array($this->optLinks[0])) {
      foreach ($this->optLinks[0] as $key => $optName) {
        if (
          FALSE !== $this->databaseDeleteRecord(
            $this->tableOptions, 'opt_name', $optName
          )
        ) {
          unset($this->optLinks[0][$key]);
        } else {
          break;
        }
      }
      if (count($this->optLinks[0]) < 1) {
        unset($this->optLinks[0]);
      }
    }
  }

  /**
  * Check options
  *
  * @access public
  */
  function checkOptions() {
    foreach (self::$optFields as $key => $val) {
      if (!isset($this->options[$key])) {
        switch ($key) {
        case 'PAPAYA_DEFAULT_HOST':
          $optValue = strtolower($_SERVER['HTTP_HOST']);
          break;
        case 'PAPAYA_PATH_DATA':
          //confixx
          $optValue = dirname($_SERVER['DOCUMENT_ROOT']).'/files/papaya-data/';
          if (!file_exists($optValue.'.')) {
            //next to document root
            $optValue = dirname($_SERVER['DOCUMENT_ROOT']).'/papaya-data/';
            if (!file_exists($optValue.'.')) {
              //document root subdirectory
              $optValue = $_SERVER['DOCUMENT_ROOT'].'/papaya-data/';
            }
          }
          break;
        case 'PAPAYA_PATH_WEB':
          $optValue = dirname(dirname($this->getBaseLink())).'/';
          if (substr($optValue, 0, 1) == '.') {
            $optValue = substr($optValue, 1);
          }
          $optValue = strtr($optValue, array('//' => '/', '\\/' => '/', '\\\\/' => '/'));
          break;
        case 'PAPAYA_XSLT_EXTENSION' :
          $optValue = '';
          if (extension_loaded('xsl')) {
            $optValue = 'xsl';
          } elseif (extension_loaded('xslt')) {
            $optValue = 'xslt';
          } elseif (extension_loaded('domxml') && function_exists('domxml_xslt_stylesheet_file')) {
            $optValue = 'domxml';
          }
          break;
        default :
          $optValue = $val[4];
          break;
        }
        $data = array(
          'opt_name' => $key,
          'opt_value' => $optValue
        );
        if ($key == 'PAPAYA_PATH_DATA' ||
            FALSE !== $this->databaseInsertRecord($this->tableOptions, NULL, $data)) {
          $this->options[$key] = array('opt_name' => $key, 'opt_value' => $optValue);
          $this->optLinks[$val[0]][] = $key;
        } else {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
  * Load option groups
  *
  * @access public
  * @return boolean TRUE
  */
  function loadOptGroups() {
    foreach (base_statictables::getTableOptGroups() as $key => $val) {
      $this->optionGroups[$key] = $this->_gt($val);
    }
    return TRUE;
  }

  /**
  * activate theme
  *
  * deprecated function - removed on tinymce commit
  *
  * @param string $baseDirectory
  * @param string $theme
  * @access public
  */
  function activateTheme($baseDirectory, $theme) {
    $themeFileName = $baseDirectory.'/themes/'.$theme.'/theme.php';
    if (file_exists($themeFileName)) {
      include_once($themeFileName);
    }
    if (!defined('PAPAYA_BGCOLOR')) {
      define('PAPAYA_BGCOLOR', 'threedface');
    }
    if (!defined('PAPAYA_BGCOLOR_LIGHT')) {
      define('PAPAYA_BGCOLOR_LIGHT', 'threedlightshadow');
    }
    if (!defined('PAPAYA_BGCOLOR_DARKER')) {
      define('PAPAYA_BGCOLOR_DARKER', 'threedshadow');
    }
    if (!defined('PAPAYA_BGCOLOR_DARK')) {
      define('PAPAYA_BGCOLOR_DARK', 'appworkspace');
    }
    if (!defined('PAPAYA_FGCOLOR')) {
      define('PAPAYA_FGCOLOR', 'windowtext');
    }
    if (!defined('PAPAYA_WINDOWCOLOR')) {
      define('PAPAYA_WINDOWCOLOR', 'window');
    }
    if (!defined('PAPAYA_HIGHLIGHT_COLOR')) {
      define('PAPAYA_HIGHLIGHT_COLOR', 'highlight');
    }
    if (!defined('PAPAYA_HIGHLIGHT_TEXTCOLOR')) {
      define('PAPAYA_HIGHLIGHT_TEXTCOLOR', 'highlighttext');
    }
    if (!defined('PAPAYA_SYSTEMPICS_PATH')) {
      define('PAPAYA_SYSTEMPICS_PATH', './themes/system/');
    }
  }

  /**
   * Save option to database table
   *
   * @param string $id
   * @return boolean
   */
  function save($id) {
    $result = FALSE;
    $option = $this->options[$id];
    if (isset($this->params[$id])) {
      $value = (string)$this->params[$id];
    } elseif (isset($option) && isset($option['opt_value'])) {
      $value = (string)$option['opt_value'];
    } elseif (isset(self::$optFields[$id][4])) {
      $value = (string)self::$optFields[$id][4];
    } else {
      $value = '';
    }
    if (isset($option) && is_array($option)) {
      $sql = "SELECT COUNT(*) FROM %s WHERE opt_name = '%s'";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableOptions, $option['opt_name']))) {
        if ($res->fetchField() > 0) {
          $data = array('opt_value' => $value);
          $filter = array('opt_name' => $option['opt_name']);
          if (FALSE !== $this->databaseUpdateRecord($this->tableOptions, $data, $filter)) {
            $this->options[$id]['opt_value'] = $value;
            $result = TRUE;
          }
        } else {
          $data = array('opt_value' => $value, 'opt_name' => $option['opt_name']);
          if (FALSE !== $this->databaseInsertRecord($this->tableOptions, NULL, $data)) {
            $this->options[$id]['opt_value'] = $value;
            $result = TRUE;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get list of options
  *
  * @param boolean $all optional, default value FALSE
  * @access public
  */
  function getList($all = FALSE) {
    if (isset($this->optionGroups) && is_array($this->optionGroups)) {
      $images = $this->papaya()->images;
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Options'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Option'))
      );
      $result .= sprintf(
        '<col>%s (%s)</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Active')),
        papaya_strings::escapeHTMLChars($this->_gt('Database'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->optionGroups as $groupId => $optionGroup) {
        if ($all ||
            (!in_array($groupId, $this->advancedOptions) && isset($this->optLinks[$groupId]))) {
          if (isset($this->optLinks[$groupId]) && is_array($this->optLinks[$groupId]) &&
              isset($this->sessionParams['opened'][$groupId]) &&
              $this->sessionParams['opened'][$groupId]) {
            $nodeHref = $this->getLink(
              array(
                'cmd' => 'close',
                'gid' => $groupId
              )
            );
            $node = sprintf(
              ' node="open" nhref="%s"',
              papaya_strings::escapeHTMLChars($nodeHref)
            );
            $imageIdx = 'items-folder';
          } elseif (isset($this->optLinks[$groupId]) &&
                    is_array($this->optLinks[$groupId])) {
            $nodeHref = $this->getLink(
              array(
                'cmd' => 'open',
                'gid' => $groupId
              )
            );
            $node = sprintf(
              ' node="close" nhref="%s"',
              papaya_strings::escapeHTMLChars($nodeHref)
            );
            $imageIdx = 'status-folder-open';
          } else {
            $node = ' indent="1"';
            $imageIdx = 'items-folder';
          }
          if (isset($this->group['group_id']) && $groupId == $this->group['group_id']) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem title="%s" image="%s"%s>'.LF,
            papaya_strings::escapeHTMLChars($optionGroup),
            papaya_strings::escapeHTMLChars($images[$imageIdx]),
            $node.$selected
          );
          $result .= '<subitem/>';
          $result .= '</listitem>';
          if (isset($this->optLinks[$groupId]) && is_array($this->optLinks[$groupId]) &&
              isset($this->sessionParams['opened'][$groupId]) &&
              ($this->sessionParams['opened'][$groupId])) {
            foreach ($this->optLinks[$groupId] as $optId) {
              if (isset($this->options[$optId]) && is_array($this->options[$optId])) {
                $option = $this->options[$optId];
                if (isset($this->params['id']) && $this->params['id'] == $optId) {
                  $selected = ' selected="selected"';
                } else {
                  $selected = '';
                }
                $result .= sprintf(
                  '<listitem title="%s" indent="2" href="%s" image="%s"%s>'.LF,
                  papaya_strings::escapeHTMLChars($option['opt_name']),
                  papaya_strings::escapeHTMLChars($this->getLink(array('id' => $optId))),
                  papaya_strings::escapeHTMLChars($images['items-option']),
                  $selected
                );
                $value = $this->papaya()->options->get($optId);
                if (isset(self::$optFields[$optId]) && self::$optFields[$optId][2] == 'combo') {
                  $activeOption = empty(self::$optFields[$optId][3][$value])
                    ? '' : self::$optFields[$optId][3][$value];
                  $dbOption = empty(self::$optFields[$optId][3][$option['opt_value']])
                    ? '' : self::$optFields[$optId][3][$option['opt_value']];
                } else {
                  $activeOption = $value;
                  $dbOption = $option['opt_value'];
                }
                if (papaya_strings::strlen($activeOption) > 30) {
                  $str = papaya_strings::substr($activeOption, 0, 30).'...';
                } elseif ($activeOption != $dbOption) {
                  if (papaya_strings::strlen($activeOption) <= 15) {
                    $str = $activeOption . ' ('.$dbOption.')';
                  } else {
                    $str = $activeOption . ' (...)';
                  }
                } else {
                  $str = $activeOption;
                }
                $result .= '<subitem>'.papaya_strings::escapeHTMLChars($str).'</subitem>';
                $result .= '</listitem>';
              }
            }
          }
        }
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * Initialize option dialog
  *
  * @access public
  */
  function initOptionDialog() {
    if (!(isset($this->optionDialog) && is_object($this->optionDialog))) {
      $option = $this->options[$this->params['id']];
      $hidden = array(
        'save' => 1,
        'cmd' => 'edit',
        'id' => empty($option['opt_name']) ? '' : $option['opt_name']
      );
      $data = array(
        'opt_name' => $option['opt_name'],
        'opt_value' => $option['opt_value'],
        'opt_active_value' => defined($option['opt_name']) ? constant($option['opt_name']) : ''
      );
      if (isset(self::$optFields[$option['opt_name']])
          && self::$optFields[$option['opt_name']][2] == 'combo'
          && isset(self::$optFields[$option['opt_name']][3][$option['opt_value']])) {
        $data['opt_active_value'] = self::$optFields[$option['opt_name']][3][$option['opt_value']];
      } else {
        $data['opt_active_value'] = $option['opt_value'];
      }
      $fields = array(
        'opt_name' => array('Name', '', FALSE, 'info', 0, '',
          $option['opt_name'], 'left'),
        'opt_active_value' => array('Active value', '', FALSE, 'disabled_input', 400)
      );
      if (is_array($optionField = self::$optFields[$option['opt_name']])) {
        if (isset($optionField[5])) {
          $needed = !(bool)$optionField[5];
        } else {
          $needed = TRUE;
        }
        $fields[$option['opt_name']] =
          array('Database value', $optionField[1], $needed, $optionField[2],
            $optionField[3], '', $option['opt_value']);
      } else {
        $fields[$option['opt_name']] =
          array('Database value', '', TRUE, 'info', '', '', $option['opt_value']);
      }
      $this->optionDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->optionDialog->dialogTitle = $this->_gt('Option');
      $this->optionDialog->baseLink = $this->baseLink;
      $this->optionDialog->inputFieldSize = 'x-small';
      $this->optionDialog->loadParams();
    }
  }

  /**
   * Get search dialog
   *
   * @access public
   * @return base_dialog
   */
  function initializeSearchDialog() {
    if (!(isset($this->searchDialog) && is_object($this->searchDialog))) {
      $hidden = array(
        'cmd' => 'search',
        'search_offset' =>
          (isset($this->params['search_offset']) && !isset($this->params['search_new']))
            ? ($this->params['search_offset'] + 1) : 1
      );
      if (isset($this->params['cmd']) &&
          $this->params['cmd'] == 'search' &&
          isset($this->params['opt_name'])) {
        $data = array('opt_name' => $this->params['opt_name']);
      } else {
        $data = array();
      }

      $fields = array(
        'opt_name' => array('Option name', 'isAlphaNum', TRUE, 'input', 100)
      );
      $this->searchDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->searchDialog->dialogTitle = $this->_gt('Search option');
      $this->searchDialog->baseLink = $this->baseLink;
      $this->searchDialog->loadParams();
      $this->searchDialog->buttonTitle = 'Search';
    }
  }

  /**
   * get xml of search dialog
   */
  function getSearchForm() {
    $this->initializeSearchDialog();
    $this->layout->add($this->searchDialog->getDialogXML());
  }

  /**
  * Get form
  *
  * @access public
  */
  function getForm() {
    if (isset($this->params['id']) && isset($this->options[$this->params['id']])) {
      $this->initOptionDialog();
      $this->layout->addRight($this->optionDialog->getDialogXML());
      $this->layout->addRight($this->getOptionHelp($this->params['id']));
    }
  }

  /**
  * Reads the help text for a given option name.
  *
  * @param $optName string Option name
  * @param $lngIdent string Backend language identifier, e.g. 'de-DE' for German
  * @return string XML element that contains the option help text or the empty
  *   string when the help file could not be read.
  */
  function getOptionHelp($optName, $lngIdent = NULL) {
    if (isset($lngIdent)) {
      $lng = $lngIdent;
    } elseif (isset($this->papaya()->administrationUser)) {
      $lng = $this->papaya()->administrationUser->options['PAPAYA_UI_LANGUAGE'];
    } else {
      $lng = PAPAYA_UI_LANGUAGE;
    }
    $fileName = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB.PAPAYA_PATH_ADMIN.
      '/data/'.$lng.'/doc/conf/'.$optName.'.txt';
    $fileName = str_replace('//', '/', $fileName);
    if ($fileName && is_file($fileName) && ($fileSize = filesize($fileName)) > 0) {
      if ($fh = @fopen($fileName, 'r')) {
        $data = fread($fh, $fileSize);
        fclose($fh);
        $result = '<sheet><text><div style="padding: 10px;">';
        $result .= papaya_strings::ensureUTF8($data);
        $result .= '</div></text></sheet>';
        return $result;
      }
    }
    return '';
  }

  /**
  * Get XSLT extensions combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string
  */
  function getXSLTExtensionsCombo($name, $element, $data) {
    $extensions = array();
    if (extension_loaded('xslcache')) {
      $extensions[] = 'xslcache';
    }
    if (extension_loaded('xsl')) {
      $extensions[] = 'xsl';
    }
    if (extension_loaded('xslt')) {
      $extensions[] = 'xslt';
    }
    if (extension_loaded('domxml') && function_exists('domxml_xslt_stylesheet_file')) {
      $extensions[] = 'domxml';
    }
    $result = '';
    if (is_array($extensions)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      $result .= sprintf(
        '<option value="">%s (%s)</option>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Automatic')),
        papaya_strings::escapeHTMLChars(reset($extensions))
      );
      foreach ($extensions as $ext) {
        if ($ext == $data) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($ext),
          $selected,
          papaya_strings::escapeHTMLChars($ext)
        );
      }
      $result .= '</select>'.LF;
    } else {
      $result = '<input type="text" disabled="disabled" value="No XSLT Extension found"/>';
    }
    return $result;
  }

  /**
  * Get admin themes combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string XML
  */
  function getAdminThemesCombo($name, $element, $data) {
    $path = $this->getBaseLink(TRUE).'/theme/';
    $result = '';
    if ($dh = opendir($path)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      while ($directoryName = readdir($dh)) {
        if (is_dir($path.$directoryName) && substr($directoryName, 0, 1) == '.') {
          $selected = ($directoryName == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s"%s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($directoryName),
            $selected,
            papaya_strings::escapeHTMLChars($directoryName)
          );
        }
      }
      $result .= '</select>'.LF;
      closedir($dh);
    } else {
      $result = sprintf(
        '<input type="text" disabled="disabled" value="%s"/>',
        $this->_gt('No themes found')
      );
    }
    return $result;
  }

  /**
   * Load all sets for the current theme. Generate a select box to define one.
   *
   * @param string $name
   * @param array $element
   * @param string $data
   * @return string
   */
  function getThemeSetsCombo($name, $element, $data) {
    $themeSets = new PapayaContentThemeSets();
    $themeSets->load(
      array('theme_name' => $this->papaya()->options->get('PAPAYA_LAYOUT_THEME'))
    );
    $result = '';
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $result .= sprintf(
      '<option value="">%s</option>'.LF,
      new PapayaUiStringTranslated('None')
    );
    $current = $this->papaya()->options->get('PAPAYA_LAYOUT_THEME_SET', '');
    foreach ($themeSets as $themeSet) {
      $selected = ($current == $data) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d - %s"%s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($themeSet['id']),
        papaya_strings::escapeHTMLChars($themeSet['title']),
        $selected,
        papaya_strings::escapeHTMLChars($themeSet['title'])
      );
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get language combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string XML
  */
  function getInterfaceLanguageCombo($name, $element, $data) {
    $sql = "SELECT lng_short, lng_title
              FROM %s WHERE is_interface_lng = 1
             ORDER BY lng_title";
    $result = '';
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_LNG)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $selected = ($row['lng_short'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s (%s)</option>'.LF,
          papaya_strings::escapeHTMLChars($row['lng_short']),
          $selected,
          papaya_strings::escapeHTMLChars($row['lng_title']),
          papaya_strings::escapeHTMLChars($row['lng_short'])
        );
      }
      $result .= '</select>'.LF;
      $res->free();
    } else {
      $result = '<input type="text" disabled="disabled" value="No language found"/>';
    }
    return $result;
  }

  /**
  * Get language combo
  *
  * @param string $name
  * @param array $element
  * @param mixed $data
  * @access public
  * @return string XML
  */
  function getContentLanguageCombo($name, $element, $data) {
    $sql = "SELECT lng_id, lng_short, lng_title
              FROM %s
             WHERE is_content_lng = 1
             ORDER BY lng_title";
    $result = '';
    if ($res = $this->databaseQueryFmt($sql, PAPAYA_DB_TBL_LNG)) {
      $languages = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $languages[$row['lng_id']] = $row;
      }
      if (is_array($languages) && count($languages) > 0) {
        if (!isset($languages[$data])) {
          if (defined('PAPAYA_CONTENT_LANGUAGE')) {
            $data = PAPAYA_CONTENT_LANGUAGE;
          } else {
            $data = min(array_keys($languages));
          }
        }
        $result .= sprintf(
          '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name)
        );
        foreach ($languages as $lngId => $lng) {
          $selected = ($data > 0 && $lngId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d"%s>%s (%s)</option>'.LF,
            papaya_strings::escapeHTMLChars($lng['lng_id']),
            $selected,
            papaya_strings::escapeHTMLChars($lng['lng_title']),
            papaya_strings::escapeHTMLChars($lng['lng_short'])
          );
        }
        $result .= '</select>'.LF;
        $res->free();
      } else {
        $result = '<input type="text" disabled="disabled" value="No language found"/>';
      }
    }
    return $result;
  }

  /**
  * Check special option
  *
  * @param string $option
  * @param integer $value
  * @access public
  * @return boolean
  */
  function checkOptionSpecial($option, $value) {
    switch ($option) {
    case 'PAPAYA_UI_SECURE' :
    case 'PAPAYA_SESSION_SECURE' :
      if (0 != (int)$value) {
        if (!PapayaUtilServerProtocol::isSecure()) {
          $this->addMsg(MSG_ERROR, $this->_gt('You need HTTPS to use this feature.'));
          return FALSE;
        }
      }
      break;
    case 'PAPAYA_LOGIN_RESTRICTION' :
      $authSec = new base_auth_secure();
      $ipStatus = $authSec->getIpStatus($_SERVER['REMOTE_ADDR']);
      if ($value == 3 && $ipStatus != 1) {
        $this->addMsg(MSG_ERROR, $this->_gt('Your IP is not in whitelist.'));
        return FALSE;
      }
      break;
    case 'PAPAYA_PAGE_STATISTIC' :
      if ($value) {
        $statisticOverviewGuid = 'bb76cc5fed2a37e3257f2e666f82ce90';
        if ($this->papaya()->plugins->has($statisticOverviewGuid)) {
          return TRUE;
        }
        $this->papaya()->messages->dispatch(
          new PapayaMessageDisplay(
            PapayaMessage::SEVERITY_ERROR,
            sprintf(
              $this->_gt('Statistic module (%s) not found.'),
              $statisticOverviewGuid
            )
          )
        );
        return FALSE;
      } else {
        return TRUE;
      }
    }
    return TRUE;
  }

  /**
  * Export all options to a named XML file
  *
  * @access public
  * @return boolean
  */
  function export() {
    // Add timestamp to filename
    $time = time();
    $fileName = str_replace(
      array('"', '\\'),
      array('\\"', '\\\\'),
      'papaya_options_'.date('Y-m-d', $time).'.xml'
    );
    // Prepare XML structure
    $this->loadOptions();
    $xml = '<?xml version="1.0" encoding="UTF-8" ?>'.LF;
    $xml .= '<options>'.LF;
    $xml .= sprintf(
      '<timestamp value="%s"/>',
      date('Y-m-d H:i:s', $time)
    );
    foreach ($this->options as $option) {
      $xml .= sprintf(
        '<option name="%s">%s</option>'.LF,
        papaya_strings::escapeHTMLChars($option['opt_name']),
        papaya_strings::escapeHTMLChars($option['opt_value'])
      );
    }
    $xml .= '</options>'.LF;

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
      $agentString = '';
    } else {
      $agentString = strtolower($_SERVER["HTTP_USER_AGENT"]);
    }
    if (strpos($agentString, 'opera') !== FALSE) {
      $agent = 'OPERA';
    } elseif (strpos($agentString, 'msie') !== FALSE) {
      $agent = 'IE';
    } else {
      $agent = 'STD';
    }
    // set download mime type header
    $mimeType = ($agent == 'IE' || $agent == 'OPERA') ?
      'application/octetstream' : 'application/octet-stream';
    header('Content-type: '.$mimeType);
    // send a nice filename to the client
    if ($agent == 'IE') {
      header('Content-Disposition: inline; filename="'.$fileName.'"');
    } else {
      header('Content-Disposition: attachment; filename="'.$fileName.'"');
    }
    header('Expires: '.gmdate('D, d M Y H:i:s', time() - 86400).' GMT');
    echo $xml;
    exit;
  }

  /**
   * Import options from an XML file
   *
   * @access public
   */
  function import() {
    // Media db instance to determine max upload size
    $mediaDB = new base_mediadb_edit;
    // Assume that there is no error
    $error = '';
    $tempFileName = NULL;
    // Check whether there's an upload file
    if (isset($_FILES[$this->paramName]['tmp_name']['xml_file'])) {
      // There is a file, but we need to make sure that no upload error occured
      $fileData = $_FILES[$this->paramName];
      if (isset($fileData) && is_array($fileData) && isset($fileData['error']['xml_file'])) {
        switch ($fileData['error']) {  // check if error encountered
        case 1:                        // exceeded max file size
        case 2:                        // exceeded max post size
          $error = $this->_gt('File too large.');
          break;
        case 3:
          $error = $this->_gt('File not complete.');
          break;
        case 6:
          $error = $this->_gt('No temporary path.');
          break;
        case 4:
          $error = $this->_gt('No upload file.');
          break;
        case 0:
        default:
          $tempFileName = (string)$fileData['tmp_name']['xml_file'];
          break;
        }
      }
      // We've got a file, so check its size and type
      if ($error == '' && isset($tempFileName) && @file_exists($tempFileName)
          && is_uploaded_file($tempFileName)) {
        $tempFileSize = @filesize($tempFileName);
        if ($tempFileSize <= 0) {
          $error = $this->_gt('No upload file.');
        } elseif ($tempFileSize >= $mediaDB->getMaxUploadSize()) {
          $error = $this->_gt('File too large.');
        } elseif ($fileData['type']['xml_file'] != 'text/xml') {
          $error = $this->_gt('Wrong file type, XML expected.');
        }
      }
    } else {
      // No file at all
      $error = $this->_gt('No upload file.');
    }
    if ($error != '') {
      // If there's an error, display it and leave
      $this->addMsg(MSG_ERROR, $error);
      return;
    }
    // Try to create an XML tree and check whether it's valid
    $xml = PapayaXmlDocument::createFromXML(file_get_contents($tempFileName), TRUE);
    if (!($xml && isset($xml->documentElement))) {
      $this->addMsg(MSG_ERROR, $this->_gt('This is not a valid XML file.'));
      return;
    }
    // Formally, everything is okay, so start parsing the XML tree
    $doc = $xml->documentElement;
    if (!($doc->hasChildNodes())) {
      $this->addMsg(MSG_ERROR, $this->_gt('Empty XML tree.'));
      return;
    }
    if (!($doc instanceof DOMElement) || $doc->nodeName != 'options') {
      $this->addMsg(MSG_ERROR, $this->_gt('Illegal root element.'));
      return;
    }
    $options = array();
    for ($i = 0; $i < $doc->childNodes->length; $i++) {
      $node = $doc->childNodes->item($i);
      if (!($node instanceof DOMElement)) {
        continue;
      }
      if ($node->nodeName != 'option' && $node->nodeName != 'timestamp') {
        $this->addMsg(MSG_ERROR, sprintf($this->_gt('Illegal XML element %s.'), $node->nodeName));
        return;
      }
      if ($node->nodeName == 'timestamp') {
        if (!($node->hasAttribute('value'))) {
          $this->addMsg(MSG_WARNING, $this->_gt('Invalid timestamp.'));
        } else {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt('XML file has timestamp %s.'),
              $node->getAttribute('value')
            )
          );
        }
      } else {
        if (!($node->hasAttribute('name'))) {
          $this->addMsg(MSG_ERROR, $this->_gt('Option without a name found.'));
          return;
        }
        $name = $node->getAttribute('name');
        if (!($node->hasChildNodes())) {
          $options[$name] = '';
        } else {
          $value = $node->childNodes->item(0);
          if (!($value instanceof DOMText)) {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('Illegal option value found for option %s: %s.'),
                $name,
                $value->nodeType
              )
            );
            return;
          }
          $options[$name] = $value->nodeValue;
        }
      }
    }
    // Determine which options are in the current options table
    $sql = "SELECT opt_name
              FROM %s";
    $sqlParams = array($this->tableOptions);
    $liveOptions = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $liveOptions[] = $row['opt_name'];
      }
    }
    // Save the options to the live options table
    foreach ($options as $name => $value) {
      $optionData = array(
        'opt_value' => $value
      );
      if (in_array($name, $liveOptions)) {
        $this->databaseUpdateRecord($this->tableOptions, $optionData, 'opt_name', $name);
      } else {
        $optionData['opt_name'] = $name;
        $this->databaseInsertRecord($this->tableOptions, NULL, $optionData);
      }
    }
    // Load/set the new options
    $this->loadOptions();
    $this->addMsg(MSG_INFO, $this->_gt('Options successfully imported.'));
  }

  /**
  * Get form to import options from an XML file
  *
  * @access public
  */
  function getImportForm() {
    $hidden = array(
      'cmd' => 'do_import'
    );
    $fields = array(
      'Load options from XML',
      'xml_file' => array('File name', 'isNoHTML', TRUE, 'file', 15, '')
    );
    $data = array();
    $loadDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $loadDialog->dialogTitle = $this->_gt('Load all options from an XML file');
    $loadDialog->baseLink = $this->baseLink;
    $loadDialog->uploadFiles = TRUE;
    $loadDialog->buttonTitle = $this->_gt('Import');
    $loadDialog->loadParams();
    $this->layout->addRight($loadDialog->getDialogXML());
  }

  /**
  * Check if directory is writeable
  *
  * @param string $path
  * @param string $name
  * @access public
  * @return boolean
  */
  function checkDirectoryWriteable($path, $name) {
    $title = papaya_strings::escapeHTMLChars($this->_gt($name));
    if (!file_exists($path)) {
      umask(011);
      if (mkdir($path, 0777)) {
        $this->addMsg(MSG_INFO, $this->_gtf('%s created.', $title));
      }
    }
    if (is_dir($path)) {
      umask(011);
      $readable = is_readable($path);
      $writeable = is_writeable($path);
      $executable = @file_exists($path.'/.');
      if (!($readable && $writeable && $executable)) {
        if (@chmod($path, 0777)) {
          $this->addMsg(MSG_INFO, $this->_gtf('Changed permissions for "%s".', $title));
          return TRUE;
        } elseif (!$readable) {
          $this->addMsg(MSG_INFO, $this->_gtf('"%s" is not readable.', $title));
        } elseif (!$executable) {
          $this->addMsg(MSG_INFO, $this->_gtf('"%s" is not executable.', $title));
        } elseif (!$writeable) {
          $this->addMsg(MSG_INFO, $this->_gtf('"%s" is not writeable.', $title));
        }
        return FALSE;
      } elseif (is_writeable($path)) {
        $this->addMsg(MSG_INFO, $this->_gtf('"%s" is useable.', $title));
        return TRUE;
      }
    } else {
      $this->addMsg(MSG_INFO, $this->_gtf('Cannot create "%s".', $title));
    }
    return FALSE;
  }

  /**
  * Check if directories are writeable
  *
  * @access public
  */
  function checkDirectoriesWriteable() {
    if (trim(PAPAYA_PATH_DATA) != '') {
      $this->checkDirectoryWriteable(dirname(PAPAYA_PATH_MEDIAFILES), 'Media main path');
      $this->checkDirectoryWriteable(PAPAYA_PATH_MEDIAFILES, 'Media files path');
      $this->checkDirectoryWriteable(PAPAYA_PATH_THUMBFILES, 'Thumbnail path');
      $this->checkDirectoryWriteable(PAPAYA_PATH_CACHE, 'Cache path');
    } else {
      $this->papaya()->messages->dispatch(
        new PapayaMessageDisplay(
          PapayaMessage::SEVERITY_ERROR,
          $this->_gt('Please set and save the PAPAYA_PATH_DATA option.')
        )
      );
    }
  }
}


