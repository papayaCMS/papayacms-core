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

/**
* Linktypes Administration
*
* @package Papaya
* @subpackage Administration
*/
class papaya_linktypes extends base_linktypes {

  /**
  * @var array $targets list of available targets
  */
  var $targets = array(0 => '_self', 1 => '_blank', 2 => '_parent');

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var base_dialog
   */
  private $dialog = NULL;

  /**
  * php 5 constructor, sets param name
  */
  function __construct($paramName = 'lt') {
    parent::__construct();
    $this->paramName = $paramName;
  }

  /**
  * php 4 constructor
  */
  function papaya_tags($paramName = 'lt') {
    $this->__construct($paramName);
  }

  /**
  * initialize lngselect, session, etc.
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();

    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('contentmode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * execute administrative actions
  */
  function execute() {
    if (isset($this->params['contentmode']) && $this->params['contentmode'] == 1
        && !isset($this->params['cmd'])) {
      $this->params['cmd'] = 'edit_popup';
    } elseif (empty($this->params['contentmode']) &&
              isset($this->params['ltid']) &&
              $this->params['ltid'] > 2 &&
              empty($this->params['cmd'])) {
      $this->params['cmd'] = 'edit_linktype';
    }

    $this->loadLinkTypes();

    if (isset($this->params['cmd'])) {
      switch($this->params['cmd']) {
      case 'add_linktype':
        $this->dialog = $this->getEditLinkTypeDialog($this->params['cmd']);
        if (isset($this->params['confirm']) && $this->params['confirm']
          && $this->dialog->checkDialogInput() && ($newId = $this->addLinkType())) {
          unset($this->linkTypes);
          unset($this->dialog);
          $this->params['ltid'] = $newId;
          $this->dialog = $this->getEditLinkTypeDialog('edit_linktype');
        }
        break;
      case 'edit_linktype':
        $this->params['contentmode'] = 0;
        $this->dialog = $this->getEditLinkTypeDialog($this->params['cmd']);
        if (isset($this->params['ltid']) && (int)$this->params['ltid'] > 2
            && isset($this->params['confirm']) && $this->params['confirm']
            && $this->dialog->checkDialogInput() && $this->setLinktype()) {
          $this->getEditLinkTypeDialog($this->params['cmd']);
          unset($this->linkTypes);
        }
        break;
      case 'del_linktype':
        if (isset($this->params['confirm']) && $this->params['confirm']) {
          $this->delLinktype();
          unset($this->linkTypes);
        } else {
          $this->dialog = $this->getDelLinkTypeDialog();
        }
        break;
      case 'edit_popup':
        $this->dialog = $this->getEditPopupPropertiesDialog($this->params['cmd']);
        if (isset($this->params['ltid']) && (int)$this->params['ltid'] > 2
            && isset($this->params['confirm']) && $this->params['confirm']
            && $this->dialog->checkDialogInput() && $this->setLinktype()) {
          $this->getEditPopupPropertiesDialog($this->params['cmd']);
          unset($this->linkTypes);
        }
        break;
      }
    }

    if (!isset($this->linkTypes)) {
      $this->loadLinkTypes();
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * generate output XML
  */
  function getXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $menubar->addButton(
      'Add linktype',
      $this->getLink(
        array('cmd' => 'add_linktype')
      ),
      'actions-link-add',
      '',
      FALSE
    );
    if (isset($this->params['ltid'])) {
      $menubar->addButton(
        'Delete linktype',
        $this->getLink(
          array(
            'cmd' => 'del_linktype',
            'ltid' => $this->params['ltid']
          )
        ),
        'actions-link-delete',
        '',
        FALSE
      );
    }

    if ($str = $menubar->getXML()) {
      $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $str));
    }

    $this->getToolbar();

    $this->layout->addLeft($this->getLinkTypesList());

    if (isset($this->dialog) && get_class($this->dialog) == 'base_dialog') {
      $this->layout->add($this->dialog->getDialogXML());
    }
  }

  /**
  * generate toolbar
  */
  function getToolbar() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;

    if (isset($this->params['ltid']) && $this->params['ltid'] > 2) {
      $toolbar->addButton(
        'General',
        $this->getLink(
          array('contentmode' => 0, 'ltid' => $this->params['ltid'])
        ),
        'categories-properties',
        '',
        empty($this->params['contentmode']) || $this->params['contentmode'] == 0
      );
      if ($this->linkTypes[$this->params['ltid']]['linktype_is_popup'] == 1) {
        $toolbar->addButton(
          'Popup configuration',
          $this->getLink(array('contentmode' => 1, 'ltid' => $this->params['ltid'])),
          'categories-content',
          '',
          isset($this->params['contentmode']) && $this->params['contentmode'] == 1
        );
      }
    }

    if ($str = $toolbar->getXML()) {
      $this->layout->add('<toolbar>'.$str.'</toolbar>', 'toolbars');
    }
  }

  /**
  * generate list of existing linktypes
  *
  * @return string $result linktypes listview
  */
  function getLinkTypesList() {
    $result = '';
    $images = $this->papaya()->images;
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Linktypes'))
    );
    if (isset($this->linkTypes) &&
        is_array($this->linkTypes) &&
        count($this->linkTypes) > 0) {
      $result .= '<items>'.LF;
      foreach ($this->linkTypes as $linkType) {
        if ($linkType['linktype_id'] < 3) {
          $result .= sprintf(
            '<listitem title="%s" image="%s" />'.LF,
            papaya_strings::escapeHTMLChars($linkType['linktype_name']),
            papaya_strings::escapeHTMLChars($images['status-link-locked'])
          );
        } else {
          $linkParams = array(
            'cmd' => 'edit_linktype',
            'ltid' => $linkType['linktype_id']
          );
          if (isset($this->params['ltid']) &&
              $linkType['linktype_id'] == $this->params['ltid']) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem title="%s" href="%s" image="%s" %s>'.LF,
            papaya_strings::escapeHTMLChars($linkType['linktype_name']),
            papaya_strings::escapeHTMLChars($this->getLink($linkParams)),
            papaya_strings::escapeHTMLChars($images['items-link']),
            $selected
          );
          $result .= '</listitem>'.LF;
        }
      }
      $result .= '</items>'.LF;
    }
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * initialize dialog to edit linktypes
  *
  * @param string $cmd command to pass
  * @return object $dialog base_dialog
  */
  function getEditLinkTypeDialog($cmd) {
    $data = array();
    $title = 'Add linktype';
    $hidden = array(
      'cmd' => $cmd,
      'confirm' => 1,
    );
    if ($cmd == 'edit_linktype' && isset($this->params['ltid']) && (int)$this->params['ltid'] > 2) {
      $hidden['ltid'] = $this->params['ltid'];
      if (isset($this->linkTypes[$this->params['ltid']])) {
        $linkType = $this->linkTypes[$this->params['ltid']];
        $data = array(
          'linktype_name' => $linkType['linktype_name'],
          'linktype_is_visible' => $linkType['linktype_is_visible'],
          'linktype_class' => $linkType['linktype_class'],
          'linktype_target' => $linkType['linktype_target'],
          'linktype_is_popup' => $linkType['linktype_is_popup'],
        );
      }
      $title = 'Edit linktype';
    }

    $fields = array(
      'linktype_name' => array('Linktype name', 'isNoHTML', TRUE, 'input', 100),
      'linktype_is_visible' => array('Visible?', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes')), '', 1),
      'linktype_class' => array('CSS class', 'isNoHTML', FALSE, 'input', 100),
      'linktype_target' => array('Link target', 'isNum', FALSE, 'combo', $this->targets),
      'linktype_is_popup' => array('Link is popup', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes'))),
    );

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->loadParams();
    $this->dialog->inputFieldSize = 'x-large';
    $this->dialog->dialogTitle = $this->_gt($title);
    $this->dialog->dialogDoubleButtons = FALSE;
    return $this->dialog;
  }

  /**
  * generates dialog to edit linktype properties
  *
  * @param string $cmd current command
  * @return object $dialog base_dialog
  */
  function getEditPopupPropertiesDialog($cmd) {
    $data = array();
    $title = 'Edit popup properties';
    $hidden = array(
      'cmd' => $cmd,
      'confirm' => 1,
    );
    if ($cmd == 'edit_popup' && isset($this->params['ltid']) && (int)$this->params['ltid'] > 2) {
      $linkType = $this->getLinkType($this->params['ltid'], TRUE);
      $hidden['ltid'] = $this->params['ltid'];
      $data = array(
        'popup_height' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'height', ''),
        'popup_location' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'location', ''),
        'popup_menubar' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'menubar', ''),
        'popup_resizable' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'resizable', ''),
        'popup_left' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'left', ''),
        'popup_top' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'top', ''),
        'popup_scrollbars' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'scrollbars', ''),
        'popup_status' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'status', ''),
        'popup_toolbar' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'toolbar', ''),
        'popup_width' => \Papaya\Utility\Arrays::get($linkType['popup_config'], 'width', '')
      );
      $title = 'Edit popup properties';
    }

    $fields = array(
      'Size, Position',
      'popup_width' => array('Width', 'isNum', TRUE, 'input', 100),
      'popup_height' => array('Height', 'isNum', TRUE, 'input', 100),
      'popup_left' =>
        array('Left', 'isNum', FALSE, 'input', 100, 'Position from left side of screen.'),
      'popup_top' =>
        array('Top', 'isNum', FALSE, 'input', 100, 'Position from top of screen.'),
      'popup_resizable' => array('Resizable', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes')), 'Popup may be resized?', 1),
      'Display Bars',
      'popup_location' => array('Locationbar', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes'))),
      'popup_menubar' => array('Menubar', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes'))),
      'popup_scrollbars' => array('Scrollbars', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes'), 2 => $this->_gt('Automatic'))),
      'popup_status' => array('Statusbar', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes'))),
      'popup_toolbar' => array('Toolbar', 'isNum', TRUE, 'combo',
        array(0 => $this->_gt('No'), 1 => $this->_gt('Yes')))
    );

    $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $this->dialog->loadParams();
    $this->dialog->inputFieldSize = 'x-large';
    $this->dialog->dialogTitle = $this->_gt($title);
    $this->dialog->dialogDoubleButtons = FALSE;
    return $this->dialog;
  }

  /**
  * generate confirmation dialog to delete linktype
  *
  * ltid > 2 is on purpose! linktypes 1 and 2 are preset and may not be altered
  */
  function getDelLinkTypeDialog() {
    if (isset($this->params['ltid']) && $this->params['ltid'] > 2) {
      $hidden = array(
        'cmd' => $this->params['cmd'],
        'ltid' => $this->params['ltid'],
        'confirm' => 1,
      );
      $this->dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Do you really want to delete linktype "%s" (%d)?'),
          $this->linkTypes[$this->params['ltid']]['linktype_name'],
          $this->params['ltid']
        ),
        'question'
      );
      $this->dialog->buttonTitle = 'Delete';
      $this->layout->add($this->dialog->getMsgDialog());
    }
  }

  /**
  * add linktype to database
  */
  function addLinkType() {
    $data = array(
      'linktype_name' => $this->params['linktype_name'],
      'linktype_is_visible' => $this->params['linktype_is_visible'],
      'linktype_class' => $this->params['linktype_class'],
      'linktype_target' => $this->params['linktype_target'],
      'linktype_is_popup' => $this->params['linktype_is_popup'],
    );
    if ($ltId = $this->databaseInsertRecord($this->tableLinkTypes, 'linktype_id', $data)) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('New linktype "%s" (%d) has been added successfully.'),
          $this->params['linktype_name'],
          $ltId
        )
      );
      return $ltId;
    }
    return FALSE;
  }

  /**
  * update existing linktype in database
  *
  * ltid > 2 is on purpose! linktypes 1 and 2 are preset and may not be altered
  */
  function setLinktype() {
    if (isset($this->params['ltid']) && (int)$this->params['ltid'] > 2) {
      if (isset($this->params['cmd']) && $this->params['cmd'] == 'edit_popup') {
        $popupConfig = array(
          // 'dependent' => $this->params['popup_dependent'],
          'height' => $this->params['popup_height'],
          'location' => $this->params['popup_location'],
          'menubar' => $this->params['popup_menubar'],
          'resizable' => $this->params['popup_resizable'],
          'left' => $this->params['popup_left'],
          'top' => $this->params['popup_top'],
          'scrollbars' => $this->params['popup_scrollbars'],
          'status' => $this->params['popup_status'],
          'toolbar' => $this->params['popup_toolbar'],
          'width' => $this->params['popup_width'],
        );
        $data = array(
          'linktype_popup_config' => \Papaya\Utility\Text\XML::serializeArray($popupConfig)
        );
      } else {
        $data = array(
          'linktype_name' => $this->params['linktype_name'],
          'linktype_is_visible' => $this->params['linktype_is_visible'],
          'linktype_class' => $this->params['linktype_class'],
          'linktype_target' => $this->params['linktype_target'],
          'linktype_is_popup' => $this->params['linktype_is_popup'],
        );
      }
      $condition = array('linktype_id' => $this->params['ltid']);
      if ($this->databaseUpdateRecord($this->tableLinkTypes, $data, $condition)) {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('Linktype "%s" (%d) updated successfully.'),
            $this->linkTypes[$this->params['ltid']]['linktype_name'],
            $this->params['ltid']
          )
        );
        $this->loadLinkTypes();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * delete linktype
  *
  * ltid > 2 is on purpose! linktypes 1 and 2 are preset and may not be altered
  */
  function delLinktype() {
    if (isset($this->params['ltid']) && (int)$this->params['ltid'] > 2) {
      $condition = array('linktype_id' => $this->params['ltid']);
      if ($this->databaseDeleteRecord($this->tableLinkTypes, $condition)) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('Linktype #%d deleted.'), $this->params['ltid'])
        );
      }
      unset($this->params['ltid']);
      unset ($this->linkTypes);
    }
  }

}
