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

use Papaya\Administration;

/**
* object to show sitemap in edit area (n-dimensional)
*
* @package Papaya
* @subpackage Administration
*/
class papaya_topic_tree extends base_topic_tree {
  /**
  * Papaya database table auth user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Papaya database table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topics public
  * @var string $tableTopicsPublic
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Papaya database table topics translations
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;
  /**
  * Papaya database table topics public translations
  * @var string $tableTopicsPublicTrans
  */
  var $tableTopicsPublicTrans = PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS;
  /**
  * Papaya database table media file links
  * @var string $tableMediaLinks
  */
  var $tableMediaLinks = PAPAYA_DB_TBL_MEDIA_LINKS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;

  /**
  * Temporary array for topics to copy
  * @var array $copyTopics
  * @access private
  */
  var $copyTopics = NULL;
  /**
  * Temporary array for page ids to copy
  * @var array $copyTopicsTree
  * @access private
  */
  var $copyTopicsTree = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var base_topic
   */
  public $topic;

  /**
   * @var array
   */
  private $copyTopicsTrans;

  /**
  * Dialog to confirm and specifiy the copy page action
  *
  * @var \Papaya\UI\Dialog
  */
  private $_dialogCopyPageConfirmation = NULL;

  /**
  * Helper object, that synchronizes page data to dependent pages.
  *
  * @var Administration\Pages\Dependency\Synchronizations
  */
  private $_synchronizations = NULL;

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  * @return string
  */
  function execute() {
    base_topic_tree::execute();
    $pageId = (isset($this->params['page_id']) && $this->params['page_id'] > 0) ?
      (int)$this->params['page_id'] : 0;
    $targetId = (isset($this->params['tgt']) && $this->params['tgt'] > 0) ?
      (int)$this->params['tgt'] : 0;
    $languageId = $this->papaya()->administrationLanguage->id;
    if (isset($this->params['cmd'])) {
      $result = '';
      switch ($this->params['cmd']) {
      case 'publish':
        if ($pageId > 0) {
          $topic = new papaya_topic;
          $topic->params = $this->params;
          if ($topic->load($pageId, $languageId)) {
            $topic->loadTranslationsInfo();
            $topic->initializePublishDialog();
            if (isset($this->params['publish_confirm']) &&
                $this->params['publish_confirm'] &&
                $topic->dialogPublish->execute()) {
              if ($topic->publishTopic()) {
                $this->addMsg(MSG_INFO, $this->_gt('Page published.'));
                $this->sychronizations()->synchronizeAction(
                  \Papaya\Content\Page\Dependency::SYNC_PUBLICATION,
                  $this->topicId,
                  array($languageId)
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Cannot publish this page.')
                );
              }
            } else {
              $topic->paramName = $this->paramName;
              $result .= $topic->getPublishForm();
            }
          } else {
            $this->addMsg(MSG_WARNING, $this->_gt('No page selected.'));
          }
        }
        break;
      case 'mv':
        $this->load($this->papaya()->administrationUser->startNode, $languageId);
        $errorString = $this->_gt('Cannot move this page.');
        $topic = (isset($this->topics[$pageId])) ?
          $this->topics[$pageId] : NULL;
        if (isset($topic) && is_array($topic)) {
          if (
               $this->topicEditable($pageId) &&
               ($this->topicEditable($targetId) || $targetId == 0)  &&
               $this->papaya()->administrationUser->hasPerm(
                 Administration\Permissions::PAGE_MOVE
               )
              ) {
            $this->movePage($topic, $targetId, $errorString);
          } else {
            $this->addMsg(
              MSG_WARNING,
              $errorString.' '.$this->_gt('Permission error!')
            );
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $errorString.' '.$this->_gt('Invalid page id!')
          );
        }
        break;
      case 'cp':
        $this->load($this->papaya()->administrationUser->startNode, $languageId);
        $errorString = $this->_gt('Cannot copy this page.');
        $topic = (isset($this->topics[$pageId])) ?
          $this->topics[$pageId] : NULL;

        if (isset($topic) && is_array($topic)) {
          if (
               ($this->topicEditable($targetId) || $targetId == 0) &&
               $this->papaya()->administrationUser->hasPerm(
                 Administration\Permissions::PAGE_COPY
               )
             ) {
            if (
              !$this->papaya()->administrationUser->hasPerm(
                Administration\Permissions::PAGE_DEPENDENCY_MANAGE
              ) ||
              $this->dialogCopyPageConfirmation()->execute()
            ) {
              $this->copyPage($topic, $targetId, $errorString);
              $this->papaya()->getObject('Surfer')->loadTopicIdList(FALSE, TRUE);
            } else {
              $this->layout->add($this->dialogCopyPageConfirmation()->getXML());
            }
          } else {
            $this->addMsg(
              MSG_WARNING,
              $errorString.' '.$this->_gt('Permission error!')
            );
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $errorString.' '.$this->_gt('Invalid page id!')
          );
        }
        break;
      }
      $this->layout->add($result);
    }
  }

  /**
  * If the user is allowed to manage dependencies, a confirmation dialog is needed to
  * specify the dependency creation for copied pages.
  *
  * @param \Papaya\UI\Dialog $dialog
  * @return \Papaya\UI\Dialog
  */
  function dialogCopyPageConfirmation(\Papaya\UI\Dialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialogCopyPageConfirmation = $dialog;
    } elseif (is_null($this->_dialogCopyPageConfirmation)) {
      $this->_dialogCopyPageConfirmation = $dialog = new \Papaya\UI\Dialog();
      $dialog->parameterGroup($this->paramName);
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => 'cp',
          'page_id' => empty($this->params['page_id']) ? 0 : $this->params['page_id'],
          'tgt' => empty($this->params['tgt']) ? 0 : $this->params['tgt'],
        )
      );
      $dialog->caption = new \Papaya\UI\Text\Translated('Copy pages');
      $dialog->fields[] = new \Papaya\UI\Dialog\Field\Information(
        new \Papaya\UI\Text\Translated(
          'Copy pages and create dependencies if necessary.'
        ),
        'actions-edit-copy'
      );
      $dialog->fields[] = new \Papaya\UI\Dialog\Field\Select\Radio(
        new \Papaya\UI\Text\Translated('Create dependencies'),
        'confirm_create_dependencies',
        array(
          1 => new \Papaya\UI\Text\Translated('Yes'),
          0 => new \Papaya\UI\Text\Translated('No')
        )
      );
      $dialog->fields[] = new \Papaya\UI\Dialog\Field\Select\Bitmask(
        new \Papaya\UI\Text\Translated('Synchronization'),
        'synchronization',
        $this->sychronizations()->getList()
      );
      $dialog->buttons[] = new \Papaya\UI\Dialog\Button\Submit(new \Papaya\UI\Text\Translated('Copy'));
    }
    return $this->_dialogCopyPageConfirmation;
  }

  /**
  * Move page
  *
  * @param array $topic
  * @param integer $targetId
  * @param string $errorString
  * @access public
  */
  function movePage($topic, $targetId, $errorString) {
    if ($targetId < 1) {
      $target = array(
        "topic_id" => 0,
        "prev_path" => '',
        "prev" => ';',
        "topic_title" => '**root**',
        "ALLPREV" => array(0 => 0));
    } else {
      $target = $this->topics[$this->params["tgt"]];
    }
    if (isset($target['ALLPREV']) && is_array($target['ALLPREV'])) {
      $family = array_merge($target['ALLPREV'], array(0));
    } else {
      $family = array(0);
    }
    if (!(
          $target['topic_id'] != $topic['topic_id'] &&
          !in_array($topic['topic_id'], $family) &&
          $target['topic_id'] != $topic['prev']
        )) {
      $this->addMsg(MSG_WARNING, $errorString.' '.$this->_gt('Input error!'));
    } else {
      $newPath = $target["prev_path"].$target["prev"].";";
      $newPath = str_replace(';;', ';', $newPath);
      $values = array(
        'prev' => $target["topic_id"],
        'prev_path' => $newPath,
        'topic_weight' => 999999
      );
      if (
        FALSE !== $this->databaseUpdateRecord(
          $this->tableTopics, $values, 'topic_id', $topic["topic_id"]
        ) &&
        FALSE !== $this->databaseUpdateRecord(
          $this->tableTopicsPublic, $values, 'topic_id', $topic["topic_id"]
        )
      ) {
        $this->addMsg(MSG_INFO, $this->_gt('Page moved!'));
        $oldPath = $topic["prev_path"].$topic["prev"].";";
        $strLength = strlen($oldPath) + 1;
        $newPath = $target["prev_path"].$target["prev"].";".$target["topic_id"].";";
        $newPath = str_replace(';;', ';', $newPath);
        //change all subnode paths (n+x)|x>0
        $sqlReplace = $this->databaseGetSQLSource(
          'CONCAT',
          $newPath,
          TRUE,
          $this->databaseGetSQLSource(
            'SUBSTRING', 'prev_path', FALSE, $strLength, TRUE
          ),
          FALSE
        );
        $sql = "UPDATE %s
                   SET prev_path = ".$sqlReplace."
                 WHERE prev = '%d' OR prev_path LIKE '%s%d;%%'";
        $params = array($this->tableTopics, $topic["topic_id"],
          $oldPath, $topic["topic_id"]);
        $paramsPub = array($this->tableTopicsPublic, $topic["topic_id"],
          $oldPath, $topic["topic_id"]);
        if (!(
              FALSE !== $this->databaseQueryFmtWrite($sql, $params) &&
              FALSE !== $this->databaseQueryFmtWrite($sql, $paramsPub)
            )) {
          $this->addMsg(MSG_WARNING, $this->_gt('Could not move subpages!'));
        } else {
          if (!isset($this->opened[$target['topic_id']])) {
            $this->sessionParams = $this->getSessionValue($this->sessionParamName);
            $this->opened[$target['topic_id']] = TRUE;
            $this->sessionParams['opened'] = $this->opened;
            $this->setSessionValue($this->sessionParamName, $this->sessionParams);
          }
          $this->load(
            $this->papaya()->administrationUser->startNode,
            $this->papaya()->administrationLanguage->id
          );
        }
      } else {
        $this->addMsg(
          MSG_WARNING,
          $errorString.' '.$this->_gt('Database error!')
        );
      }
    }
  }

  /**
  * Copy page
  *
  * @param array $topic
  * @param integer $targetId
  * @param string $errorString
  * @access public
  */
  function copyPage($topic, $targetId, $errorString) {
    if ($targetId < 1) {
      $target = array(
        "topic_id" => 0,
        "prev_path" => '',
        "prev" => '',
        "ALLPREV" => array(0 => 0)
      );
    } else {
      $target = $this->topics[$this->params['tgt']];
    }
    if ($target['topic_id'] == $topic['topic_id'] ||
        $topic['topic_id'] == $target['prev'] ||
        (
          isset($target['ALLPREV']) &&
          is_array($target['ALLPREV']) &&
          in_array($topic['topic_id'], $target['ALLPREV'])
        )) {
      $this->addMsg(MSG_INFO, $errorString.' '.$this->_gt('Input error!'));
    } else {
      unset($this->copyTopics);
      unset($this->copyTopicsTree);
      $sql = "SELECT *
                FROM %s
               WHERE topic_id = '%d' OR prev = '%d'";
      $params = array(
        $this->tableTopics,
        $topic['topic_id'],
        $topic['topic_id']
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $row['topic_created'] = time();
          $row['topic_modified'] = time();
          $row['author_id'] = $this->papaya()->administrationUser->userId;
          $this->copyTopics[(int)$row['topic_id']] = $row;
          $this->copyTopicsTree[(int)$row['prev']][] = $row['topic_id'];
        }
        $sql = "SELECT *
                  FROM %s
                 WHERE prev_path LIKE '%s%d;%d;%%'
                 ORDER BY prev_path ASC";
        $params = array($this->tableTopics, $topic['prev_path'],
          $topic['prev'], $topic['topic_id']);
        if ($res = $this->databaseQueryFmt($sql, $params, 200)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $row['topic_created'] = time();
            $row['topic_modified'] = time();
            $row['author_id'] = $this->papaya()->administrationUser->userId;
            $this->copyTopics[(int)$row['topic_id']] = $row;
            $this->copyTopicsTree[(int)$row['prev']][] = $row['topic_id'];
          }
        }
      }
      if (isset($this->copyTopics) && is_array($this->copyTopics)) {
        if (isset($this->copyTopics[$topic['topic_id']])) {
          $this->copyTopics[$topic['topic_weight']] = 999999;
        }
        $this->copyTopicsTrans = array();
        $filter = $this->databaseGetSQLCondition(
          'topic_id', array_keys($this->copyTopics)
        );
        $sql = "SELECT *
                  FROM %s
                 WHERE $filter";
        if ($res = $this->databaseQueryFmt($sql, $this->tableTopicsTrans)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this->copyTopicsTrans[(int)$row['topic_id']][(int)$row['lng_id']] = $row;
          }
        }
        $current = &$this->copyTopics[$topic['topic_id']];
        $oldId = $topic['topic_id'];
        $current['prev'] = $target['topic_id'];
        $ancestors = \Papaya\Utility\Arrays::decodeIdList($target['prev_path']);
        $ancestors[] = $target['prev'];
        $current['prev_path'] = \Papaya\Utility\Arrays::encodeAndQuoteIdList($ancestors);
        unset($current['topic_id']);
        if ($newId = $this->databaseInsertRecord($this->tableTopics, 'topic_id', $current)) {
          $newPath = $current['prev_path'].$current['prev'].';';
          if ($success = $this->copyPageTranslations($topic['topic_id'], $newId, $newPath)) {
            $this->copyPageMediaLinks($oldId, $newId);
            $this->copyOrCreatePageDependency($oldId, $newId);
            if (isset($this->copyTopicsTree[$topic['topic_id']]) &&
                is_array($this->copyTopicsTree[$topic['topic_id']])) {
              $success = $this->copySubPages(
                $this->copyTopicsTree[$topic['topic_id']], $newId, $newPath
              );
            }

          }
        } else {
          $success = FALSE;
        }
        if ($success) {
          $this->addMsg(MSG_INFO, $this->_gt('Page copied!'));
        } else {
          $this->addMsg(MSG_ERROR, $errorString.' '.$this->_gt('Database error!'));
        }
      }
    }
  }

  /**
  * Copy page translations
  *
  * @param integer $oldId
  * @param integer $newId
  * @access public
  * @return boolean
  */
  function copyPageTranslations($oldId, $newId) {
    if (isset($this->copyTopicsTrans[$oldId]) &&
        is_array($this->copyTopicsTrans[$oldId])) {
      $translations = $this->copyTopicsTrans[$oldId];
      foreach ($translations as $id => $translation) {
        $translations[$id]['topic_id'] = $newId;
        $translations[$id]['topic_title'] = $this->_gt('Copy of').' '.
          $translation['topic_title'];
      }
      return (FALSE !== $this->databaseInsertRecords($this->tableTopicsTrans, $translations));
    }
    return TRUE;
  }

  /**
  * copy media links (for imported pages)
  *
  * @param integer $oldId
  * @param integer $newId
  * @return boolean
  */
  function copyPageMediaLinks($oldId, $newId) {
    $sql = "SELECT page_id, language_id, file_id
              FROM %s
             WHERE page_id = %d";
    $links = array();
    if ($res = $this->databaseQueryFmt($sql, array($this->tableMediaLinks, $oldId))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['page_id'] = $newId;
        $links[] = $row;
      }
      if (isset($links) && is_array($links) && count($links) > 0) {
        return (FALSE !== $this->databaseInsertRecords($this->tableMediaLinks, $links));
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Copy or create the page dependency
  *
  * @param integer $oldId
  * @param integer $newId
  * @return boolean
  */
  function copyOrCreatePageDependency($oldId, $newId) {
    $createMissing = FALSE;
    $synchronization = 0;
    if (
         $this->papaya()->administrationUser->hasPerm(
           Administration\Permissions::PAGE_DEPENDENCY_MANAGE
         )
       ) {
      $createMissing = $this->dialogCopyPageConfirmation()->data->get(
        'confirm_create_dependencies', $createMissing
      );
      $synchronization = $this->dialogCopyPageConfirmation()->data->get(
        'synchronization', $synchronization
      );
    }
    // load the current dependency if available
    $old = new \Papaya\Content\Page\Dependency();
    if ($old->load($oldId)) {
      // if available change clone id and save
      $new = new \Papaya\Content\Page\Dependency();
      $new->assign($old->toArray());
      $new->id = $newId;
      return $new->save();
    } elseif ($createMissing) {
      // if not available and create allowed define new dependency and save
      $new = new \Papaya\Content\Page\Dependency();
      $new->id = $newId;
      $new->originId = $oldId;
      $new->synchronization = $synchronization;
      $new->note = '';
      return $new->save();
    } else {
      return TRUE;
    }
  }

  /**
  * Copy sub pages
  *
  * @param array $ids
  * @param integer $prev
  * @param string $prevPath
  * @access public
  * @return boolean $success
  */
  function copySubPages($ids, $prev, $prevPath) {
    $success = TRUE;
    if (isset($ids) && is_array($ids)) {
      foreach ($ids as $id) {
        if (isset($this->copyTopics[$id]) && is_array($this->copyTopics[$id])) {
          $current = &$this->copyTopics[$id];
          $current['prev'] = $prev;
          $current['prev_path'] = $prevPath;
          unset($current['topic_id']);
          if ($newId = $this->databaseInsertRecord($this->tableTopics, 'topic_id', $current)) {
            $newPath = $current['prev_path'].$current['prev'].';';
            if ($success = $this->copyPageTranslations($id, $newId, $newPath)) {
              $this->copyPageMediaLinks($id, $newId);
              $this->copyOrCreatePageDependency($id, $newId);
              if (isset($this->copyTopicsTree[$id]) &&
                  is_array($this->copyTopicsTree[$id])) {
                $success = $this->copySubPages($this->copyTopicsTree[$id], $newId, $newPath);
              }
            }
          } else {
            $success = FALSE;
          }
          if (!$success) {
            return FALSE;
          }
        }
      }
    }
    return $success;
  }

  /**
  * init partial tree for navigation
  *
  * @param base_topic $topic
  * @return array
  */
  function initPartTree($topic) {
    $this->topicId = $topic->topicId;
    $this->topic = $topic;
    $this->opened[$topic->topic['prev']] = TRUE;
    $this->opened[$this->topicId] = TRUE;
    $base = 0;
    if (preg_match_all('/\d+/', $topic->topic['prev_path'], $regs, PREG_PATTERN_ORDER)) {
      $base = (int)end($regs[0]);
    }
    $this->load($base, $this->papaya()->administrationLanguage->id);
    $nodes = array();
    if (isset($this->topicLinks) && is_array($this->topicLinks)) {
      $nodes = $this->topicLinks[$base]['children'];
    }
    if (isset($nodes) && !is_array($nodes)) {
      $nodes = $this->rootTopics;
    }
    return array($base, $nodes);
  }

  /**
  * Get part tree
  *
  * @param object base_topic $topic
  * @access public
  * @return string
  */
  function getPartTree($topic) {
    $result = '';
    list($base, $nodes) = $this->initPartTree($topic);
    if (isset($this->topics) && is_array($this->topics)) {
      $result .= sprintf(
        '<listview title="%s"><items>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Pages'))
      );
      if ($base > 0) {
        $result .= sprintf(
          '<listitem title="%s" image="%s" href="%s" span="3"></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Parent page')),
          papaya_strings::escapeHTMLChars($this->papaya()->images['actions-go-superior']),
          $this->getLink(array('page_id' => $base))
        );
        $indent = 1;
      } else {
        $indent = 0;
      }
      $result .= $this->getStaticSubTree($nodes, $indent);
      $result .= '</items></listview>'."\r\n";
    }
    return $result;
  }

  /**
  * Get stratic sub tree
  *
  * @param array $nodes
  * @param integer $indent optional, default value 0
  * @access public
  * @return string
  */
  function getStaticSubTree($nodes, $indent=0) {
    $result = '';
    if (isset($nodes) && is_array($nodes)) {
      $images = $this->papaya()->images;
      $counter = 0;
      $maxCounter = count($nodes);
      foreach ($nodes as $id) {
        $val = $this->topics[$id];
        $imageIdx = $this->getTopicImage($val['topic_status']);
        if ($id == $this->topicId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        if (isset($val['topic_title']) && trim($val['topic_title']) != '') {
          $title = papaya_strings::escapeHTMLChars($val['topic_title']);
        } elseif (isset($val['mlang_topic_title']) && trim($val['mlang_topic_title']) != '') {
          $title = papaya_strings::escapeHTMLChars('['.$val['mlang_topic_title'].']');
        } else {
          $title = papaya_strings::escapeHTMLChars($this->_gt('No title'));
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" indent="%d" image="%s"%s>',
          papaya_strings::escapeHTMLChars($title),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('page_id' => $id))
          ),
          (int)$indent,
          papaya_strings::escapeHTMLChars(
            $images[$imageIdx]
          ),
          $selected
        );
        if (++$counter > 1) {
          $result .= sprintf(
            '<subitem><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'move_position',
                  'direction' => '-1',
                  'page_id' => $id
                )
              )
            ),
            papaya_strings::escapeHTMLChars($images['actions-go-up']),
            papaya_strings::escapeHTMLChars($this->_gt('Move up'))
          );
        } else {
          $result .= '<subitem/>';
        }
        if ($counter < $maxCounter) {
          $result .= sprintf(
            '<subitem><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
            $this->getLink(
              array(
                'cmd' => 'move_position',
                'direction' => '+1',
                'page_id' => $id
              )
            ),
            papaya_strings::escapeHTMLChars($images['actions-go-down']),
            papaya_strings::escapeHTMLChars($this->_gt('Move down'))
          );
        } else {
          $result .= '<subitem/>';
        }
        $result .= '</listitem>';
        if (isset($this->topicLinks[$id]['children']) &&
            is_array($this->topicLinks[$id]['children']) &&
            count($this->topicLinks[$id]['children']) > 0) {
          $result .= $this->getStaticSubTree(
            $this->topicLinks[$id]['children'],
            $indent + 1
          );
        }
      }
    }
    return $result;
  }

  /**
  * Map topic/page status to an image index
  *
  * @param integer $status
  * @return string
  */
  function getTopicImage($status) {
    switch ($status) {
    case 7:
      $imageIdx = 'status-page-modified-hidden';
      break; // published, blocked, modified
    case 6:
      $imageIdx = 'status-page-published-hidden';
      break; // partial published (not all languages)
    case 5:
      $imageIdx = 'status-page-published-partial';
      break; // published and blocked
    case 4:
      $imageIdx = 'status-page-deleted';
      break; // deleted
    case 3:
      $imageIdx = 'status-page-modified';
      break; // published and modified
    case 2:
      $imageIdx = 'status-page-published';
      break; // published and up to date
    default:
      $imageIdx = 'status-page-created';
      break; // created - no public version
    }
    return $imageIdx;
  }

  /**
  * Get
  *
  * @access public
  * @return string
  */
  function get() {
    $this->load(
      $this->papaya()->administrationUser->startNode,
      $this->papaya()->administrationLanguage->id
    );
    $images = $this->papaya()->images;
    $result = '';
    if (isset($this->topics) && is_array($this->topics)) {
      $result .= sprintf(
        '<listview title="%s" width="100%%">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Sitemap'))
      );
      $result .= '<cols>'.LF;
      $result .= '<col/>'.LF;
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Modified'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Publish'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Copy'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Move'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Edit'))
      );
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      $result .= sprintf(
        '<listitem title="%s" image="%s">'.LF,
        papaya_strings::escapeHTMLChars('Page root'),
        papaya_strings::escapeHTMLChars($images['places-desktop'])
      );
      $result .= '<subitem/>';
      $result .= '<subitem/>';
      if ($this->topicId > 0) {
        $cpLink = $this->getLink(
          array(
            'cmd' => 'cp',
            'page_id' => (int)$this->topicId,
            'tgt' => 0
          )
        );
        $cpHint = sprintf($this->_gt('Copy page to %s (%s)'), $this->_gt('Page root'), 0);
        $result .= '<subitem align="center">';
        $result .= sprintf(
          '<a href="%s"><glyph src="%s" hint="%s"/></a>',
          papaya_strings::escapeHTMLChars($cpLink),
          papaya_strings::escapeHTMLChars($images['actions-edit-copy']),
          papaya_strings::escapeHTMLChars($cpHint)
        );
        $result .= '</subitem>';
        if (isset($this->topics[$this->topicId]["prev"]) &&
            $this->topics[$this->topicId]["prev"] > 0) {
          $mvLink = $this->getLink(
            array(
              'cmd' => 'mv',
              'page_id' => (int)$this->topicId,
              'tgt' => 0
            )
          );
          $mvHint = sprintf($this->_gt('Move page to %s (%s)'), $this->_gt('Page root'), 0);
          $result .= '<subitem align="center">';
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" hint="%s"/></a>',
            papaya_strings::escapeHTMLChars($mvLink),
            papaya_strings::escapeHTMLChars($images['actions-page-move']),
            papaya_strings::escapeHTMLChars($mvHint)
          );
          $result .= '</subitem>';
        } else {
          $result .= '<subitem/>';
        }
      } else {
        $result .= '<subitem/>';
        $result .= '<subitem/>';
      }
      $result .= '<subitem/>';
      $result .= '</listitem>';

      $counter = 0;
      $startNode = $this->papaya()->administrationUser->startNode;
      if (isset($this->topicLinks[$startNode])) {
        $result .= $this->getSubTree(
          $this->topicLinks[$startNode]['children'],
          $counter
        );
      }
      $result .= '</items></listview>'."\r\n";
    }
    $this->layout->add($result);

    $topic = new papaya_topic;
    if ($topic->load($this->params['page_id'], $this->papaya()->administrationLanguage->id)) {
      $this->layout->addRight($topic->getTopicInformation());
    }
  }

  /**
  * Get sub tree
  *
  * @param array $idArr
  * @param integer &$counter
  * @access public
  * @return string
  */
  function getSubTree($idArr, &$counter) {
    $result = '';
    if (isset($idArr) && is_array($idArr)) {
      $images = $this->papaya()->images;
      foreach ($idArr as $id) {
        $val = $this->topics[$id];
        if (isset($val) && is_array($val)) {
          $counter++;
          if (isset($val['ALLPREV']) && is_array($val['ALLPREV'])) {
            $indent = count($val['ALLPREV']);
          } else {
            $indent = 1;
          }
          if (isset($this->subtopicCount[$id])) {
            if (isset($this->opened[$id])) {
              $nodeHref = sprintf(
                '%s#node%s',
                $this->getLink(
                  array('cmd' => 'close', 'page_id' => (int)$id)
                ),
                (int)$id
              );
              $node = sprintf(' node="open" nhref="%s"', $nodeHref);
            } else {
              $nodeHref = sprintf(
                '%s#node%s',
                $this->getLink(array('cmd' => 'open', 'page_id' => (int)$id)),
                (int)$id
              );
              $node = sprintf(' node="close" nhref="%s"', $nodeHref);
            }
          } else {
            $node = ' node="empty"';
          }
          $imageIdx = $this->getTopicImage($val['topic_status']);
          $showPublish = !in_array($val['topic_status'], array(2, 4));
          if ($id == $this->topicId) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem title="%s (%d)" href="%s#node%d" image="%s" indent="%d" %s%s>'.LF,
            papaya_strings::escapeHTMLChars($val['topic_title']),
            (int)$val['topic_id'],
            papaya_strings::escapeHTMLChars($this->getLink(array('page_id' => $id))),
            papaya_strings::escapeHTMLChars($id),
            papaya_strings::escapeHTMLChars($images[$imageIdx]),
            (int)$indent,
            $node,
            $selected
          );
          $fullPath = $val['prev_path'].';'.$val['prev'].';'.$val['topic_id'];
          if (preg_match_all('/\d+/', $fullPath, $match, PREG_PATTERN_ORDER)) {
            $family = $match[0];
          } else {
            $family = FALSE;
          }
          $mvRadio = FALSE;
          $cpRadio = FALSE;
          if (!(is_array($family) && in_array($this->topicId, $family))) {
            $cpRadio = TRUE;
            $previous = empty($this->topics[$this->topicId]["prev"])
              ? 0 : (int)$this->topics[$this->topicId]['prev'];
            if ($val['topic_id'] != $previous && $this->topicId != $val['prev']) {
              $mvRadio = TRUE;
            }
          }
          $result .= sprintf(
            '<subitem align="center">%s</subitem>',
            date('Y-m-d H:i:s', $val['topic_modified'])
          );
          if ($showPublish &&
              $this->papaya()->administrationUser->hasPerm(
                Administration\Permissions::PAGE_PUBLISH
              ) &&
              $this->topicEditable($val['topic_id'])) {
            $publishLink = $this->getLink(array('cmd' => 'publish', 'page_id' => (int)$id));
            $publishHint = sprintf($this->_gt('Publish page %s (%s)'), $val['topic_title'], $id);
            $result .= '<subitem align="center">';
            $result .= sprintf(
              '<a href="%s"><glyph src="%s" hint="%s"/></a>',
              papaya_strings::escapeHTMLChars($publishLink),
              papaya_strings::escapeHTMLChars($images['items-publication']),
              papaya_strings::escapeHTMLChars($publishHint)
            );
            $result .= '</subitem>';
          } else {
            $result .= '<subitem/>';
          }
          if ($this->topicId > 0) {
            if ($cpRadio) {
              $cpLink = $this->getLink(
                array(
                  'cmd' => 'cp',
                  'page_id' => (int)$this->topicId,
                  'tgt' => (int)$id
                )
              );
              $cpHint = sprintf($this->_gt('Copy page to %s (%s)'), $val['topic_title'], $id);
              $result .= '<subitem align="center">';
              $result .= sprintf(
                '<a href="%s"><glyph src="%s" hint="%s"/></a>',
                papaya_strings::escapeHTMLChars($cpLink),
                papaya_strings::escapeHTMLChars($images['actions-edit-copy']),
                papaya_strings::escapeHTMLChars($cpHint)
              );
              $result .= '</subitem>';
            } else {
              $result .= '<subitem/>';
            }
            if ($mvRadio) {
              $mvLink = $this->getLink(
                array(
                  'cmd' => 'mv',
                  'page_id' => (int)$this->topicId,
                  'tgt' => (int)$id
                )
              );
              $mvHint = sprintf($this->_gt('Move page to %s (%s)'), $val['topic_title'], $id);
              $result .= '<subitem align="center">';
              $result .= sprintf(
                '<a href="%s"><glyph src="%s" hint="%s"/></a>',
                papaya_strings::escapeHTMLChars($mvLink),
                papaya_strings::escapeHTMLChars($images['actions-page-move']),
                papaya_strings::escapeHTMLChars($mvHint)
              );
              $result .= '</subitem>';
            } else {
              $result .= '<subitem/>';
            }
          } else {
            $result .= '<subitem/>';
            $result .= '<subitem/>';
          }
          $result .= '<subitem align="center">';
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" hint="%s"/></a>',
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('page_id' => (int)$id), NULL, 'topic.php')
            ),
            papaya_strings::escapeHTMLChars($images['actions-edit']),
            papaya_strings::escapeHTMLChars($this->_gt('Edit page'))
          );
          $result .= '</subitem>';
          $result .= '</listitem>'.LF;
          if (isset($this->topicLinks[$id]) &&
              is_array($this->topicLinks[$id])) {
            $result .= $this->getSubTree($this->topicLinks[$id]['children'], $counter);
          }
        }
      }
    }
    return $result;
  }

  /**
  * Getter/Setter for the synchronizations object
  *
  * @param Administration\Pages\Dependency\Synchronizations $synchronizations
  * @return Administration\Pages\Dependency\Synchronizations
  */
  public function sychronizations(
    Administration\Pages\Dependency\Synchronizations $synchronizations = NULL
  ) {
    if (isset($synchronizations)) {
      $this->_synchronizations = $synchronizations;
    } elseif (is_null($this->_synchronizations)) {
      $this->_synchronizations = new Administration\Pages\Dependency\Synchronizations();
    }
    return $this->_synchronizations;
  }
}
