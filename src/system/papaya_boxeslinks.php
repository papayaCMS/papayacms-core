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

use Papaya\Administration\Permissions;

/**
* Link Box with page
*
* @package Papaya
* @subpackage Core
*/
class papaya_boxeslinks extends base_boxeslinks {
  /**
  * Boxes list
  * @var array $boxesList
  */
  var $boxesList = array();
  /**
  * Link list
  * @var array $linkList
  */
  var $linkList = array();
  /**
  * Used
  * @var array $used
  */
  var $used = array();

  /**
   * @var array
   */
  private $opened = array();

  /**
   * @var array
   */
  private $usedGroups = array();

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_boxes';
    $this->initializeParams($this->sessionParamName);
    $this->initializeNodes();
  }

  /**
  * Initiation nodes
  *
  * @access public
  */
  function initializeNodes() {
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->opened = $this->sessionParams['opened'];
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open':
        if ($this->params['gid'] > 0) {
          $this->opened[$this->params['gid']] = TRUE;
        }
        break;
      case 'close':
        if (isset($this->opened[$this->params['gid']])) {
          unset($this->opened[$this->params['gid']]);
        }
        break;
      }
      $this->sessionParams['opened'] = $this->opened;
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
  }

  /**
  * Load list
  *
  * @access public
  */
  function loadList() {
    $this->linkList = array();
    $this->used = array();
    $this->usedGroups = array();
    $sql = "SELECT boxlink_id, box_id, boxgroup_id, box_sort, topic_id
              FROM %s
             WHERE (topic_id = %d and boxgroup_id = 0)
                OR (topic_id = %d and box_id = 0)
             ORDER BY box_sort";
    $params = array($this->tableLink, $this->topicId, $this->groupPageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!empty($this->topicId) && $this->topicId == $row['topic_id']) {
          $this->linkList[$row['boxlink_id']] = $row;
          $this->used[] = $row['box_id'];
        } elseif (!empty($this->groupPageId) && $this->groupPageId == $row['topic_id']) {
          $this->linkList[$row['boxlink_id']] = $row;
          $this->usedGroups[] = $row['boxgroup_id'];
        }
      }
    }
  }

  /**
  * Load box list
  *
  * @access public
  */
  function loadBoxList() {
    $this->boxesList = array();
    $sql = "SELECT bl.box_id, bl.box_name, bl.boxgroup_id, bl.box_modified,
                   bl.box_unpublished_languages,
                   bp.box_modified as box_published,
                   bp.box_public_from, bp.box_public_to
              FROM %s bl
              LEFT OUTER JOIN %s bp ON bp.box_id = bl.box_id
             ORDER BY bl.box_name";
    $params = array($this->tableBox, $this->tableBoxPublic);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->boxesList[$row['box_id']] = $row;
      }
    }
  }

  /**
   * Load data list
   *
   * @param integer $lngId
   * @param integer $viewModeId
   * @param null $now
   * @return boolean
   * @access public
   */
  function loadDataList($lngId, $viewModeId, $now = NULL) {
    $this->data = array();
    if ($viewModeId > 0) {
      $sql = "SELECT bl.topic_id, bl.box_id, bl.box_sort,
                     b.box_name, b.boxgroup_id, b.box_deliverymode,
                     bt.box_title, bt.box_data, bt.view_id,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s bl,
                     %s b,
                     %s bt,
                     %s v,
                     %s vl,
                     %s m
               WHERE bl.topic_id = '%d'
                 AND b.box_id = bl.box_id
                 AND bt.box_id = bl.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND vl.view_id = bt.view_id
                 AND vl.viewmode_id = %d
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'
               ORDER BY bl.topic_id, bl.box_sort, bl.box_id";
      $params = array($this->tableLink, $this->tableBox, $this->tableBoxTrans,
        $this->tableViews, $this->tableViewLinks, $this->tableModules,
        $this->topicId, $lngId, $viewModeId);
    } else {
      $sql = "SELECT bl.topic_id, bl.box_id, bl.box_sort,
                     b.box_name, b.boxgroup_id, b.box_deliverymode,
                     bt.box_title, bt.box_data, bt.view_id,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s bl,
                     %s b,
                     %s bt,
                     %s v,
                     %s m
               WHERE bl.topic_id = '%d'
                 AND b.box_id = bl.box_id
                 AND bt.box_id = bl.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'
               ORDER BY bl.topic_id, bl.box_sort, bl.box_id";
      $params = array($this->tableLink, $this->tableBox, $this->tableBoxTrans,
        $this->tableViews, $this->tableModules, $this->topicId, $lngId);
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['module_file'] = $row['module_path'].$row['module_file'];
        $this->data[$row['box_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  public function getModeDialog($mode) {
    $dialog = new \PapayaUiDialog();
    $dialog->caption = new \PapayaUiStringTranslated('Link Mode');
    $dialog->options->captionStyle = \PapayaUiDialogOptions::CAPTION_NONE;
    if ($this->papaya()->options->get('PAPAYA_FEATURE_BOXGROUPS_LINKABLE', FALSE)) {
      $modes = array(
        self::INHERIT_ALL => new \PapayaUiStringTranslated('None'),
        self::INHERIT_BOXES => new \PapayaUiStringTranslated('Groups'),
        self::INHERIT_GROUPS => new \PapayaUiStringTranslated('Boxes'),
        self::INHERIT_NONE => new \PapayaUiStringTranslated('Boxes and groups')
      );
    } else {
      $modes = array(
        self::INHERIT_ALL => new \PapayaUiStringTranslated('None'),
        self::INHERIT_NONE => new \PapayaUiStringTranslated('Boxes')
      );
    }

    $dialog->fields[] = $field = new \PapayaUiDialogFieldSelectRadio(
      new \PapayaUiStringTranslated(
        'Attach to page'
      ),
      'box_useparent',
      $modes
    );
    $field->setDefaultValue($mode);
    $dialog->buttons[] = new \PapayaUiDialogButtonSubmit(
      new \PapayaUiStringTranslated('Save')
    );
    return $dialog;
  }

  /**
  * Get list
  *
  * @param array|\PapayaUiImages $images
  * @param integer $mode
  * @param string $caption
  * @access public
  * @return string '' or XML
  */
  function getList($images, $mode, $caption) {
    $administrationUser = $this->papaya()->administrationUser;
    $linkBoxes = TRUE;
    $linkGroups = TRUE;
    switch ($mode) {
    case self::INHERIT_ALL :
      $linkBoxes = FALSE;
      $linkGroups = FALSE;
      break;
    case self::INHERIT_GROUPS :
      $linkGroups = FALSE;
      break;
    case self::INHERIT_BOXES :
      $linkBoxes = FALSE;
      break;
    }
    $listview = new \PapayaUiListview();
    $listview->caption = new \PapayaUiStringTranslated('Boxes And Box Groups');

    if ($this->papaya()->options->get('PAPAYA_FEATURE_BOXGROUPS_LINKABLE', FALSE)) {
      if ($linkGroups) {
        $listview->items[] = $item = new \PapayaUiListviewItem(
          'items-page',
          new \PapayaUiStringTranslated('Linked Groups')
        );
        $item->columnSpan = 5;
        foreach ($this->boxGroupsList as $groupId => $group) {
          if ($group['boxgroup_linkable'] && in_array($groupId, $this->usedGroups)) {
            $listview->items[] = $item = new \PapayaUiListviewItem(
              'items-folder', $group['boxgroup_title']
            );
            $item->indentation = 1;
            $item->subitems[] = new \PapayaUiListviewSubitemText($group['boxgroup_name']);
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
            $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
              'actions-list-remove',
              new \PapayaUiStringTranslated('Remove'),
              array(
                $this->paramName => array(
                  'cmd' => 'group_unlink',
                  'boxgroup_id' => $groupId,
                  'page_id' => $this->groupPageId
                )
              )
            );
          }
        }
        $listview->items[] = $item = new \PapayaUiListviewItem(
          'items-page',
          new \PapayaUiStringTranslated('Available Groups')
        );
        $item->columnSpan = 5;
        foreach ($this->boxGroupsList as $groupId => $group) {
          if ($group['boxgroup_linkable'] && !in_array($groupId, $this->usedGroups)) {
            $listview->items[] = $item = new \PapayaUiListviewItem(
              'items-folder', $group['boxgroup_title']
            );
            $item->indentation = 1;
            $item->subitems[] = new \PapayaUiListviewSubitemText($group['boxgroup_name']);
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
            $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
              'actions-list-add',
              new \PapayaUiStringTranslated('Add'),
              array(
                $this->paramName => array(
                  'cmd' => 'group_link',
                  'boxgroup_id' => $groupId,
                  'page_id' => $this->groupPageId
                )
              )
            );
          }
        }
      } else {
        $listview->items[] = $item = new \PapayaUiListviewItem(
          'items-page',
          new \PapayaUiStringTranslated('Inherited Groups')
        );
        $item->columnSpan = 5;
        foreach ($this->boxGroupsList as $groupId => $group) {
          if ($group['boxgroup_linkable'] && in_array($groupId, $this->usedGroups)) {
            $listview->items[] = $item = new \PapayaUiListviewItem(
              'items-folder', $group['boxgroup_title']
            );
            $item->indentation = 1;
            $item->subitems[] = new \PapayaUiListviewSubitemText($group['boxgroup_name']);
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
            $item->subitems[] = new \PapayaUiListviewSubitemText('');
          }
        }
      }
    }

    $groupedBoxLinks = array();
    $groupedBoxes = array();
    foreach ($this->linkList as $boxLink) {
      if (isset($this->boxesList[$boxLink['box_id']])) {
        $box = $this->boxesList[$boxLink['box_id']];
        $groupedBoxLinks[$box['boxgroup_id']][$boxLink['box_id']] = $boxLink;
      }
    }
    foreach ($this->boxesList as $box) {
      $groupedBoxes[$box['boxgroup_id']][$box['box_id']] = $box;
    }
    $listview->items[] = $item = new \PapayaUiListviewItem(
      'items-page',
      new \PapayaUiStringTranslated($linkBoxes ?'Linked Boxes' : 'Inherited Boxes')
    );
    $item->columnSpan = 5;
    foreach ($this->boxGroupsList as $groupId => $group) {
      if (!empty($groupedBoxLinks[$groupId])) {
        $boxLinks = $groupedBoxLinks[$groupId];
        $opened = (isset($this->opened[$groupId]) && $this->opened[$groupId]);
        $listview->items[] = $item = new \PapayaUiListviewItem(
          $opened ? 'status-folder-open' : 'items-folder',
          $group['boxgroup_title']
        );
        $item->indentation = 1;
        $item->node()->status = $opened
          ? \PapayaUiListviewItemNode::NODE_OPEN
          : \PapayaUiListviewItemNode::NODE_CLOSED;
        $item->node()->reference()->setParameters(
          array(
            'cmd' => $opened ? 'close' : 'open', 'gid' => $groupId, 'page_id' => $this->topicId
          ),
          $this->paramName
        );
        $item->subitems[] = new \PapayaUiListviewSubitemText($group['boxgroup_name']);
        $item->subitems[] = new \PapayaUiListviewSubitemText('');
        $item->subitems[] = new \PapayaUiListviewSubitemText('');
        $item->subitems[] = new \PapayaUiListviewSubitemText('');
        if ($opened) {
          /** @var \PapayaUiListviewItem|NULL $previousItem */
          $previousItem = NULL;
          /** @var array|NULL $previousLink */
          $previousLink = NULL;
          foreach ($boxLinks as $boxId => $boxLink) {
            if (isset($groupedBoxes[$groupId][$boxId])) {
              $box = $groupedBoxes[$groupId][$boxId];
              $listview->items[] = $item = new \PapayaUiListviewItem(
                $this->getBoxStatusImage($box), $box['box_name']
              );
              $item->indentation = 2;
              if ($administrationUser->hasPerm(Permissions::BOX_MANAGE)) {
                $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
                  'actions-edit',
                  new \PapayaUiStringTranslated('Edit box'),
                  array(
                    'bb' => array(
                      'cmd' => 'chg_show',
                      'bid' => $box['box_id'],
                      'p_mode' => 1
                    )
                  )
                );
                $subitem->reference(clone $listview->reference);
                $subitem->reference()->setRelative('boxes.php');
              } else {
                $item->columnSpan = 2;
              }
              if ($linkBoxes) {
                if (isset($previousItem)) {
                  $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
                    'actions-go-up',
                    new \PapayaUiStringTranslated('Move up'),
                    array(
                      $this->paramName => array(
                        'cmd' => 'up',
                        'boxlink_id' => $boxLink['boxlink_id'],
                        'page_id' => $this->topicId
                      )
                    )
                  );
                  $previousItem->subitems[2] = $subitem = new \PapayaUiListviewSubitemImage(
                    'actions-go-down',
                    new \PapayaUiStringTranslated('Move down'),
                    array(
                      $this->paramName => array(
                        'cmd' => 'down',
                        'boxlink_id' => $previousLink['boxlink_id'],
                        'page_id' => $this->topicId
                      )
                    )
                  );
                } else {
                  $item->subitems[] = new \PapayaUiListviewSubitemText('');
                }
                $item->subitems[] = new \PapayaUiListviewSubitemText('');
                $previousItem = $item;
                $previousLink = $boxLink;

                $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
                  'actions-list-remove',
                  new \PapayaUiStringTranslated('Remove'),
                  array(
                    $this->paramName => array(
                      'cmd' => 'del',
                      'boxlink_id' => $boxLink['boxlink_id'],
                      'page_id' => $this->topicId
                    )
                  )
                );
              } else {
                $item->subitems[] = new \PapayaUiListviewSubitemText('');
                $item->subitems[] = new \PapayaUiListviewSubitemText('');
                $item->subitems[] = new \PapayaUiListviewSubitemText('');
              }
            }
          }
        }
      }
    }
    if ($linkBoxes) {
      $listview->items[] = $item = new \PapayaUiListviewItem(
        'items-page',
        new \PapayaUiStringTranslated('Available Boxes')
      );
      $item->columnSpan = 5;
      foreach ($this->boxGroupsList as $groupId => $group) {
        $boxes = empty($groupedBoxes[$groupId]) ? array() : $groupedBoxes[$groupId];
        $opened = (isset($this->opened[$groupId]) && $this->opened[$groupId]);
        if (!empty($groupedBoxLinks[$groupId])) {
          $linkCount = count($groupedBoxLinks[$groupId]);
        } else {
          $linkCount = 0;
        }
        if (!$group['boxgroup_linkable'] && count($boxes) > 0 && count($boxes) > $linkCount) {
          $listview->items[] = $item = new \PapayaUiListviewItem(
            $opened ? 'status-folder-open' : 'items-folder',
            $group['boxgroup_title']
          );
          $item->indentation = 1;
          $item->node()->status = $opened
            ? \PapayaUiListviewItemNode::NODE_OPEN
            : \PapayaUiListviewItemNode::NODE_CLOSED;
          $item->node()->reference()->setParameters(
            array(
              'cmd' => $opened ? 'close' : 'open', 'gid' => $groupId, 'page_id' => $this->topicId
            ),
            $this->paramName
          );
          $item->subitems[] = new \PapayaUiListviewSubitemText($group['boxgroup_name']);
          $item->subitems[] = new \PapayaUiListviewSubitemText('');
          $item->subitems[] = new \PapayaUiListviewSubitemText('');
          $item->subitems[] = new \PapayaUiListviewSubitemText('');
          if ($opened) {
            foreach ($boxes as $boxId => $box) {
              if (!isset($groupedBoxLinks[$groupId][$boxId])) {
                $listview->items[] = $item = new \PapayaUiListviewItem(
                  $this->getBoxStatusImage($box), $box['box_name']
                );
                $item->indentation = 2;
                if ($administrationUser->hasPerm(Permissions::BOX_MANAGE)) {
                  $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
                    'actions-edit',
                    new \PapayaUiStringTranslated('Edit box'),
                    array(
                      'bb' => array(
                        'cmd' => 'chg_show',
                        'bid' => $box['box_id'],
                        'p_mode' => 1
                      )
                    )
                  );
                  $subitem->reference(clone $listview->reference);
                  $subitem->reference()->setRelative('boxes.php');
                } else {
                  $item->columnSpan = 2;
                }
                $item->subitems[] = new \PapayaUiListviewSubitemText('');
                $item->subitems[] = new \PapayaUiListviewSubitemText('');
                $item->subitems[] = $subitem = new \PapayaUiListviewSubitemImage(
                  'actions-list-add',
                  new \PapayaUiStringTranslated('Remove'),
                  array(
                    $this->paramName => array(
                      'cmd' => 'add',
                      'box_id' => $boxId,
                      'page_id' => $this->topicId
                    )
                  )
                );
              }
            }
          }
        }
      }
    }
    return $listview->getXml();
  }

  public function getBoxStatusImage($box) {
    if ($pubDate = $box['box_published']) {
      $now = time();
      if ($pubDate >= $box['box_modified']) {
        if ($box['box_public_from'] < $now &&
            (
             $box['box_public_to'] == 0 ||
             $box['box_public_to'] == $box['box_public_from'] ||
             $box['box_public_to'] > $now
            )
           ) {
          if ($box['box_unpublished_languages'] > 0) {
            $imageIndex = 'status-box-published-partial';
          } else {
            $imageIndex = 'status-box-published';
          }
        } else {
          $imageIndex = 'status-box-published-hidden';
        }
      } elseif ($box['box_public_from'] < $now &&
                (
                 $box['box_public_to'] == 0 ||
                 $box['box_public_to'] == $box['box_public_from'] ||
                 $box['box_public_to'] > $now
                )) {
        $imageIndex = 'status-box-modified';
      } else {
        $imageIndex = 'status-box-modified-hidden';
      }
    } else {
      $imageIndex = 'status-box-created';
    }
    return $imageIndex;
  }

  /**
  * User input Processing
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'add':
        return $this->add();
      case 'del':
        return $this->delete();
      case 'group_link':
        return $this->linkGroup();
      case 'group_unlink':
        return $this->unlinkGroup();
      case 'up':
        return $this->move('up');
      case 'down':
        return $this->move('down');
      }
    }
    return NULL;
  }

  /**
  * Add a box into link table
  *
  * @access public
  */
  function add() {
    if ($this->params['box_id'] > 0 && $this->topicId > 0) {
      $sql = "SELECT MAX(box_sort)
                FROM %s
               WHERE topic_id = '%d'";
      $sort = 0;
      $params = array($this->tableLink, $this->topicId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          $sort = $row[0];
        }
      }
      ++$sort;
      $values = array(
        'box_id' => $this->params['box_id'],
        'topic_id' => $this->topicId,
        'box_sort' => $sort
      );
      if (FALSE !== $this->databaseInsertRecord($this->tableLink, 'boxlink_id', $values)) {
        $this->loadList();
        return TRUE;
      }
    }
    return NULL;
  }

  /**
  * Delete box from link table
  *
  * @access public
  */
  function delete() {
    if (isset($this->linkList[$this->params['boxlink_id']]) && $this->topicId > 0) {
      $link = $this->linkList[$this->params['boxlink_id']];
      if (
        FALSE !== $this->databaseDeleteRecord(
          $this->tableLink, 'boxlink_id', $link['boxlink_id']
        )
      ) {
        $sql = "UPDATE %s
                   SET box_sort = box_sort-1
                 WHERE topic_id = %d
                   AND (box_sort > %d)";
        $params = array($this->tableLink, $link['topic_id'], $link['box_sort']);
        $this->databaseQueryFmtWrite($sql, $params);
      }
      $this->loadList();
      return TRUE;
    }
    return NULL;
  }

  /**
  * link box group to page
  *
  * @access public
  */
  private function linkGroup() {
    if (empty($this->params['boxgroup_id']) ||
        empty($this->params['page_id']) ||
        $this->params['page_id'] != $this->groupPageId ||
        in_array($this->params['boxgroup_id'], $this->usedGroups)) {
      return NULL;
    }
    $data = array(
      'boxgroup_id' => $this->params['boxgroup_id'],
      'topic_id' => $this->params['page_id'],
    );
    if (FALSE !== $this->databaseInsertRecord($this->tableLink, 'boxlink_id', $data)) {
      $this->loadList();
    }
  }

  /**
  * link box group to page
  *
  * @access public
  */
  private function unlinkGroup() {
    if (empty($this->params['boxgroup_id']) ||
        empty($this->params['page_id']) ||
        $this->params['page_id'] != $this->groupPageId) {
      return NULL;
    }
    $filter = array(
      'boxgroup_id' => $this->params['boxgroup_id'],
      'topic_id' => $this->params['page_id'],
    );
    if (FALSE !== $this->databaseDeleteRecord($this->tableLink, $filter)) {
      $this->loadList();
    }
  }

  /**
  * Get  grouped ids
  *
  * @param integer $groupId
  * @access public
  * @return array|FALSE
  */
  function getGroupedIDs($groupId) {
    $result = array();
    if (isset($this->linkList) && is_array($this->linkList)) {
      foreach ($this->linkList as $id => $link) {
        if ($groupId == $this->boxesList[$link['box_id']]['boxgroup_id']) {
          $result[$id] = $link;
        }
      }
    }
    if (count($result) < 2) {
      unset($result);
      $result = FALSE;
    }
    return $result;
  }

  /**
  * Move
  *
  * @param string $dir optional, default value 'up'
  * @access public
  * @return boolean
  */
  function move($dir = 'up') {
    $result = NULL;
    $link = $this->linkList[$this->params['boxlink_id']];
    $groupId = $this->boxesList[$link['box_id']]['boxgroup_id'];
    $grouped = $this->getGroupedIDs($groupId);
    if (isset($grouped) && is_array($grouped)) {
      $prior = FALSE;
      $i = reset($grouped);
      while (isset($i) && ($i['boxlink_id'] != $link['boxlink_id'])) {
        $prior = $i;
        $i = next($grouped);
      }
      $next = next($grouped);
      switch ($dir) {
      case 'up':
        $result = $this->exchange($link, $prior);
        break;
      case 'down':
        $result = $this->exchange($link, $next);
        break;
      }
    }
    return $result;
  }

  /**
  * Exchange ( move box link from to )
  *
  * @param array &$src
  * @param array &$des
  * @access public
  * @return boolean
  */
  function exchange(&$src, &$des) {
    $result = FALSE;
    if ((isset($src) && is_array($src)) && (isset($des) && is_array($des))) {
      $updated = $this->databaseUpdateRecord(
        $this->tableLink,
        array('box_sort' => $des['box_sort']),
        'boxlink_id',
        $src['boxlink_id']
      );
      if (FALSE !== $updated) {
        $updated = $this->databaseUpdateRecord(
          $this->tableLink,
          array('box_sort' => $src['box_sort']),
          'boxlink_id',
          $des['boxlink_id']
        );
        if (FALSE !== $updated) {
          $result = TRUE;
        }
      }
      $this->loadList();
    }
    return $result;
  }
}

