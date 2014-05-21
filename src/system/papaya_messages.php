<?php
/**
* message service
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
* @subpackage Administration
* @version $Id: papaya_messages.php 39728 2014-04-07 19:51:21Z weinert $
*/

/**
* message service
*
* @package Papaya
* @subpackage Administration
*/
class papaya_messages extends base_messages {
  /**
  * Input field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'xx-large';

  /**
  * New message fields
  * @var array
  */
  var $newMessageFields = array(
    'Recipents',
    'to' => array('RCPT', 'isSomeText', TRUE, 'input', 2000),
    'cc' => array('CC', 'isSomeText', FALSE, 'input', 2000),
    'bcc' => array('BCC', 'isSomeText', FALSE, 'input', 2000),
    'mailme' => array('Email to sender', 'isNum', TRUE, 'yesno', '',
      'Do you want to receive a copy of this email?', 0, 'center'),
    'Message',
    'subject' => array('Subject', 'isSomeText', TRUE, 'input', 200),
    'priority' => array('Priority', 'isNum', TRUE, 'combo',
      array(0 => 'normal', 1 => 'high', 2 => 'urgent')),
    'message' => array('Text', 'isSomeText', TRUE, 'textarea', 30),
  );

  /**
   * @var base_dialog
   */
  private $dialog = NULL;


  /**
  * Initialize parameters
  *
  * @param string $paramName optional, default value 'msg'
  * @access public
  */
  function initialize($paramName = 'msg') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_messages_'.$paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('folder_id');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Basic class for handling parameters
  *
  * @access public
  */
  function execute() {
    $this->params['msg_id'] = (isset($this->params['msg_id'])) ?
      (int)$this->params['msg_id'] : 0;
    $this->loadMessage($this->params['msg_id']);
    if (isset($this->message) && is_array($this->message) &&
        ((int)$this->message['msg_folder_id'] >= (-1)) &&
        ($this->message['msg_new'])) {
      if ($this->updateReaded($this->params['msg_id'])) {
        $this->loadMessage($this->params['msg_id']);
      }
    }
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'reply':
        if (isset($this->message) && is_array($this->message)) {
          $this->loadUsers();
          $this->params['reply_to'] = $this->params['msg_id'];
          $this->params['to'] = sprintf(
            '%s <%s>',
            $this->getUserEmailName($this->users[$this->message['msg_from']]),
            $this->message['msg_from']
          );
          $this->initializeNewMessageForm();
          $this->dialog->params['reply_to'] = $this->params['msg_id'];
          $this->dialog->params['subject'] =
            (strpos($this->message['msg_subject'], 'Re:') !== 0) ?
              'Re: '.$this->message['msg_subject'] : $this->message['msg_subject'];
          $this->dialog->params['to'] = sprintf(
            '%s <%s@papaya>',
            $this->getUserEmailName($this->users[$this->message['msg_from']]),
            $this->users[$this->message['msg_from']]['username']
          );
          $this->dialog->params['message'] =
            sprintf(
              '%s %s %s:',
              $this->getUserEmailName($this->users[$this->message['msg_from']]),
              $this->_gt('wrote on'),
              date('Y-m-d H:i:s')
            )."\n\n".
            $this->rewrapMessage($this->message['msg_text'], 1, 75, TRUE);
        }
        $this->params['cmd'] = 'new';
        break;
      case 'forward':
        if (isset($this->message) && is_array($this->message)) {
          $this->loadUsers();
          $this->params['reply_to'] = $this->params['msg_id'];
          $this->initializeNewMessageForm();
          $this->dialog->params['reply_to'] = $this->params['msg_id'];
          $this->dialog->params['subject'] =
            (strpos($this->message['msg_subject'], 'Fw:') !== 0) ?
              'Fw: '.$this->message['msg_subject'] : $this->message['msg_subject'];
          $this->dialog->params['message'] =
            sprintf(
              '%s %s %s:',
              $this->getUserEmailName($this->users[$this->message['msg_from']]),
              $this->_gt('wrote on'),
              date('Y-m-d H:i:s')
            )."\n\n".
            $this->rewrapMessage($this->message['msg_text'], 0, 75, TRUE);
        }
        $this->params['cmd'] = 'new';
        break;
      case 'new':
        $this->initializeNewMessageForm();
        if ($this->dialog->modified()) {
          if ($this->dialog->checkDialogInput()) {
            $this->sendMessage();
          }
        }
        break;
      case 'del':
        if ($this->deleteMessage()) {
          $this->loadMessage($this->params['msg_id']);
        }
        break;
      }
    }
    $this->loadMessages();
    $this->loadUsers();
    $this->loadUserGroups();
  }


  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->layout->setParam('COLUMNWIDTH_LEFT', '100px');
      $this->layout->setParam('COLUMNWIDTH_CENTER', '50%');
      $this->layout->setParam('COLUMNWIDTH_RIGHT', '50%');
      $this->getFolderPanel();
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch ($this->params['cmd']) {
      case 'new':
        $this->layout->setParam('COLUMNWIDTH_CENTER', '100%');
        $this->layout->setParam('COLUMNWIDTH_RIGHT', 10);
        $this->getAddressJSForm();
        $this->getNewMessageForm();
        break;
      default:
        $this->getMessageView();
        $this->getMessageThread();
        $this->getMessageList();
      }
      $this->getButtons();
    }
  }

  /**
  * Get folder panel
  *
  * @access public
  */
  function getFolderPanel() {
    $images = $this->papaya()->images;
    $counts = $this->loadMessageCounts(array(0, -1, -2));
    $result = '<iconpanel align="left">';
    $result .= sprintf(
      '<icon src="%s" subtitle="%s" href="todo.php"/>',
      papaya_strings::escapeHTMLChars($images['items-task']),
      papaya_strings::escapeHTMLChars($this->getFolderTitle(-3))
    );
    $result .= sprintf(
      '<icon src="%s" subtitle="%s" href="%s"/>',
      papaya_strings::escapeHTMLChars(
        ($counts[0] > 0)
          ? $images['status-messages-inbox-full']
          : $images['categories-messages-inbox']
      ),
      papaya_strings::escapeHTMLChars($this->getFolderTitle(0)),
      papaya_strings::escapeHTMLChars(
        $this->getLink(array('folder_id' => 0), NULL, 'msgbox.php')
      )
    );
    $result .= sprintf(
      '<icon src="%s" subtitle="%s" href="%s"/>',
      papaya_strings::escapeHTMLChars(
        ($counts[-1] > 0)
          ? $images['status-messages-outbox-full']
          : $images['categories-messages-outbox']
      ),
      papaya_strings::escapeHTMLChars($this->getFolderTitle(-1)),
      papaya_strings::escapeHTMLChars(
        $this->getLink(array('folder_id' => '-1'), NULL, 'msgbox.php')
      )
    );
    $result .= sprintf(
      '<icon src="%s" subtitle="%s" href="%s"/>',
      papaya_strings::escapeHTMLChars(
        ($counts[-2] > 0) ? $images['status-trash-full'] :  $images['places-trash']
      ),
      papaya_strings::escapeHTMLChars($this->getFolderTitle(-2)),
      papaya_strings::escapeHTMLChars(
        $this->getLink(array('folder_id' => '-2'), NULL, 'msgbox.php')
      )
    );
    $result .= '</iconpanel>';
    $this->layout->addLeft($result);
  }

  /**
  * get message list
  *
  * @access public
  */
  function getMessageList() {
    if (isset($this->messageList) && is_array($this->messageList)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->getFolderTitle((int)$this->params['folder_id']))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s/%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Subject')),
        papaya_strings::escapeHTMLChars($this->_gt('Sender'))
      );
      $result .= '<col/>';
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Time'))
      );
      $result .= '</cols>';
      $result .= '<items>'.LF;
      $href = $this->baseLink.'?'.$this->paramName.'[msg_id]=';
      foreach ($this->messageList as $message) {
        $imageIndex = $this->getMsgGlyphIndex(
          $message['msg_type'],
          $message['msg_new'],
          isset($this->params['msg_id']) && $message['msg_id'] == $this->params['msg_id']
        );
        if (isset($this->params['msg_id']) && $message['msg_id'] == $this->params['msg_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem title="%s" subtitle="%s" image="%s" href="%s" %s>',
          papaya_strings::escapeHTMLChars($message['msg_subject']),
          papaya_strings::escapeHTMLChars(
            isset($this->users[$message['msg_from']])
              ? $this->getUserEmailName($this->users[$message['msg_from']])
              : $this->_gt('Unknown')
          ),
          papaya_strings::escapeHTMLChars($this->papaya()->images[$imageIndex]),
          papaya_strings::escapeHTMLChars($href.(int)$message['msg_id']),
          $selected
        );
        if ($priorityIndex = $this->getPriorityGlyphIndex($message['msg_priority'])) {
          $result .= sprintf(
            '<subitem align="center"><glyph src="%s"/></subitem>',
            papaya_strings::escapeHTMLChars($this->papaya()->images[$priorityIndex])
          );
        } else {
          $result .= '<subitem/>';
        }
        $result .= '<subitem align="center">'.
          date('Y-m-d H:i:s', $message['msg_datetime']).'</subitem>';
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->add($result);
    }
  }

  /**
  * Get message thread
  *
  * @access public
  */
  function getMessageThread() {
    if (isset($this->message) && is_array($this->message)) {
      $this->loadMessageThread($this->loadThreadId($this->message['msg_id']));
    }
    if (isset($this->messageThread) && is_array($this->messageThread)) {
      $rootIds = array();
      foreach ($this->messageThread as $msg) {
        if (isset($this->messageThread[$msg['msg_prev_id']])) {
          $this->messageThread[$msg['msg_prev_id']]['CHILDREN'][] = $msg['msg_id'];
        } else {
          $rootIds[] = $msg['msg_id'];
        }
      }
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars('Thread')
      );
      $result .= '<items>'.LF;
      $result .= $this->getMessageSubThread($rootIds, 0);
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->add($result);
    }
  }

  /**
  * Get message sub thread
  *
  * @param integer|array $ids
  * @param integer $indent
  * @access public
  * @return string '' or XML
  */
  function getMessageSubThread($ids, $indent) {
    $result = '';
    if (isset($ids) &&
        is_array($ids) &&
        isset($this->messageThread) &&
        is_array($this->messageThread)) {
      foreach ($ids as $id) {
        $message = $this->messageThread[$id];
        if (isset($message) && is_array($message)) {
          $imageIndex = $this->getMsgGlyphIndex(
            $message['msg_type'],
            $message['msg_new'],
            ($message['msg_id'] == $this->params['msg_id'])
          );
          $selected = ($message['msg_id'] == $this->params['msg_id'])
            ? ' selected="selected"' : '';
          $result .= sprintf(
            '<listitem title="%s" subtitle="%s" image="%s" href="%s" indent="%s" %s>',
            papaya_strings::escapeHTMLChars($message['msg_subject']),
            papaya_strings::escapeHTMLChars(
              $this->getUserEmailName($this->users[$this->message['msg_from']])
            ),
            $this->papaya()->images[$imageIndex],
            $this->getLink(
              array(
                'msg_id' => $message['msg_id'],
                'folder_id' => $message['msg_folder_id']
              )
            ),
            $indent,
            $selected
          );
          $result .= sprintf(
            '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
            $this->getFolderGlyph($message['msg_folder_id']),
            $this->getFolderTitle($message['msg_folder_id'])
          );
          $result .= '</listitem>'.LF;
          if (isset($message['CHILDREN']) && is_array($message['CHILDREN'])) {
            $result .= $this->getMessageSubThread($message['CHILDREN'], $indent + 1);
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get message view
  *
  * @access public
  */
  function getMessageView() {
    if (isset($this->message) && is_array($this->message)) {
      $result = '<sheet width="100%" align="center">';
      $result .= '<header>';
      $result .= '<lines>';
      $result .= sprintf(
        '<line class="headertitle">%s</line>',
        papaya_strings::escapeHTMLChars($this->message['msg_subject'])
      );
      $href = $this->getLink(array('cmd' => 'new', 'to' => $this->message['msg_from']));
      $result .= sprintf(
        '<line class="headersubtitle">%s: <a href="%s" class="email">%s</a></line>',
        papaya_strings::escapeHTMLChars('From'),
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars(
          $this->getUserEmailName($this->users[$this->message['msg_from']])
        )
      );
      $result .= sprintf(
        '<line class="headersubtitle">%s: %s</line>',
        papaya_strings::escapeHTMLChars('To'),
        $this->getAddressesStr($this->decodeAddresses($this->message['msg_to']), TRUE)
      );
      if (trim($this->message['msg_cc'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          papaya_strings::escapeHTMLChars('CC'),
          $this->getAddressesStr($this->decodeAddresses($this->message['msg_cc']), TRUE)
        );
      }
      if (trim($this->message['msg_bcc'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          papaya_strings::escapeHTMLChars('BCC'),
          $this->getAddressesStr($this->decodeAddresses($this->message['msg_bcc']), TRUE)
        );
      }
      $result .= '</lines>';
      $result .= '<infos>';
      $result .= sprintf(
        '<line>%s</line>',
        date('Y-m-d H:i:s', $this->message['msg_datetime'])
      );
      switch($this->message['msg_priority']) {
      case 2:
        $result .= sprintf(
          '<line><span style="color: #FF0000">%s</span></line>',
          papaya_strings::escapeHTMLChars($this->_gt('Urgent'))
        );
        break;
      case 1:
        $result .= sprintf(
          '<line>%s</line>',
          papaya_strings::escapeHTMLChars($this->_gt('High'))
        );
        break;
      }
      if ($this->message['msg_rel_topic_id'] > 0 ||
          $this->message['msg_rel_box_id'] > 0) {
        $result .= '<line>';
        if ($this->message['msg_rel_topic_id'] > 0) {
          $result .= sprintf(
            '<a href="topic.php?p_id=%d"><glyph src="%s" hint="%s"/></a>',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('page_id' => $this->message['msg_rel_topic_id']),
                'tt',
                'topic.php'
              )
            ),
            $this->papaya()->images['items-page'],
            papaya_strings::escapeHTMLChars($this->_gt('Goto page'))
          );
        }
        if ($this->message['msg_rel_box_id'] > 0) {
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" hint="%s"/></a>',
            $this->getLink(
              array(
                'mode' => 0,
                'cmd' => 'chg_show',
                'bid' => $this->message['msg_rel_box_id']
              ),
              'bb',
              'boxes.php'
            ),
            $this->papaya()->images['items-box'],
            papaya_strings::escapeHTMLChars($this->_gt('Goto box'))
          );
        }
        $result .= '</line>';
      }
      $result .= '</infos>';
      $result .= '</header>';
      $result .= '<text>';
      $result .= $this->formatMessage($this->message['msg_text']);
      $result .= '</text>';
      $result .= '</sheet>';
      $this->layout->addRight($result);
    }
  }

  /**
  * Get message glyph index
  *
  * @param integer $type
  * @param boolean $new
  * @param boolean $opened
  * @access public
  * @return integer
  */
  function getMsgGlyphIndex($type, $new, $opened) {
    if ($opened) {
      return 'status-mail-open';
    } elseif ($new) {
      return 'status-mail-new';
    } else {
      return 'items-mail';
    }
  }

  /**
  * Get priority glyph index
  *
  * @param integer $priority
  * @access public
  * @return integer
  */
  function getPriorityGlyphIndex($priority) {
    switch($priority) {
    case 2:
      return 'status-priority-highest';
    case 1:
      return 'status-priority-high';
    default:
      return 0;
    }
  }

  /**
  * Get folder title
  *
  * @param integer $index
  * @access public
  * @return string
  */
  function getFolderTitle($index) {
    switch ($index) {
    case 0:
      return $this->_gt('Incoming mails');
    case -1:
      return $this->_gt('Sent mails');
    case -2:
      return $this->_gt('Trash');
    case -3:
      return $this->_gt('Tasks');
    default:
      return '';
    }
  }

  /**
  * Get folder glyph
  *
  * @param integer $index
  * @access public
  * @return mixed
  */
  function getFolderGlyph($index) {
    switch ($index) {
    case 0:
      return $this->papaya()->images['categories-messages-inbox'];
    case -1:
      return $this->papaya()->images['categories-messages-outbox'];
    case -2:
      return $this->papaya()->images['places-trash'];
    case -3:
      return $this->papaya()->images['items-taks'];
    default:
      return '';
    }
  }

  /**
  * Get addresses string
  *
  * @param array $addr
  * @param boolean $linked optional, default value FALSE
  * @access public
  * @return string
  */
  function getAddressesStr($addr, $linked = FALSE) {
    if (isset($addr) && is_array($addr)) {
      $result = '';
      foreach ($addr as $item) {
        if ($linked) {
          if (isset($item['user_id'])) {
            $result .= sprintf(
              '<a href="%s" class="email">%s</a>, ',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'new',
                    'to' => sprintf('%s <%s>', $item['name'], $item['user_id'])
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($item['name'])
            );
          } elseif (isset($item['email'])) {
            $result .= sprintf(
              '<a href="mailto:%s" class="email">%s</a>, ',
              papaya_strings::escapeHTMLChars($item['email']),
              papaya_strings::escapeHTMLChars($item['name'])
            );
          } else {
            $result .= papaya_strings::escapeHTMLChars($item['name']).', ';
          }
        } else {
          $result .= papaya_strings::escapeHTMLChars($item['name']).', ';
        }
      }
      return substr($result, 0, -2);
    }
    return '';
  }

  /**
  * address string to Javascript
  *
  * @param string $str
  * @param array $field
  * @access public
  * @return string
  */
  function addrStrToJs($str, $field) {
    $addrs = $this->decodeAddresses($str);
    $result = '';
    if (isset($addrs) && is_array($addrs)) {
      $result .= sprintf('addr["%s"] = new Array;'.LF, $field);
      foreach ($addrs as $key => $val) {
        $address = isset($val['user_id']) ? $val['user_id'] : $val['email'];
        $result .= sprintf(
          'addr["%s"][%s] = "%s <%s>";'.LF,
          $field,
          $key,
          $val['name'],
          $address
        );
      }
    }
    return $result;
  }

  /**
  * Get address javascript form
  *
  * @access public
  */
  function getAddressJSForm() {
    $alertMessage = $this->_gt('Address already added.');
    $this->layout->addScript(
      '<script type="text/javascript" language="JavaScript" src="./script/mail.js"></script>'
    );
    $this->layout->addScript(
      '<script type="text/javascript" language="JavaScript"><![CDATA[
        var addr = new Array();
        var msg_wasadded = "'.$alertMessage.'";
        ]]></script>'
    );
    $result = sprintf(
      '<dialog action="#" title="%s" width="400">',
      papaya_strings::escapeHTMLChars($this->_gt('Address book'))
    );
    $result .= '<lines class="dialogLarge">';
    $result .= '<line>';
    $result .= sprintf(
      '<select name="%s[mailto]" class="dialogSelect dialogScale" id="addrcombo_mailto">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    if (isset($this->users) &&
        is_array($this->users) &&
        count($this->users) > 0) {
      $result .= sprintf(
        '<optgroup label="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Users'))
      );
      foreach ($this->users as $user) {
        $value = sprintf(
          '%s <%s@papaya>',
          papaya_strings::escapeHTMLChars($this->getUserEmailName($user)),
          papaya_strings::escapeHTMLChars($user['username'])
        );
        $result .= sprintf(
          '<option value="%s">%s</option>'.LF,
          papaya_strings::escapeHTMLChars($value),
          papaya_strings::escapeHTMLChars($value)
        );
      }
      $result .= '</optgroup>';
    }
    if (isset($this->userGroups) &&
        is_array($this->userGroups) &&
        count($this->userGroups) > 0) {
      $result .= sprintf(
        '<optgroup label="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('User groups'))
      );
      foreach ($this->userGroups as $group) {
        $value = sprintf(
          '%s <%s@group.papaya>',
          papaya_strings::escapeHTMLChars($this->getEmailName($group['grouptitle'])),
          papaya_strings::escapeHTMLChars(
            papaya_strings::normalizeString($group['grouptitle'])
          )
        );
        $result .= sprintf(
          '<option value="%s">%s</option>'.LF,
          papaya_strings::escapeHTMLChars($value),
          papaya_strings::escapeHTMLChars($value)
        );
      }
      $result .= '</optgroup>';
    }
    $result .= '</select>';
    $result .= '</line>'.LF;
    $result .= '</lines>';
    $result .= sprintf(
      '<dlgbutton type="button" value="add_address_to" caption="%s" hint="%s" ',
      papaya_strings::escapeHTMLChars($this->_gt('Add To')),
      papaya_strings::escapeHTMLChars($this->_gt('Add To'))
    );
    $result .= 'onclick="PapayaMails.addAddress(\'TO\');"/>';
    $result .= sprintf(
      '<dlgbutton type="button" value="add_address_cc" caption="%s" hint="%s" ',
      papaya_strings::escapeHTMLChars($this->_gt('Add CC')),
      papaya_strings::escapeHTMLChars($this->_gt('Add CC'))
    );
    $result .= 'onclick="PapayaMails.addAddress(\'CC\');"/>';
    $result .= sprintf(
      '<dlgbutton type="button" value="add_address_bcc" caption="%s" hint="%s" ',
      papaya_strings::escapeHTMLChars($this->_gt('Add BCC')),
      papaya_strings::escapeHTMLChars($this->_gt('Add BCC'))
    );
    $result .= 'onclick="PapayaMails.addAddress(\'BCC\');"/>';
    $result .= '</dialog>';
    $this->layout->add($result);
  }

  /**
  * Initialize new message form
  *
  * @access public
  */
  function initializeNewMessageForm() {
    if (!(isset($this->dialog) && is_object($this->dialog))) {
      $hidden = array(
        'cmd' => 'new',
        'save' => 1,
        'reply_to' => empty($this->params['reply_to']) ? 0 : (int)$this->params['reply_to']
      );
      $data = NULL;
      $this->dialog = new base_dialog(
        $this, $this->paramName, $this->newMessageFields, $data, $hidden
      );
      $this->dialog->dialogTitle = $this->_gt('New message');
      $this->dialog->buttonTitle = 'Send';
      $this->dialog->dialogId = 'dialogNewMessage';
      $this->dialog->inputFieldSize = $this->inputFieldSize;
      $this->dialog->dialogDoubleButtons = TRUE;
      $this->dialog->loadParams();
    }
  }

  /**
  * Get new message form
  *
  * @access public
  */
  function getNewMessageForm() {
    $this->initializeNewMessageForm();
    $this->layout->add($this->dialog->getDialogXML());
  }

  /**
  * Send message
  *
  * @access public
  */
  function sendMessage() {
    $addr['to'] = empty($this->params['to']) ? NULL : $this->decodeAddresses($this->params['to']);
    $addr['cc'] = empty($this->params['cc']) ? NULL : $this->decodeAddresses($this->params['cc']);
    $addr['bcc'] = empty($this->params['bcc'])
      ? NULL : $this->decodeAddresses($this->params['bcc']);
    if ($this->checkAndCompleteAddresses($addr)) {
      $email = new email();
      if (isset($addr['to']) && is_array($addr['to']) && count($addr['to']) > 0) {
        foreach ($addr['to'] as $data) {
          $email->addAddress($data['email'], $data['name']);
        }
      }
      if (isset($addr['cc']) && is_array($addr['cc']) && count($addr['cc']) > 0) {
        foreach ($addr['cc'] as $data) {
          $email->addAddress($data['email'], $data['name'], 'CC');
        }
      }
      if (isset($addr['bcc']) && is_array($addr['bcc']) && count($addr['bcc']) > 0) {
        foreach ($addr['bcc'] as $data) {
          $email->addAddress($data['email'], $data['name'], 'BCC');
        }
      }
      $administrationUser = $this->papaya()->administrationUser;
      if (PapayaFilterFactory::isEmail($administrationUser->user['email'], TRUE)) {
        $email->setSender(
          $this->getUserEmailAddress($administrationUser->user),
          $this->getUserEmailName($administrationUser->user)
        );
      }
      $email->setSubject($this->params['subject']);
      $email->setBody($this->params['message']);
      if ($email->send()) {
        $this->addMsg(MSG_INFO, $this->_gt('Emails sent.'));
      } else {
        $this->addMsg(MSG_WARNING, $this->_gt('Could not sent all emails.'));
      }
      $record = array(
        'msg_to' => empty($this->params['to']) ? '' : (string)$this->params['to'],
        'msg_cc' => empty($this->params['cc']) ? '' : (string)$this->params['cc'],
        'msg_bcc' => empty($this->params['bcc']) ? '' : (string)$this->params['bcc'],
        'msg_from' => empty($this->papaya()->administrationUser->userId)
          ? '' : (string)$this->papaya()->administrationUser->userId,
        'msg_datetime' => time(),
        'msg_subject' => empty($this->params['subject']) ? '' : (string)$this->params['subject'],
        'msg_text' => empty($this->params['message']) ? '' : (string)$this->params['message'],
        'msg_priority' => empty($this->params['priority'])
          ? '0' : (string)$this->params['priority'],
        'msg_prev_id' => empty($this->params['reply_to']) ? '0' : (string)$this->params['reply_to'],
        'msg_thread_id' => ($this->loadThreadId($this->params['reply_to']))
      );
      if (!empty($addr['to'])) {
        $this->saveMessage($addr['to'], $record);
      }
      if (!empty($addr['cc'])) {
        $this->saveMessage($addr['cc'], $record);
      }
      if (!empty($addr['bcc'])) {
        $this->saveMessage($addr['bcc'], $record);
      }

      //Postausgang
      $record['msg_owner_id'] = $this->papaya()->administrationUser->userId;
      $record['msg_bcc'] = empty($this->params['bcc']) ? '' : (string)$this->params['bcc'];
      $record['msg_folder_id'] = -1;
      if (!$this->databaseInsertRecord($this->tableMessages, 'msg_id', $record)) {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database error').' - '.$this->_gt('Message not saved.')
        );
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('No valid recepient specified'));
    }
  }

  /**
  * Get email string
  *
  * @param array $addrList
  * @access public
  * @return string
  */
  function getEMailString($addrList) {
    $result = '';
    if (isset($addrList) && is_array($addrList)) {
      foreach ($addrList as $addr) {
        $result .= sprintf('%s <%s>, ', $addr['name'], $addr['email']);
      }
      $result = substr($result, 0, -2);
    }
    return $result;
  }

  /**
  * Check and complete addresses
  *
  * @param array &$addressLists
  * @access public
  * @return boolean
  */
  function checkAndCompleteAddresses(&$addressLists) {
    $this->loadUsers();
    $this->loadUserGroups();
    $result = FALSE;
    $resultAddresses = NULL;
    $userNames = array();
    $groupNames = array();
    $groupIds = array();
    foreach ($this->users as $userId => $userData) {
      $userNames[$userData['username']] = $userId;
    }
    foreach ($this->userGroups as $groupId => $groupData) {
      $groupNames[papaya_strings::normalizeString($groupData['grouptitle'])] = $groupId;
    }
    foreach ($addressLists as $list) {
      if (isset($list) && is_array($list)) {
        foreach ($list as $val) {
          if (isset($val['group_name']) && (isset($groupNames[$val['group_name']]))) {
            $groupIds[(int)$groupNames[$val['group_name']]] = TRUE;
          }
        }
      }
    }
    $groupUsers = $this->loadGroupUsers(array_keys($groupIds));
    foreach ($addressLists as $field => $list) {
      if (isset($list) && is_array($list)) {
        foreach ($list as $val) {
          if (isset($val['email'])) {
            $resultAddresses[$field][] = $val;
            if ($field == 'to') {
              $result = TRUE;
            }
          } elseif (isset($val['user_id']) && (isset($this->users[$val['user_id']])) &&
              PapayaFilterFactory::isEmail($this->users[$val['user_id']]['email'], TRUE)) {
            $val['email'] = $this->users[$val['user_id']]['email'];
            $resultAddresses[$field][] = $val;
            if ($field == 'to') {
              $result = TRUE;
            }
          } elseif (
            isset($val['user_login']) &&
            isset($userNames[$val['user_login']]) &&
            PapayaFilterFactory::isEmail(
              $this->users[$userNames[$val['user_login']]]['email'], TRUE
            )
          ) {
            $val['email'] = $this->users[$userNames[$val['user_login']]]['email'];
            $val['user_id'] = $userNames[$val['user_login']];
            $resultAddresses[$field][] = $val;
            if ($field == 'to') {
              $result = TRUE;
            }
          } elseif (isset($val['group_name']) &&
                    isset($groupNames[$val['group_name']]) &&
                    ($groupId = $groupNames[$val['group_name']]) &&
                    isset($groupUsers[$groupId])) {
            foreach ($groupUsers[$groupId] as $user) {
              $val['email'] = $user['email'];
              $val['user_id'] = $user['user_id'];
              $resultAddresses[$field][] = $val;
              if ($field == 'to') {
                $result = TRUE;
              }
            }
          } else {
            $this->addMsg(
              MSG_WARNING,
              $this->_gtf('Invalid Recipient "%s".', $val['name'])
            );
          }
        }
      }
    }
    $addressLists = $resultAddresses;
    return $result;
  }

  /**
  * Load user data for a specific set of groups
  * @param $groupIds
  * @return array
  */
  function loadGroupUsers($groupIds) {
    $result = array();
    if ($filter = $this->databaseGetSQLCondition('group_id', $groupIds)) {
      $sql = "SELECT group_id, user_id, email
                FROM %s g
               WHERE $filter";
      if ($res = $this->databaseQueryFmt($sql, $this->tableAuthUser)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['group_id']][$row['user_id']] = $row;
        }
      }
      $filter = $this->databaseGetSQLCondition('gl.group_id', $groupIds);
      $sql = "SELECT gl.group_id, gl.user_id, u.email
                FROM %s u, %s gl
               WHERE u.user_id = gl.user_id
                 AND $filter";
      $params = array($this->tableAuthUser, $this->tableAuthLinks);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['group_id']][$row['user_id']] = $row;
        }
      }
    }
    return $result;
  }

  /**
  * Get buttons
  *
  * @access public
  */
  function getButtons() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;

    $toolbar->addButton(
      'Add task',
      $this->getLink(array('cmd' => 'new', 'todo_id' => 0), 'todo', 'todo.php'),
      'actions-task-add',
      'Add a new task'
    );
    $toolbar->addButton(
      'Compose message',
      $this->getLink(array('cmd' => 'new')),
      'actions-mail-add',
      'Compose a new message'
    );
    $toolbar->addSeperator();
    if (isset($this->message) && is_array($this->message)) {
      $toolbar->addButton(
        'Answer',
        $this->getLink(array('cmd' => 'reply', 'msg_id' => $this->message['msg_id'])),
        'actions-mail-reply-sender',
        ''
      );
      $toolbar->addButton(
        'Forward',
        $this->getLink(array('cmd' => 'forward', 'msg_id' => $this->message['msg_id'])),
        'actions-mail-forward',
        ''
      );
      $toolbar->addButton(
        'Delete',
        $this->getLink(array('cmd' => 'del', 'msg_id' => $this->message['msg_id'])),
        'actions-mail-delete',
        ''
      );
    }

    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $str));
    }
  }
}


