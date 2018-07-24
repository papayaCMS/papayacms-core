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
* page administration class
*
* @package Papaya
* @subpackage Core
*/
class base_topic_edit extends base_topic {

  /**
   * @var PapayaTemplate
   */
  public $layout;

  /**
   * @var array
   */
  public $languages = array();

  /**
   * @var array
   */
  public $versions = array();

  /**
   * @var base_dialog
   */
  public $dialogHandoff = NULL;

  /**
   * @var base_dialog
   */
  public $dialogAddTranslation = NULL;

  /**
   * @var base_dialog
   */
  private $dialogProperties = NULL;
  
  /**
   * @var base_dialog
   */
  private $dialogSocialMedia = NULL;

  /**
   * @var papaya_todo
   */
  private $_tasks = NULL;

  /**
  * informational message about views, set in initialize()
  * @var string $infoSheetViews
  */
  var $infoSheetViews = '';

  /**
  * Surfer permissions table name
  * @var string $tableSurferPerm
  */
  var $tableSurferPerm = PAPAYA_DB_TBL_SURFERPERM;

  /**
   * @var base_btnbuilder $menubar
   */
  public $menubar = NULL;

  /**
   * @var string $lockingIdent
   */
  public $lockingIdent = '';

  /**
   * @var integer $versionCount
   */
  public $versionsCount = NULL;

  /**
   * @var NULL|array $savedVersion
   */
  public $savedVersion = NULL;

  /**
  * Helper object, that synchronizes page data to dependent pages.
  *
  * @var Administration\Pages\Dependency\Synchronizations
  */
  private $_synchronizations = NULL;

  /**
   * @var \PapayaUiDialog
   */
  public $dialogPublish = NULL;

  /**
   * @var papaya_locking
   */
  private $lockingObj;

  /**
  * Initialize for parameters
  *
  * @param mixed $id optional, default value NULL
  * @access public
  */
  function initialize($id = NULL) {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    if (isset($this->params['page_id'])) {
      $this->sessionParams['page_id'] = (int)$this->params['page_id'];
    } elseif (isset($id) && ($id > 0)) {
      $this->params['page_id'] = (int)$id;
      $this->sessionParams['page_id'] = (int)$this->params['page_id'];
    } elseif (isset($this->sessionParams['page_id'])) {
      $this->params['page_id'] = (int)$this->sessionParams['page_id'];
    } else {
      $this->params['page_id'] = 0;
    }
    $this->initializeSessionParam('mode');
    if (isset($this->params['open_mod']) &&
        preg_match('/[a-z\d]{32}/', $this->params['open_mod'])) {
      $this->sessionParams['openmods'][$this->params['open_mod']] = TRUE;
    } elseif (isset($this->params['close_mod']) &&
              preg_match('/[a-z\d]{32}/', $this->params['close_mod'])) {
      unset($this->sessionParams['openmods'][$this->params['close_mod']]);
    }
    $this->initializeSessionParam('version_datetime');
    if (!empty($this->params['version_date']) && !empty($this->params['version_time'])) {
      $versionDateTime = PapayaUtilDate::stringToTimestamp(
        $this->params['version_date'].' '.$this->params['version_time']
      );
      $this->params['version_datetime'] = $versionDateTime;
      $this->sessionParams['version_datetime'] = $versionDateTime;
    }
    $this->initializeSessionParam('viewmode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Basic executions
  *
  * @access public
  */
  function execute() {
    $this->load($this->params['page_id'], $this->papaya()->administrationLanguage->id);
    $this->currentLanguage = $this->loadCurrentLanguage(
      $this->papaya()->administrationLanguage->id
    );

    $this->layout->parameters()->set('COLUMNWIDTH_LEFT', '250px');

    $this->lockingObj = papaya_locking::getInstance();
    $this->lockingIdent = $this->lockingObj->getLockIdent(
      $this->params['mode'], $this->params['page_id']
    );
    $locked = $this->lockingObj->setLock(
      $this->papaya()->administrationUser->userId, 1, $this->lockingIdent
    );
    if (!$locked) {
      $userName = $this->lockingObj->getLockUser(1, $this->lockingIdent);
      $msg = sprintf(
        $this->_gt('%s is currently editing this page.'),
        $userName
      );
      $this->addMsg(MSG_INFO, $msg);
    }

    $this->menubar = new base_btnbuilder;
    $this->menubar->images = $this->papaya()->images;
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    $authUser = $this->papaya()->administrationUser;
    switch ($this->params['cmd']) {
    case 'move_position' :
      if ($this->hasPermUser(PERM_WRITE, $authUser) &&
          $this->editable($authUser)) {
        if (preg_match('(^\d+$)D', $this->topic['topic_weight']) &&
            $this->topic['topic_weight'] > 100000 &&
            !empty($this->params['direction']) &&
            (int)$this->params['direction'] != 0) {
          $currentWeight = $this->topic['topic_weight'];
          $direction = (int)$this->params['direction'];
          $this->changePosition($currentWeight, $direction);
          if ($this->papaya()->options->get('PAPAYA_LOG_EVENT_PAGE_MOVED', TRUE)) {
            $this->papaya()->messages->dispatch(
              new PapayaMessageLog(
                PapayaMessageLogable::GROUP_CONTENT,
                PapayaMessage::SEVERITY_INFO,
                sprintf(
                  'Page "%s (%s)" moved position (%d %s).',
                  $this->topic['TRANSLATION']['topic_title'],
                  (int)$this->topicId,
                  abs($this->params['direction']),
                  ($this->params['direction'] > 0) ? 'up' : 'down'
                )
              )
            );
          }
        }
      }
      break;
    case 'move_page_dialog' :
      if ($this->hasPermUser(PERM_WRITE, $authUser) && $this->editable($authUser)) {
        if (isset($this->params['confirm_move']) && $this->params['confirm_move']) {
          $moveSteps = 0;
          if (isset($this->params['move_pos_first'])) {
            $moveSteps = ($this->topic['topic_weight'] - 100000) * -1;
          } elseif (isset($this->params['move_pos_10up'])) {
            $moveSteps = -10;
          } elseif (isset($this->params['move_pos_5up'])) {
            $moveSteps = -5;
          } elseif (isset($this->params['move_pos_5down'])) {
            $moveSteps = 5;
          } elseif (isset($this->params['move_pos_10down'])) {
            $moveSteps = 10;
          } elseif (isset($this->params['move_pos_last'])) {
            $count = $this->getSubPageCount($this->topic['prev']);
            $moveSteps = $count - ($this->topic['topic_weight'] - 100000);
          } elseif (!empty($this->params['steps']) &&
                    (int)$this->params['steps'] != 0) {
            if (isset($this->params['direction']) &&
                $this->params['direction'] == 1) {
              $moveSteps = (int)$this->params['steps'] * -1;
            } else {
              $moveSteps = (int)$this->params['steps'];
            }
          }
          if (
            preg_match('(^\d+$)D', $this->topic['topic_weight']) &&
            $this->topic['topic_weight'] > 100000 &&
            $moveSteps != 0
          ) {
            $this->changePosition($this->topic['topic_weight'], $moveSteps);
            if ($this->papaya()->options->get('PAPAYA_LOG_EVENT_PAGE_MOVED', TRUE)) {
              $this->papaya()->messages->dispatch(
                new PapayaMessageLog(
                  PapayaMessageLogable::GROUP_CONTENT,
                  PapayaMessage::SEVERITY_INFO,
                  sprintf(
                    'Page "%s (%s)" moved position (%d %s).',
                    $this->topic['TRANSLATION']['topic_title'],
                    (int)$this->topicId,
                    abs($moveSteps),
                    ($moveSteps > 0) ? 'up' : 'down'
                  )
                )
              );
            }
            $this->load($this->params['page_id'], $this->papaya()->administrationLanguage->id);
          }
        }
        $this->layout->add($this->getMovePageDialog());
      }
      break;
    case 'add_translation':
      if ($this->hasPermUser(PERM_WRITE, $authUser) &&
          $this->editable($authUser)) {
        if (
          isset($this->params['lng_id']) && $this->params['lng_id'] > 0 &&
          $this->papaya()->administrationLanguage->id == $this->params['lng_id'] &&
          isset($this->papaya()->languages[(int)$this->params['lng_id']])
        ) {
          if (
            $this->createTranslation(
              $this->papaya()->administrationLanguage->id,
              empty($this->params['copy_lng_id']) ? 0 : (int)$this->params['copy_lng_id']
            )
          ) {
            $this->papaya()->messages->dispatch(
              new PapayaMessageLog(
                PapayaMessageLogable::GROUP_CONTENT,
                PapayaMessage::SEVERITY_INFO,
                new PapayaUiString(
                  '%s created a new translation "%s" for page "#%d"',
                  array(
                    $this->papaya()->administrationUser->getDisplayName(),
                    $this->papaya()->languages[(int)$this->params['lng_id']]['code'],
                    $this->topicId
                  )
                )
              )
            );
            $this->papaya()->messages->dispatch(
              new PapayaMessageDisplay(
                PapayaMessage::SEVERITY_INFO,
                new PapayaUiString(
                  'New translation "%s" for page "#%d" added.',
                  array(
                    $this->papaya()->languages[(int)$this->params['lng_id']]['code'],
                    $this->topicId
                  )
                )
              )
            );
            $this->sychronizations()->synchronizeAction(
              \Papaya\Content\Page\Dependency::SYNC_PROPERTIES |
              \Papaya\Content\Page\Dependency::SYNC_VIEW |
              \Papaya\Content\Page\Dependency::SYNC_CONTENT,
              $this->topicId,
              array($this->papaya()->administrationLanguage->id)
            );
            $this->loadTranslatedData(
              $this->topicId, $this->papaya()->administrationLanguage->id
            );
            $this->currentLanguage = $this->papaya()->administrationLanguage->getCurrent();
          }
        }
      }
      break;
    case 'add_topic':
      if ((
           $authUser->hasPerm(Administration\Permissions::PAGE_CREATE) &&
           $this->hasPermUser(PERM_CREATE, $authUser)
          ) &&
          (
           $authUser->startNode == 0 ||
           $this->hasParent($authUser->startNode)
          ) &&
          (
           $this->getLevel($authUser->startNode) < $authUser->subLevel ||
           $authUser->subLevel == 0
          )
         ) {
        if ($newId = $this->create()) {
          $this->papaya()->messages->dispatch(
            new PapayaMessageLog(
              PapayaMessageLogable::GROUP_CONTENT,
              PapayaMessage::SEVERITY_INFO,
              new PapayaUiString(
                '%s created the new page "#%d"',
                array(
                  $this->papaya()->administrationUser->getDisplayName(),
                  $newId
                )
              )
            )
          );
          $this->params['page_id'] = $newId;
          $this->initializeSessionParam('page_id', array('mode', 'cmd'));
          $this->load($this->params['page_id'], $this->papaya()->administrationLanguage->id);
        }
      }
      break;
    case 'import' :
      if ($this->hasImportView()) {
        $this->params['mode'] = 100;
        if (isset($this->params['import_confirm']) && $this->params['import_confirm']) {
          $importer = new base_import();
          $importer->layout = $this->layout;
          $moduleData = $this->topic['TRANSLATION'];
          $importData = $importer->importFile(
            $_FILES[$this->paramName]['tmp_name']['import_file'],
            $_FILES[$this->paramName]['name']['import_file'],
            $this->topicId,
            $moduleData['lng_id'],
            $moduleData['view_id']
          );
          if ($importData) {
            $plugin = $this->papaya()->plugins->get(
              $moduleData['module_guid'],
              $this,
              $importData
            );
            if (isset($plugin) && is_object($plugin)) {
              if ($this->saveContent($moduleData['module_guid'], $plugin->getData())) {
                $this->addMsg(MSG_INFO, $this->_gt('Import saved.'));
              }
            }
          }
        }
        $this->layout->add($this->getImportDialogXML());
      } else {
        $this->addMsg(MSG_INFO, $this->_gt('Please define an import configuration for this view.'));
      }
      break;
    case 'del_trans':
      if (isset($this->params['del_trans_confirm']) &&
          $this->params['del_trans_confirm']) {
        if (isset($this->params['del_trans_language']) &&
            $this->params['del_trans_language']) {
          foreach ($this->params['del_trans_language'] as $lng) {
            if ($this->deleteTrans($lng)) {
              $this->addMsg(MSG_INFO, $this->_gt('Language version of page deleted.'));
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_PAGES,
                sprintf(
                  'Language version "%s" of page "%s (%s)" deleted.',
                  $this->papaya()->languages[(int)$lng]['code'],
                  $this->topic['TRANSLATION']['topic_title'],
                  (int)$this->topicId
                )
              );
              $this->loadTranslationsData();
              $this->loadTranslatedData(
                $this->topicId, $this->papaya()->administrationLanguage->id
              );
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('Couldn\'t delete language version %s of page.')
              );
            }
          }
          $this->sychronizations()->synchronizeAction(
            \Papaya\Content\Page\Dependency::SYNC_PROPERTIES |
            \Papaya\Content\Page\Dependency::SYNC_VIEW |
            \Papaya\Content\Page\Dependency::SYNC_CONTENT,
            $this->topicId,
            (array)$this->params['del_trans_language']
          );
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Select language version to delete.'));
          $this->layout->add($this->getDelTransForm());
        }
      } else {
        $this->layout->add($this->getDelTransForm());
      }
      break;
    }
    if (!$this->topicId && $this->hasParent($authUser->user['start_node']) ||
         (
            $this->topic['is_deleted'] &&
            !$authUser->hasPerm(Administration\Permissions::PAGE_TRASH_MANAGE)
         )
       ) {
      if ($this->params['page_id'] != $authUser->user['start_node'] &&
          !empty($this->params['redirected'])) {
        $protocol = PapayaUtilServerProtocol::get();
        $toUrl = $protocol."://".$_SERVER['HTTP_HOST'].$this->getBasePath().
          $this->getLink(
            array(
              'redirected' => 1,
              'page_id' => $authUser->user['start_node']
            )
          );
        if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
          header('X-Papaya-Status: redirecting to allowed subtree');
        }
        header('Location: '.str_replace(array("\r", "\n"), '', $toUrl));
        exit;
      }
      $this->addMsg(MSG_INFO, $this->_gt('Please select a page.'));
      $this->menubar->addButton(
        'Add page',
        $this->getLink(
          array(
            'cmd' => 'add_topic',
            'mode' => 0,
            'page_id' => $this->topicId
          )
        ),
        'actions-page-add',
        'Create a new page'
      );
    } elseif (!(
                $this->hasPermUser(PERM_WRITE, $authUser) &&
                $this->editable($authUser)
              )) {
      $this->getPageHierarchy();
      $this->addMsg(MSG_ERROR, $this->_gt('You have no edit permissions for this page.'));
      if (!isset($this->params['mode'])) {
        $this->params['mode'] = 0;
      }
      switch ((int)$this->params['mode']) {
      case 100 :
        //import - just block all stuff
        break;
      case 5:
        $this->embedPreview();
        break;
      default:
        $this->layout->add($this->getPropertiesReadOnly());
        $this->layout->addRight($this->getInformation());
      }
      $this->getToolbar();
    } elseif (isset($this->topic['TRANSLATION']) &&
              is_array($this->topic['TRANSLATION'])) {
      $this->getPageHierarchy();
      $this->setMenuBar(TRUE);
      if (!isset($this->params['mode'])) {
        $this->params['mode'] = 0;
      }
      switch ((int)$this->params['mode']) {
      case 100 :
        //import - just block all stuff
        break;
      case 1: //edit content
        $dependencyBlocker = $this->getDependencyBlocker();
        if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_CONTENT)) {
          $this->layout->add($dependencyBlocker->getXml());
        } elseif ($str = $this->getEditContent()) {
          $this->layout->add($str);
        }
        break;
      case 2:  //boxes
        $dependencyBlocker = $this->getDependencyBlocker();
        if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_BOXES)) {
          $this->layout->add($dependencyBlocker->getXml());
        } elseif ($authUser->hasPerm(Administration\Permissions::BOX_LINK)) {
          $boxLinks = new papaya_boxeslinks($this);
          $boxLinks->initialize();
          $dialog = $boxLinks->getModeDialog($this->topic['box_useparent']);
          $this->layout->add($dialog->getXml());
          if ($dialog->execute()) {
            $saved = $this->saveBoxUseParent(
              $dialog->data()->get('box_useparent', papaya_boxeslinks::INHERIT_ALL)
            );
            if ($saved) {
              $this->load($this->topicId, $this->papaya()->administrationLanguage->id);
              $this->sychronizations()->synchronizeAction(
                \Papaya\Content\Page\Dependency::SYNC_BOXES,
                $this->topicId,
                array($this->papaya()->administrationLanguage->id)
              );
            }
          }
          $boxLinks->setPageId($this->getBoxesTopicId(), $this->getBoxGroupsTopicId());
          $boxLinks->loadList();
          $boxLinks->loadBoxList();
          $boxLinks->loadBoxGroupList();
          if ($boxLinks->execute()) {
            $this->sychronizations()->synchronizeAction(
              \Papaya\Content\Page\Dependency::SYNC_BOXES,
              $this->topicId,
              array($this->papaya()->administrationLanguage->id)
            );
          }
          $this->layout->add(
            $boxLinks->getList(
              $this->papaya()->images, $this->topic['box_useparent'], $this->_gt('Linked boxes')
            )
          );
        } else {
          $this->addMsg(
            MSG_WARNING,
            "You have no permissions to edit box links."
          );
        }
        break;
      case 5:  // preview
        $this->embedPreview();
        break;
      case 4: // surfer permissions
        $dependencyBlocker = $this->getDependencyBlocker();
        if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_ACCESS)) {
          $this->layout->add($dependencyBlocker->getXml());
        } elseif ($authUser->hasPerm(2, '88236ef1454768e23787103f46d711c2')) {
          $sfl = new base_surferlinks($this->topicId);
          if ($sfl->execute()) {
            $this->sychronizations()->synchronizeAction(
              \Papaya\Content\Page\Dependency::SYNC_ACCESS,
              $this->topicId,
              array($this->papaya()->administrationLanguage->id)
            );
          }
          $this->layout->add($sfl->get($this->papaya()->administrationLanguage->id));
          $this->layout->addRight($sfl->getModeDlg());
        } else {
          $this->addMsg(
            MSG_WARNING,
            "You have no permissions to edit access permissions"
          );
        }
        break;
      case 6 : //view and module
        $dependencyBlocker = $this->getDependencyBlocker();
        if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_VIEW)) {
          $this->layout->add($dependencyBlocker->getXml());
          $this->layout->addRight($this->getInformation());
          break;
        } elseif (isset($this->params['cmd']) && $this->params['cmd'] === 'chg_view') {
          if ($this->checkEditView()) {
            if ($this->saveView()) {
              $this->loadTranslatedData(
                $this->topicId, $this->papaya()->administrationLanguage->id
              );
              $this->addMsg(
                MSG_INFO,
                sprintf($this->_gt('%s modified.'), $this->_gt('View'))
              );
              $this->sychronizations()->synchronizeAction(
                \Papaya\Content\Page\Dependency::SYNC_VIEW,
                $this->topicId,
                array($this->papaya()->administrationLanguage->id)
              );
            }
          }
        }
        $this->layout->add($this->getEditView());
        $this->layout->addRight($this->getInformation());
        break;
      case 7: //versions
        // public version
        $this->loadTranslationsInfo();
        $this->layout->add($this->publishExecute());
        $this->layout->add($this->socialMediaExecute());
        $this->load($this->topicId, $this->papaya()->administrationLanguage->id);
        $this->loadTranslationsInfo();
        $this->layout->add($this->getPublicData());
        // backup versions
        if ($authUser->hasPerm(Administration\Permissions::PAGE_VERSION_MANAGE)) {
          $this->loadVersionsList();
          if (isset($this->params['version_id']) &&
              $this->loadVersion($this->params['version_id'])) {
            $this->layout->add($this->versionExecute());
            $this->loadVersionTranslatedData(
              empty($this->params['version_id']) ? 0 : (int)$this->params['version_id'],
              $this->papaya()->administrationLanguage->id
            );
            $this->loadVersionTranslationsInfo();
          }
          $this->loadVersionsList();
          if (isset($this->savedVersion) && is_array($this->savedVersion)) {
            $this->getVersionInfos();
          }
          $this->layout->add($this->getVersionsList());
        }
        break;
      case 8: //editor permissions
        if ($authUser->hasPerm(Administration\Permissions::PAGE_PERMISSION_MANAGE)) {
          $editUser = new papaya_user();
          $editUser->initialize('edit_usr');
          $editUser->loadGroups();
          $editUser->loadUsers();
          $editUser->loadGroupTree();
          $editUser->load($this->topic['author_id']);
          $this->changePermExecute($editUser);
          $this->layout->add($this->getEditPerm($editUser));
          $this->layout->addRight($this->getEditUser($editUser));
        }
        break;
      case 10: // tags
        $dependencyBlocker = $this->getDependencyBlocker();
        if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_TAGS)) {
          $this->layout->add($dependencyBlocker->getXml());
        } elseif ($authUser->hasPerm(Administration\Permissions::TAG_MANAGE)) {
          $tags = papaya_taglinks::getInstance($this);
          if (isset($tags) && $tags->prepare('topic', $this->topicId)) {
            if ($tags->execute()) {
              $this->sychronizations()->synchronizeAction(
                \Papaya\Content\Page\Dependency::SYNC_TAGS,
                $this->topicId,
                array($this->papaya()->administrationLanguage->id)
              );
            }
            $this->layout->add($tags->getXml());
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You have no permissions to edit tag links.')
          );
        }
        break;
      case 11 : // dependencies
        if ($authUser->hasPerm(Administration\Permissions::PAGE_DEPENDENCY_MANAGE)) {
          $dependencies = new Administration\Pages\Dependency\Changer();
          $dependencies->parameterGroup($this->paramName);
          $this->layout->add($dependencies->getXml());
          $this->menubar->addSeparator();
          foreach ($dependencies->menu()->elements as $button) {
            if ($button instanceof PapayaUiToolbarSeparator) {
              $this->menubar->addSeparator();
            } else {
              $this->menubar->addButton(
                (string)$button->caption,
                $button->reference->getRelative(),
                $button->image,
                (string)$button->hint
              );
            }
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('You have no permissions to change page dependencies.')
          );
        }
        break;
      default:
        $this->execPageActions();
        $this->setMenuBar(TRUE);
        $dependencyBlocker = $this->getDependencyBlocker();
        if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_PROPERTIES)) {
          $this->layout->add($dependencyBlocker->getXml());
        } else {
          $this->layout->add($this->getPropertiesDialog());
        }
        $this->layout->addRight($this->getInformation());
      }
      $this->getToolbar();
    } else {
      //Show a dialog to create a translation
      $this->setMenuBar(TRUE);
      $this->execPageActions();
      $dependencyBlocker = $this->getDependencyBlocker();
      if ($dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_PROPERTIES)) {
        $this->layout->add($dependencyBlocker->getXml());
      } else {
        $this->layout->add($this->addTranslationDialog());
        $this->layout->add($this->getPropertiesDialog());
      }
      $this->layout->addRight($this->getInformation());
    }
    $this->addTopicIdDialog();
    $topicTree = new papaya_topic_tree('ptt');
    $topicTree->layout = $this->layout;
    $topicTree->paramName = 'tt';
    if (isset($this->params['mode']) && $this->params['mode'] == 5) {
      $this->addVersionPreviewDialog();
    }
    $this->layout->addLeft($topicTree->getPartTree($this));

    if ($str = $this->menubar->getXML()) {
      $this->layout->addMenu(
        sprintf('<menu ident="%s">%s</menu>'.LF, 'edit2', $str)
      );
    }

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  public function embedPreview() {
    $outputObj = new papaya_output();
    $views = $outputObj->loadViewsList($this->topic['TRANSLATION']['view_id']);

    $viewMode = $this->papaya()->options->get('PAPAYA_URL_EXTENSION', 'html');
    $viewLinks = array();
    if (count($views) > 0) {
      $viewLinks['xml'] = 'xml';
      foreach ($views as $view) {
        $viewLinks[$view['viewmode_ext']] = $view['viewmode_ext'];
      }
      if (isset($this->params['viewmode']) && isset($viewLinks[$this->params['viewmode']])) {
        $viewMode = $this->params['viewmode'];
      }
      if (!isset($viewLinks[$viewMode])) {
        $viewMode = 'xml';
        $this->addMsg(
          MSG_WARNING,
          $this->_gt('This page has currently no default output - showing XML.')
        );
      }
    } else {
      $viewMode = 'xml';
      $this->addMsg(
        MSG_WARNING,
        $this->_gt('This page has currently no formatted output - showing XML.')
      );
    }
    $link = $this->getPreviewUrl(
      $viewMode,
      empty($this->params['version_datetime']) ? 0 : (int)$this->params['version_datetime']
    );
    $this->sessionParams['viewmode'] = $viewMode;
    $this->layout->add(
      $this->getContentFrame(
        $link,
        $this->_gt('Preview'),
        $viewLinks,
        $viewMode
      )
    );
    $this->menubar->addSeperator();
    $this->menubar->addButton(
      'Full window',
      $link,
      'actions-view-fullscreen',
      'Full window preview'
    );
  }

  public function getPreviewUrl($mode = NULL, $time = 0) {
    $reference = new PapayaUiReferencePage();
    $reference->setPreview(TRUE, $time);
    $reference->setOutputMode($mode);
    $reference->setPageLanguage(
      $this->papaya()->administrationLanguage->getCurrent()->identifier
    );
    $reference->setPageId($this->topicId);
    return $reference->getRelative();
  }

  /**
  * Get the dependency blocker object. It is used to block editing pages if the page depends to
  * another page
  *
  * @return Administration\Pages\Dependency\Blocker
  */
  public function getDependencyBlocker() {
    $blocker = new Administration\Pages\Dependency\Blocker((int)$this->topicId);
    $blocker->parameterGroup($this->paramName);
    return $blocker;
  }

  /**
  * Execute page actions
  *
  * @access public
  */
  function execPageActions() {
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    $authUser = $this->papaya()->administrationUser;
    switch ($this->params['cmd']) {
    case 'save_properties':
      $dependencyBlocker = $this->getDependencyBlocker();
      if (!$dependencyBlocker->isSynchronized(\Papaya\Content\Page\Dependency::SYNC_PROPERTIES)) {
        $this->initializePropertiesDialog();
        if ($this->dialogProperties->checkDialogInput()) {
          if ($this->saveProperties()) {
            unset($this->dialogProperties);
            $this->load($this->topicId, $this->papaya()->administrationLanguage->id);
            $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            $this->sychronizations()->synchronizeAction(
              \Papaya\Content\Page\Dependency::SYNC_PROPERTIES,
              $this->topicId,
              array($this->papaya()->administrationLanguage->id)
            );
          } else {
            $this->addMsg(MSG_WARNING, $this->_gt('Database error! Changes not saved.'));
          }
        }
      }
      break;
    case 'del_topic':
      if (isset($this->params['del_topic_confirm']) && isset($this->topic)) {
        $prev = empty($this->topic['prev']) ? 0 : (int)$this->topic['prev'];
        if ($this->topic['is_deleted'] &&
            $authUser->hasPerm(Administration\Permissions::PAGE_TRASH_MANAGE) &&
              $authUser->hasPerm(Administration\Permissions::PAGE_DELETE)) {
          if ($this->destroy()) {
            $this->addMsg(
              MSG_INFO,
              $this->_gtf('Page #%d deleted.', array($this->topicId)),
              TRUE,
              PAPAYA_LOGTYPE_PAGES
            );
            unset($topic);
            $this->params['page_id'] = $prev;
            $this->initializeSessionParam('page_id', array('cmd', 'mode'));
            $this->load(
              $this->params['page_id'],
              $this->papaya()->administrationLanguage->id
            );
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Cannot delete this page.'));
          }
        } elseif ($this->delete() &&
                  $authUser->hasPerm(Administration\Permissions::PAGE_DELETE)) {
          $this->addMsg(
            MSG_INFO,
            $this->_gtf('Page #%d moved to trash.', array($this->topicId)),
            TRUE,
            PAPAYA_LOGTYPE_PAGES
          );
          unset($topic);
          $this->params['page_id'] = $prev;
          $this->initializeSessionParam('page_id', array('cmd', 'mode'));
          $this->load(
            $this->params['page_id'],
            $this->papaya()->administrationLanguage->id
          );
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Cannot delete this page.'));
        }
      } else {
        $this->layout->add($this->getDelForm());
      }
      break;
    }
  }

  /**
  * Load translations information
  *
  * @access public
  */
  function loadTranslationsInfo() {
    unset($this->topic['TRANSLATIONINFOS']);
    $sql = "SELECT tt.topic_id, tt.lng_id, tt.topic_trans_modified,
                   tt.topic_title, ttp.topic_trans_modified as topic_trans_published,
                   v.view_title, v.view_is_cacheable
              FROM %s tt
              LEFT OUTER JOIN %s ttp
                ON (ttp.topic_id = tt.topic_id AND ttp.lng_id = tt.lng_id)
              LEFT OUTER JOIN %s v ON (v.view_id = tt.view_id)
             WHERE tt.topic_id = %d";
    $params = array($this->tableTopicsTrans, $this->tableTopicsPublicTrans, $this->tableViews,
      (int)$this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $this->topic['UNPUBLISHED'] = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topic['TRANSLATIONINFOS'][$row['lng_id']] = $row;
        if ((!isset($row['topic_trans_published'])) ||
            $row['topic_trans_published'] < $row['topic_trans_modified']) {
          $this->topic['UNPUBLISHED']++;
        }
      }
      $this->saveUnpublishedLanguages($this->topic['UNPUBLISHED']);
    }
  }

  /**
  * Save the current count of unpublished language version to the topic record
  *
  * @param integer $unpublished
  * @access public
  * @return boolean
  */
  function saveUnpublishedLanguages($unpublished) {
    if ($this->topic['topic_unpublished_languages'] != $unpublished) {
      $data = array(
        'topic_unpublished_languages' => $unpublished
      );
      $filter = array(
        'topic_id' => (int)$this->topicId
      );
      if (FALSE !== $this->databaseUpdateRecord($this->tableTopics, $data, $filter)) {
        $this->topic['topic_unpublished_languages'] = $unpublished;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load and display the ancestors of the current page
  */
  public function getPageHierarchy() {
    $pageIds = PapayaUtilArray::decodeIdList($this->topic['prev_path']);
    $pageIds[] = $this->topic['prev'];
    $pageIds[] = $this->topicId;
    $hierarchy = new Administration\Pages\Ancestors();
    $hierarchy->setIds($pageIds);
    $this->layout->add($hierarchy->getXml(), 'toolbars');
  }

  /**
  * Get toolbar
  *
  * @access public
  */
  function getToolbar() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $authUser = $this->papaya()->administrationUser;

    if ($this->hasPermUser(PERM_WRITE, $authUser) && $this->editable($authUser)) {
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 0, 'page_id' => $this->topicId)
        ),
        'categories-properties',
        '',
        (empty($this->params['mode']) || $this->params['mode'] == 0)
      );
      $toolbar->addButton(
        'View',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 6, 'page_id' => $this->topicId)
        ),
        'items-view',
        'Select module and view',
        (isset($this->params['mode']) && $this->params['mode'] == 6)
      );
      $toolbar->addButton(
        'Content',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 1, 'page_id' => $this->topicId)
        ),
        'categories-content',
        'Edit content',
        (isset($this->params['mode']) && $this->params['mode'] == 1)
      );
      if ($authUser->hasPerm(Administration\Permissions::BOX_LINK)) {
        $toolbar->addButton(
          'Boxes',
          $this->getLink(
            array('cmd' => 'chg_mode', 'mode' => 2, 'page_id' => $this->topicId)
          ),
          'items-box',
          'Connect boxes',
          (isset($this->params['mode']) && $this->params['mode'] == 2)
        );
      }
      if ($authUser->hasPerm(Administration\Permissions::TAG_MANAGE)) {
        $toolbar->addButton(
          'Tags',
          $this->getLink(
            array('cmd' => 'chg_mode', 'mode' => 10, 'page_id' => $this->topicId)
          ),
          'items-tag',
          'Connect tags',
          (isset($this->params['mode']) && $this->params['mode'] == 10)
        );
      }
      $toolbar->addButton(
        'Preview',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 5, 'page_id' => $this->topicId)
        ),
        'categories-preview',
        'Page preview',
        (isset($this->params['mode']) && $this->params['mode'] == 5)
      );
      $toolbar->addSeperator();
      if ($authUser->hasPerm(Administration\Permissions::PAGE_DEPENDENCY_MANAGE)) {
        $toolbar->addButton(
          $this->_gt('Dependencies').$this->getDependencyBlocker()->counter()->getLabel(),
          $this->getLink(
            array('cmd' => 'chg_mode', 'mode' => 11, 'page_id' => $this->topicId)
          ),
          $this->getDependencyBlocker()->dependency()->isDependency($this->topicId)
            ? 'status-file-inherited' : 'actions-edit-copy',
          $this->_gt('Manage depencies'),
          (isset($this->params['mode']) && $this->params['mode'] == 11),
          TRUE
        );
      }
      if ($authUser->hasPerm(2, '88236ef1454768e23787103f46d711c2')) {
        $toolbar->addButton(
          'Access',
          $this->getLink(
            array('cmd' => 'chg_mode', 'mode' => 4, 'page_id' => $this->topicId)
          ),
          'categories-access',
          'Restrict access',
          (isset($this->params['mode']) && $this->params['mode'] == 4)
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Versions',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 7, 'page_id' => $this->topicId)
        ),
        'items-time',
        'Version management',
        (isset($this->params['mode']) && $this->params['mode'] == 7)
      );
      if ($authUser->hasPerm(Administration\Permissions::PAGE_PERMISSION_MANAGE)) {
        $toolbar->addButton(
          'Permissions',
          $this->getLink(
            array('cmd' => 'chg_mode', 'mode' => 8, 'page_id' => $this->topicId)
          ),
          'items-user-group',
          'Edit permissions',
          (isset($this->params['mode']) && $this->params['mode'] == 8)
        );
      }
    } elseif ($this->hasPermUser(PERM_READ, $authUser)) {
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 0, 'page_id' => $this->topicId)
        ),
        'categories-properties',
        '',
        (isset($this->params['mode']) && $this->params['mode'] != 5)
      );
      $toolbar->addButton(
        'Preview',
        $this->getLink(
          array('cmd' => 'chg_mode', 'mode' => 5)
        ),
        'categories-preview',
        'Page preview',
        (isset($this->params['mode']) && $this->params['mode'] == 5)
      );
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->add('<toolbar>'.$str.'</toolbar>', 'toolbars');
    }
  }

  /**
  * Set menu bar
  *
  * @param boolean $editable optional, default value TRUE
  * @access public
  */
  function setMenuBar($editable = TRUE) {
    $this->menubar->clear();
    $authUser = $this->papaya()->administrationUser;
    if ($this->hasPermUser(PERM_CREATE, $authUser, $this->topic['prev'])) {
      $this->menubar->addButton(
        'Add page',
        $this->getLink(
          array(
            'cmd' => 'add_topic',
            'page_id' => $this->topic['prev'],
            'mode' => 0
          )
        ),
        'actions-page-add',
        'Create a new page'
      );
    }
    if ($this->hasPermUser(PERM_CREATE, $authUser)) {
      $this->menubar->addButton(
        'Add subpage',
        $this->getLink(
          array(
            'cmd' => 'add_topic',
            'page_id' => $this->topicId,
            'mode' => 0
          )
        ),
        'actions-page-child-add',
        'Create a new subpage'
      );
    }

    if ($editable) {
      $this->menubar->addButton(
        'Move page',
        $this->getLink(
          array(
            'cmd' => 'move_page_dialog',
            'page_id' => $this->topicId,
            'mode' => 0
          )
        ),
        'actions-page-move',
        'Move this page'
      );
      if (defined('PAPAYA_IMPORTFILTER_USE') && PAPAYA_IMPORTFILTER_USE && $this->hasImportView()) {
        $this->menubar->addSeperator();
        $this->menubar->addButton(
          'Import',
          $this->getLink(array('cmd' => 'import')),
          'actions-upload',
          '',
          isset($this->params['mode']) && $this->params['mode'] == 100
        );
      }
      $this->menubar->addSeperator();

      if ($authUser->hasPerm(Administration\Permissions::PAGE_DELETE)) {
        $this->menubar->addButton(
          'Delete page',
          $this->getLink(
            array(
              'cmd' => 'del_topic',
              'page_id' => $this->topicId,
              'mode' => 0
            )
          ),
          'actions-page-delete',
          'Delete this page'
        );
        if (isset($this->topic['TRANSLATION'])) {
          $this->menubar->addButton(
            'Delete translation',
            $this->getLink(
              array(
                'cmd' => 'del_trans',
                'page_id' => $this->topicId
              )
            ),
            'actions-phrase-delete',
            'Delete a language version of this page'
          );
        }
      }
      $this->menubar->addSeperator();
      if ($authUser->hasPerm(Administration\Permissions::PAGE_PUBLISH)) {
        $this->menubar->addButton(
          'Publish page',
          $this->getLink(
            array(
              'cmd' => 'publish',
              'page_id' => $this->topicId,
              'mode' => 7
            )
          ),
          'items-publication',
          'Publish this page'
        );
        if (isset($this->params['mode']) && $this->params['mode'] == 7) {
          $this->menubar->addButton(
            'Handoff page',
            $this->getLink(
              array(
                'cmd' => 'handoff',
                'page_id' => $this->topicId,
                'mode' => 7
              )
            ),
            'actions-publication-forward',
            'Handoff this page'
          );
        }
      } else {
        $this->menubar->addButton(
          'Handoff page',
          $this->getLink(
            array(
              'cmd' => 'handoff',
              'page_id' => $this->topicId,
              'mode' => 7
            )
          ),
          'actions-publication-forward',
          'Handoff this page'
        );
      }
    }
  }

  /**
  * Load
  *
  * @param integer $id topic id
  * @param integer $lngId language id
  * @access public
  * @return boolean
  */
  function load($id, $lngId) {
    $result = FALSE;
    $sql = "SELECT t.topic_id,
                   t.prev, t.prev_path,
                   t.topic_mainlanguage, t.topic_weight,
                   t.topic_created, t.topic_modified,
                   t.topic_changefreq, t.topic_priority,
                   t.topic_cachemode, t.topic_cachetime,
                   t.topic_expiresmode, t.topic_expirestime, t.topic_sessionmode,
                   t.author_id, t.author_perm, t.author_group,
                   t.linktype_id, t.topic_protocol, t.is_deleted,
                   t.meta_useparent, t.box_useparent, t.surfer_useparent, t.surfer_permids,
                   t.topic_unpublished_languages,
                   tp.topic_modified AS topic_published,
                   tp.topic_created AS topic_published_created,
                   tp.published_from, tp.published_to,
                   u.user_id, u.givenname AS author_givenname, u.surname AS author_surname,
                   l.lng_short, l.lng_title
              FROM %s t
              LEFT OUTER JOIN %s tp ON tp.topic_id = t.topic_id
              LEFT OUTER JOIN %s u ON t.author_id = u.user_id
              LEFT OUTER JOIN %s l ON l.lng_id = t.topic_mainlanguage
             WHERE t.topic_id = %d";
    $params = array($this->tableTopics, $this->tableTopicsPublic,
      $this->tableAuthUser, $this->tableLanguages, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topicId = (int)$row["topic_id"];
        $this->topic = $row;
        $row['TRANSLATION'] = NULL;
        $this->loadTranslatedData($this->topicId, $lngId);
        $result = TRUE;
      }
      $res->free();
    }
    return $result;
  }

  /**
  * Store topic in database without content(ohne Inhalt)
  *
  * @access public
  * @return boolean
  */
  function saveProperties() {
    if ($this->topicId == $this->dialogProperties->params['page_id']) {
      $authUser = $this->papaya()->administrationUser;
      $translationModified = FALSE;
      $result = TRUE;
      if (isset($this->topic['TRANSLATION'])) {
        $dataTrans = array(
          'topic_title' => $this->dialogProperties->data['topic_title']
        );
        if ($authUser->hasPerm(Administration\Permissions::PAGE_METADATA_EDIT) &&
            $this->dialogProperties->data['meta_useparent']) {
          if (isset($this->dialogProperties->params['meta_title'])) {
            $dataTrans['meta_title'] = $this->dialogProperties->data['meta_title'];
          }
          if (isset($this->dialogProperties->params['meta_keywords'])) {
            $dataTrans['meta_keywords'] = $this->dialogProperties->data['meta_keywords'];
          }
          if (isset($this->dialogProperties->params['meta_descr'])) {
            $dataTrans['meta_descr'] = $this->dialogProperties->data['meta_descr'];
          }
        }
        if ($this->checkDataModified($dataTrans, $this->topic['TRANSLATION'])) {
          $translationModified = TRUE;
          $dataTrans['topic_trans_modified'] = time();
          $filter = array(
            'topic_id' => (int)$this->topicId,
            'lng_id' => $this->topic['TRANSLATION']['lng_id']
          );
          $result = (
            FALSE !== $this->databaseUpdateRecord(
              $this->tableTopicsTrans, $dataTrans, $filter
            )
          );
        }
      } else {
        $dataTrans = array('topic_title' => $this->_gt('No title'));
      }
      if ($result) {
        $data = array(
          'linktype_id' => $this->dialogProperties->data['linktype_id'],
          'topic_protocol' => $this->dialogProperties->data['topic_protocol'],
          'prev_path' => $this->checkPath(),
          'is_deleted' => '0',
          'topic_mainlanguage' => $this->dialogProperties->data['topic_mainlanguage'],
          'topic_changefreq' => $this->dialogProperties->data['topic_changefreq'],
          'topic_priority' => $this->dialogProperties->data['topic_priority'],
          'topic_cachemode' => $this->dialogProperties->data['topic_cachemode'],
          'topic_cachetime' => $this->dialogProperties->data['topic_cachetime'],
          'topic_expiresmode' => $this->dialogProperties->data['topic_expiresmode'],
          'topic_expirestime' => $this->dialogProperties->data['topic_expirestime'],
          'topic_sessionmode' => $this->dialogProperties->data['topic_sessionmode']
        );
        if ($authUser->hasPerm(Administration\Permissions::PAGE_METADATA_EDIT)) {
          $data['meta_useparent'] = (int)(!$this->dialogProperties->data['meta_useparent']);
        }
        if ($translationModified || $this->checkDataModified($data, $this->topic)) {
          $data['topic_modified'] = time();
          $result = (
            FALSE !== $this->databaseUpdateRecord(
              $this->tableTopics, $data, 'topic_id', (int)$this->topicId
            )
          );
          if ($result) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'Properties of page "%s" #%d changed.',
                papaya_strings::escapeHTMLChars($dataTrans['topic_title']),
                (int)$this->topicId
              )
            );
          }
        }
      }
      return $result;
    }
    return FALSE;
  }

  /**
   * save language specific content
   *
   * @access public
   * @param string $moduleGuid
   * @param string $content
   * @return boolean
   */
  function saveContent($moduleGuid, $content) {
    if (isset($this->topic['TRANSLATION']) && is_array($this->topic['TRANSLATION'])) {
      $dependencyBlocker = $this->getDependencyBlocker();
      $pageViews = $dependencyBlocker->getSynchronizedViews(
        $this->topic['TRANSLATION']['lng_id']
      );
      foreach ($pageViews as $pageId => $view) {
        if ($view['module_id'] != $moduleGuid) {
          $this->papaya()->messages->dispatch(
            new PapayaMessageDisplayTranslated(
              PapayaMessage::SEVERITY_WARNING,
              'Dependend page #%d uses a view with a differnt module. Can not change content.',
              array(
                $pageId
              )
            )
          );
          return FALSE;
        }
      }
      $dataTrans = array(
        'topic_content' => $content
      );
      if ($this->checkDataModified($dataTrans, $this->topic['TRANSLATION'])) {
        $dataTrans['topic_trans_modified'] = time();
        $filter = array(
          'topic_id' => (int)$this->topicId,
          'lng_id' => $this->topic['TRANSLATION']['lng_id']
        );
        if (FALSE !== $this->databaseUpdateRecord($this->tableTopicsTrans, $dataTrans, $filter)) {
          $data = array(
            'topic_modified' => $dataTrans['topic_trans_modified'],
            'is_deleted' => '0'
          );
          if (
            FALSE !== $this->databaseUpdateRecord(
              $this->tableTopics, $data, 'topic_id', (int)$this->topicId
            )
          ) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'Content of page "%s (%s)" changed.',
                papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                (int)$this->topicId
              )
            );
            $this->sychronizations()->synchronizeAction(
              \Papaya\Content\Page\Dependency::SYNC_CONTENT,
              $this->topicId,
              array($this->papaya()->administrationLanguage->id)
            );
            return TRUE;
          }
        }
      }
      return FALSE;
    } else {
      return FALSE;
    }
  }

  /**
  * save language specific view
  *
  * @access public
  * @return boolean
  */
  function saveView() {
    if (isset($this->topic['TRANSLATION']) &&
        is_array($this->topic['TRANSLATION'])) {
      $dataTrans = array(
        'view_id' => (int)$this->params['view_id']
      );
      if ($this->checkDataModified($dataTrans, $this->topic['TRANSLATION'])) {
        $dataTrans['topic_trans_modified'] = time();
        $filter = array(
          'topic_id' => (int)$this->topicId,
          'lng_id' => $this->topic['TRANSLATION']['lng_id']
        );
        if (FALSE !== $this->databaseUpdateRecord($this->tableTopicsTrans, $dataTrans, $filter)) {
          $data = array(
            'topic_modified' => $dataTrans['topic_trans_modified'],
            'is_deleted' => '0'
          );
          if (
            FALSE !== $this->databaseUpdateRecord(
              $this->tableTopics, $data, 'topic_id', (int)$this->topicId
            )
          ) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'View of page "%s (%s)" changed.',
                papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                $this->topicId
              )
            );
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Create topic
  *
  * @return boolean
  */
  function create() {
    $authUser = $this->papaya()->administrationUser;
    if ($this->topicId > 0 && isset($this->topic) && is_array($this->topic)) {
      $data = array(
        'topic_modified' => time(),
        'topic_created' => time(),
        'topic_mainlanguage' => 0,
        'topic_changefreq' => (int)$this->topic['topic_changefreq'],
        'topic_priority' => (int)$this->topic['topic_priority'],
        'topic_weight' => (int)999999,
        'author_id' => $authUser->userId,
        'author_group' => (int)$this->topic['author_group'],
        'author_perm' => '7'.substr($this->topic['author_perm'], 1),
        'linktype_id' => (int)$this->topic['linktype_id'],
        'topic_protocol' => (int)$this->topic['topic_protocol'],
        'prev' => (int)$this->topicId,
        'prev_path' => str_replace(
          ';;',
          ';',
          $this->topic['prev_path'].$this->topic['prev'].';'
        ),
        'meta_useparent' => 1,
        'box_useparent' => 1,
        'surfer_useparent' => 2,
        'topic_cachemode' => 1,
        'topic_expiresmode' => 1
      );
    } else {
      $data = array(
        'topic_modified' => time(),
        'topic_created' => time(),
        'topic_mainlanguage' => 0,
        'author_id' => $authUser->userId,
        'author_group' => $authUser->user['group_id'],
        'author_perm' => '777',
        'linktype_id' => 1,
        'topic_protocol' => 0,
        'prev' => 0,
        'prev_path' => ';',
        'meta_useparent' => 0,
        'box_useparent' => 0,
        'surfer_useparent' => 1,
        'topic_cachemode' => 1,
        'topic_expiresmode' => 1
      );
    }
    if ($newId = $this->databaseInsertRecord($this->tableTopics, 'topic_id', $data)) {
      $data = array(
        'topic_id' => (int)$newId,
        'lng_id' => (int)$this->papaya()->administrationLanguage->id,
        'author_id' => $authUser->userId,
        'topic_trans_created' => time(),
        'topic_trans_modified' => time(),
        'topic_content' => '',
        'meta_title' => '',
        'meta_keywords' => '',
        'meta_descr' => '',
      );
      $this->databaseInsertRecord($this->tableTopicsTrans, NULL, $data);
      return $newId;
    }
    return FALSE;
  }

  /**
   * Create translation
   *
   * @param integer $lngId
   * @param $copyLngId
   * @access public
   * @return boolean
   */
  function createTranslation($lngId, $copyLngId) {
    $authUser = $this->papaya()->administrationUser;
    $data = array(
      'topic_id' => (int)$this->topicId,
      'lng_id' => (int)$lngId,
      'author_id' => $authUser->userId,
      'topic_trans_created' => time(),
      'topic_trans_modified' => time(),
      'topic_title' => '',
      'topic_content' => '',
      'meta_title' => '',
      'meta_keywords' => '',
      'meta_descr' => '',
    );
    if ($copyLngId > 0) {
      $sql = "SELECT topic_title, topic_content,
                     meta_title, meta_keywords, meta_descr,
                     view_id
                FROM %s
               WHERE topic_id = %d AND lng_id = %d";
      $params = array($this->tableTopicsTrans, (int)$this->topicId, $copyLngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          foreach ($row as $column => $value) {
            $data[$column] = $value;
          }
        }
        $res->free();
      }
    }
    return FALSE !== $this->databaseInsertRecord(
      $this->tableTopicsTrans, NULL, $data
    );
  }

  /**
  * Delete
  *
  * @access public
  * @return boolean
  */
  function delete() {
    //move current topic to trash (mark as deleted)
    if ($this->deletePublicTopic()) {
      $data = array(
        'is_deleted' => '1'
      );
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableTopics, $data, 'topic_id', $this->topicId
      );
    }
    return FALSE;
  }

  /**
  * Delete this entry and all subentries, all versions and all additional data
  *
  * @return boolean
  */
  function destroy() {
    //load Ids
    $ids = FALSE;
    $oldPath = $this->topic["prev_path"].$this->topic["prev"].
      ';'.$this->topic["topic_id"].';';
    $sql = "SELECT topic_id
              FROM %s
             WHERE topic_id = %d
                OR prev = '%d'
                OR prev_path like '%s%%'";
    $params = array($this->tableTopics, $this->topicId,
      $this->topicId, $oldPath);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $ids[] = $row[0];
      }
    }
    if (isset($ids) && is_array($ids)) {
      $filter = array("topic_id" => $ids);

      $topicTranslations = $this->loadTopicTranslations($ids);
      //delete boxes
      if (FALSE !== $this->databaseDeleteRecord($this->tableBoxesLinks, $filter)) {
        //delete public pages
        if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsPublicTrans, $filter)) {
          if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsPublic, $filter)) {
            //delete backups
            if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsVersionsTrans, $filter)) {
              if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsVersions, $filter)) {
                //delete page
                if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsTrans, $filter)) {
                  if (FALSE !== $this->databaseDeleteRecord($this->tableTopics, $filter)) {

                    $actionsConnector = $this
                        ->papaya()
                        ->plugins
                        ->get('79f18e7c40824a0f975363346716ff62');

                    if (is_object($actionsConnector)) {
                      $actionsConnector->call(
                          'default',
                          'onDeletePages',
                          ['topic_ids' => $ids, 'topic_translations' => $topicTranslations]
                      );
                    }
                    return TRUE;
                  }
                }
              }
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Load page translations, topic id with language id
   * @param array $topicIds
   * @return array
   */
  public function loadTopicTranslations($topicIds) {
    $topicTranslations = [];

    $filter = str_replace('%', '%%', $this->databaseGetSqlCondition('topic_id', $topicIds));

    $sql = "SELECT topic_id, lng_id
              FROM %s
             WHERE $filter";
    $parameters = [
        $this->databaseGetTableName('topic_public_trans')
    ];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $topicTranslations[] = $row;
      }
    }
    return $topicTranslations;
  }

  /**
   * Create version
   *
   * @access public
   * @param null|array $publishedLngs
   * @return boolean
   */
  function createVersion($publishedLngs = NULL) {
    $currentTime = time();
    if (isset($this->params['change_level'])) {
      $changeLevel = (int)$this->params['change_level'];
    } else {
      $changeLevel = -1;
    }
    $authUser = $this->papaya()->administrationUser;
    $versionData = array(
      'topic_id' => $this->topicId,
      'topic_weight' => $this->topic['topic_weight'],
      'topic_mainlanguage' => (int)$this->topic['topic_mainlanguage'],
      'topic_modified' => $this->topic['topic_modified'],
      'topic_change_level' => $changeLevel,
      'topic_changefreq' => $this->topic['topic_changefreq'],
      'topic_priority' => $this->topic['topic_priority'],
      'linktype_id' => (int)$this->topic['linktype_id'],
      'topic_protocol' => (int)$this->topic['topic_protocol'],
      'meta_useparent' => (int)$this->topic['meta_useparent'],
      'box_useparent' => (int)$this->topic['box_useparent'],
      'version_time' => $currentTime,
      'version_author_id' => $authUser->userId,
      'version_message' => $this->params['commit_message'],
    );
    if (
      $newVersionId = $this->databaseInsertRecord(
        $this->tableTopicsVersions, 'version_id', $versionData
      )
    ) {
      $sql = "INSERT INTO %s (version_id, lng_id, version_published,
                     topic_id, topic_title, topic_content, author_id,
                     view_id, meta_title, meta_keywords, meta_descr)
              SELECT '%d', tt.lng_id, 0, tt.topic_id, tt.topic_title,
                     tt.topic_content, tt.author_id,
                     tt.view_id, tt.meta_title, tt.meta_keywords, tt.meta_descr
                FROM %s tt
               WHERE tt.topic_id = %d";
      $params = array($this->tableTopicsVersionsTrans, $newVersionId,
        $this->tableTopicsTrans, $this->topicId);
      if (FALSE !== $this->databaseQueryFmtWrite($sql, $params)) {
        if (isset($publishedLngs) && is_array($publishedLngs) && count($publishedLngs) > 0) {
          $filter = $this->databaseGetSQLCondition('lng_id', $publishedLngs);
          $sql = "UPDATE %s SET version_published = 1
                   WHERE version_id = %d
                     AND $filter";
          $this->databaseQueryFmtWrite($sql, array($this->tableTopicsVersionsTrans, $newVersionId));
        }
        $this->removeOldVersions();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Changes the position of the current topic in the given direction
   *
   * @param integer $currentPosition
   * @param integer $direction
   * @return boolean TRUE, if position has been changed, else FALSE
   */
  function changePosition($currentPosition, $direction) {
    $newPosition = $currentPosition + $direction;
    if ($newPosition < 100001) {
      $newPosition = 100001;
    }

    if ($this->databaseGetProtocol() == 'pgsql') {
      //move other topics away
      if ($direction > 0) {
        $sql = "UPDATE %s SET topic_weight = topic_weight::integer - 1
                 WHERE prev = '%d'
                   AND topic_weight > '%d'
                   AND topic_weight <= '%d'";
      } else {
        $sql = "UPDATE %s SET topic_weight = topic_weight::integer + 1
                 WHERE prev = '%d'
                   AND topic_weight < '%d'
                   AND topic_weight >= '%d'";
      }
    } else {
      //move other topics away
      if ($direction > 0) {
        $sql = "UPDATE %s SET topic_weight = topic_weight - 1
                 WHERE prev = '%d'
                   AND topic_weight > '%d'
                   AND topic_weight <= '%d'";
      } else {
        $sql = "UPDATE %s SET topic_weight = topic_weight + 1
                 WHERE prev = '%d'
                   AND topic_weight < '%d'
                   AND topic_weight >= '%d'";
      }
    }

    $params = array(
      $this->tableTopics,
      $this->topic['prev'],
      $currentPosition,
      $newPosition
    );
    if (FALSE !== $this->databaseQueryFmtWrite($sql, $params)) {
      $data = array(
        'topic_weight' => $newPosition
      );
      $filter = array(
        'topic_id' => $this->topicId
      );
      if (FALSE !== $this->databaseUpdateRecord($this->tableTopics, $data, $filter)) {
        return $this->syncTablePositions($this->topic['prev']);
      }
    }
    return FALSE;
  }

  /**
   * Syncronize page positions in edit and public tables.
   * @param integer|NULL $topicPrev
   * @return boolean
   */
  function syncTablePositions($topicPrev = NULL) {
    if ($this->tableTopics != $this->tableTopicsPublic) {
      if (!empty($topicPrev)) {
        $sql = "UPDATE %s tp
                   SET topic_weight = (SELECT t.topic_weight
                                         FROM %s t
                                        WHERE t.prev = '%d' AND tp.topic_id = t.topic_id)
                 WHERE tp.topic_id IN (SELECT t.topic_id
                                         FROM %s t
                                        WHERE t.prev = '%d' AND tp.topic_id = t.topic_id)";
        $params = array(
          $this->tableTopicsPublic,
          $this->tableTopics,
          $topicPrev,
          $this->tableTopics,
          $topicPrev
        );
        if (FALSE != $this->databaseQueryFmtWrite($sql, $params)) {
          return TRUE;
        }
      } else {
        $sql = "UPDATE %s tp
                  SET topic_weight =
              (SELECT t.topic_weight
                 FROM %s t
                WHERE tp.topic_id = t.topic_id)";
        $params = array(
          $this->tableTopicsPublic,
          $this->tableTopics
        );
        if (FALSE != $this->databaseQueryFmtWrite($sql, $params)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Get public date
  *
  * @access public
  * @return mixed
  */
  function getPublicDate() {
    $sql = "SELECT topic_modified
              FROM %s
             WHERE topic_id = %d";
    $params = array($this->tableTopicsPublic, $this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return $row[0];
      }
    }
    return FALSE;
  }

  /**
  * Publish topic
  *
  * @access public
  * @return boolean
  */
  function publishTopic() {
    if (isset($this->topic) && is_array($this->topic)) {
      $created = $this->createVersion(
        $this->dialogPublish->data()->get('public_languages', array())
      );
      if ($created) {
        $sql = "SELECT COUNT(*)
                  FROM %s
                 WHERE topic_id = %d";
        $publishedFrom = $this->dialogPublish->data->get('published_from');
        $publishedTo = $this->dialogPublish->data->get('published_to');
        if ($publishedFrom <= 0) {
          $publishedFrom = $this->topic['published_from'];
        }
        if ($publishedTo <= 0) {
          $publishedTo = $this->topic['published_to'];
        }
        if ($publishedTo < $publishedFrom) {
          $publishedTo = $publishedFrom;
        }

        $data = array(
          'topic_weight' => $this->topic['topic_weight'],
          'topic_mainlanguage' => $this->topic['topic_mainlanguage'],
          'topic_modified' => $this->topic['topic_modified'],
          'topic_changefreq' => $this->topic['topic_changefreq'],
          'topic_priority' => $this->topic['topic_priority'],
          'topic_cachemode' => $this->topic['topic_cachemode'],
          'topic_cachetime' => $this->topic['topic_cachetime'],
          'topic_expiresmode' => $this->topic['topic_expiresmode'],
          'topic_expirestime' => $this->topic['topic_expirestime'],
          'topic_sessionmode' => $this->topic['topic_sessionmode'],
          'prev' => $this->topic['prev'],
          'prev_path' => $this->topic['prev_path'],
          'linktype_id' => (int)$this->topic['linktype_id'],
          'topic_protocol' => (int)$this->topic['topic_protocol'],
          'author_id' => $this->topic['author_id'],
          'meta_useparent' => (int)$this->topic['meta_useparent'],
          'box_useparent' => (int)$this->topic['box_useparent'],
          'surfer_useparent' => (int)$this->topic['surfer_useparent'],
          'surfer_permids' => $this->topic['surfer_permids'],
          'published_from' => (int)$publishedFrom,
          'published_to' => (int)$publishedTo
        );

        if ($res = $this->databaseQueryFmt($sql, array($this->tableTopicsPublic, $this->topicId))) {
          $row = $res->fetchRow();
          $topicCreated = $this->dialogPublish->data()->get(
            'topic_created', $this->topic['topic_created']
          );
          $data['topic_created'] = $topicCreated;
          if ($row[0] > 0) {
            $result = (
              FALSE !== $this->databaseUpdateRecord(
                $this->tableTopicsPublic, $data, 'topic_id', $this->topicId
              )
            );
          } else {
            $data['topic_id'] = $this->topicId;
            $result = (
              FALSE !== $this->databaseInsertRecord(
                $this->tableTopicsPublic, NULL, $data
              )
            );
          }
          if ($result) {
            $languages = $this->dialogPublish->data()->get('public_languages', array());
            if (count($languages) > 0) {
              $params = array('topic_id' => $this->topicId, 'lng_id' => $languages);
              if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsPublicTrans, $params)) {
                $filter = ' AND '.$this->databaseGetSQLCondition('t.lng_id', $languages);
                $sql = "INSERT INTO %s (topic_id, lng_id, topic_title,
                               topic_content, author_id, view_id,
                               topic_trans_created, topic_trans_modified,
                               topic_trans_checked,
                               meta_title, meta_keywords, meta_descr)
                        SELECT t.topic_id, t.lng_id, t.topic_title, t.topic_content,
                               t.author_id, t.view_id,
                               t.topic_trans_created, '%d', '%d',
                               t.meta_title, t.meta_keywords, t.meta_descr
                          FROM %s t
                         WHERE t.topic_id = %d $filter";
                $now = time();
                $params = array(
                  $this->tableTopicsPublicTrans,
                  $now,
                  $now,
                  $this->tableTopicsTrans,
                  $this->topicId
                );
                if (FALSE !== $this->databaseQueryFmtWrite($sql, $params)) {
                  $this->deleteCache();
                  $data['languages'] = $languages;
                  if (!isset($data['topic_id'])) {
                    $data['topic_id'] = $this->topicId;
                  }
                  $actionsConnector = $this
                    ->papaya()
                    ->plugins
                    ->get('79f18e7c40824a0f975363346716ff62');
                  if (is_object($actionsConnector)) {
                    $actionsConnector->call('default', 'onPublishPage', $data);
                  }
                  return TRUE;
                }
              }
            }
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * del public topic
  *
  * @access public
  * @return boolean
  */
  function deletePublicTopic() {
    $languages = [];
    $sql = "SELECT lng_id
              FROM %s
             WHERE topic_id = %d";
    $parameters = [
      $this->tableTopicsPublicTrans,
      $this->topicId
    ];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $languages[] = $row['lng_id'];
      }
    }
    if (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableTopicsPublicTrans, 'topic_id', $this->topicId
      )
    ) {
      if (!empty($languages)) {
        $actionsConnector = $this
          ->papaya()
          ->plugins
          ->get('79f18e7c40824a0f975363346716ff62');
        if (is_object($actionsConnector)) {
          foreach ($languages as $lngId) {
            $actionsConnector->call(
              'default',
              'onUnpublishPage',
              ['topic_id' => $this->topicId, 'lng_id' => $lngId]
            );
          }
        }
      }
      if (
        FALSE !== $this->databaseDeleteRecord($this->tableTopicsPublic, 'topic_id', $this->topicId)
      ) {
        $this->deleteCache();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Delete public topic translation
   *
   * @access public
   * @param null|integer $lngId
   * @return boolean
   */
  function deletePublicTopicTrans($lngId = NULL) {
    if (empty($lngId)) {
      if (empty($this->params['lng'])) {
        return FALSE;
      }
      $lngId = $this->params['lng'];
    }
    $condition = array('topic_id' => $this->topicId, 'lng_id' => $lngId);
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE topic_id = %d";
    if (FALSE !== $this->databaseDeleteRecord($this->tableTopicsPublicTrans, $condition)) {
      if (
        $res = $this->databaseQueryFmt($sql, array($this->tableTopicsPublicTrans, $this->topicId))
      ) {
        $row = $res->fetchRow();
        if ($row[0] == 0) {
          $this->deletePublicTopic();
        }
      }
      $this->deleteCache();
      $actionsConnector = $this
        ->papaya()
        ->plugins
        ->get('79f18e7c40824a0f975363346716ff62');
      if (is_object($actionsConnector)) {
        $actionsConnector->call(
          'default',
          'onUnpublishPage',
          ['topic_id' => $this->topicId, 'lng_id' => $lngId]
        );
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Delete language editing version of topic and public if exists
   *
   * @access public
   * @param integer $lngId
   * @return boolean
   */
  function deleteTrans($lngId) {
    $condition = array('topic_id' => $this->topicId, 'lng_id' => $lngId);
    if ($this->deletePublicTopicTrans($lngId) &&
        FALSE !== $this->databaseDeleteRecord($this->tableTopicsTrans, $condition)) {
      $this->deleteCache();
      return TRUE;
    }
    return FALSE;
  }


  /**
  * Load content/page modules
  *
  * @access public
  * @return mixed boolean FALSE or array
  */
  function loadContentList() {
    $result = FALSE;
    $sql = "SELECT module_guid, module_title, module_path,
                   module_file, module_class
              FROM %s
             WHERE module_type='page' AND module_active = 1
             ORDER BY module_title, module_guid";
    $params = array($this->tableModules);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['module_file'] = $row['module_path'].$row['module_file'];
        $result[$row["module_guid"]] = $row;
      }
    }
    return $result;
  }

  /**
  * Load views/stylsheets
  *
  * @return mixed
  * @access public
  */
  function loadViewList() {
    $result = FALSE;
    $sql = "SELECT view_title, view_id, module_guid
              FROM %s
             ORDER BY view_title, view_id";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableViews))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row["view_id"]] = $row;
      }
    }
    return $result;
  }

  /**
  * Load states of a page
  *
  * @return array
  * @access public
  */
  function loadStateList() {
    $result = array();
    foreach (base_statictables::getTableStates() as $key => $val) {
      $result[$key] = $this->_gt($val);
    }
    return $result;
  }

  /**
  * Load language list
  *
  * @access public
  */
  function loadLanguageList() {
    unset($this->languages);
    $sql = "SELECT lng_id, lng_title
              FROM %s
             WHERE is_content_lng = 1
             ORDER BY lng_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableLanguages)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->languages[$row['lng_id']] = $row['lng_title'];
      }
      $res->free();
    }
  }

  /**
  * Load versions list
  *
  * @access public
  */
  function loadVersionsList() {
    unset($this->versions);
    $this->versionsCount = 0;
    $sql = "SELECT t.version_id, t.version_time, t.version_author_id, t.version_message,
                   u.user_id, u.givenname, u.surname
              FROM %s t
              LEFT OUTER JOIN %s u ON t.version_author_id = u.user_id
             WHERE t.topic_id = %d
             ORDER BY t.version_time DESC, t.version_id DESC";
    $params = array($this->tableTopicsVersions, $this->tableAuthUser, (int)$this->topicId);
    $offset = empty($this->params['version_offset']) ? 0 : (int)$this->params['version_offset'];
    if ($res = $this->databaseQueryFmt($sql, $params, 10, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['fullname'] = $row['givenname'].' '.$row['surname'];
        $this->versions[$row["version_id"]] = $row;
      }
      $this->versionsCount = $res->absCount();
    }
  }

  /**
   * Load version
   *
   * @param integer $versionId
   * @param null|integer $topicId
   * @param integer $versionTime optional, default value 0
   * @access public
   * @return boolean
   */
  function loadVersion($versionId, $topicId = NULL, $versionTime = 0) {
    unset($this->savedVersion);
    if (isset($topicId) && $versionTime > 0) {
      $sql = "SELECT v.version_id, v.version_time, v.version_message, v.version_author_id,
                     v.topic_id, v.topic_mainlanguage, v.topic_weight,
                     v.topic_modified,
                     v.linktype_id, v.topic_protocol,
                     v.meta_useparent, v.box_useparent,
                     t.prev, t.prev_path,
                     u.user_id, u.givenname, u.surname
                FROM %s v
                LEFT OUTER JOIN %s u ON v.version_author_id = u.user_id
                LEFT OUTER JOIN %s t ON t.topic_id = v.topic_id
               WHERE v.topic_id = '%d' AND v.version_time <= %d
               ORDER BY v.version_time DESC, v.version_id DESC";
      $params = array(
        $this->tableTopicsVersions,
        $this->tableAuthUser,
        $this->tableTopics,
        $topicId,
        $versionTime
      );
    } else {
      $sql = "SELECT v.version_id, v.version_time, v.version_message, v.version_author_id,
                     v.topic_id, v.topic_mainlanguage, v.topic_weight,
                     v.topic_modified,
                     v.linktype_id, v.topic_protocol,
                     v.meta_useparent, v.box_useparent,
                     t.prev, t.prev_path,
                     u.user_id, u.givenname, u.surname
                FROM %s v
                LEFT OUTER JOIN %s u ON v.version_author_id = u.user_id
                LEFT OUTER JOIN %s t ON t.topic_id = v.topic_id
               WHERE v.version_id = '%d'";
      $params = array(
        $this->tableTopicsVersions,
        $this->tableAuthUser,
        $this->tableTopics,
        $versionId
      );
    }
    if ($res = $this->databaseQueryFmt($sql, $params, 1)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->savedVersion = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load version translated data
  *
  * @param integer $versionId
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadVersionTranslatedData($versionId, $lngId) {
    $result = FALSE;
    $sql = "SELECT t.version_published,
                   t.topic_id, t.topic_title, t.topic_content, t.lng_id,
                   t.meta_title, t.meta_keywords, t.meta_descr,
                   t.view_id, v.view_title, v.view_name,
                   m.module_guid, m.module_title, m.module_path,
                   m.module_file, m.module_class,
                   u.user_id, u.username, u.givenname, u.surname, u.group_id
              FROM %s t
              LEFT OUTER JOIN %s u ON t.author_id = u.user_id
              LEFT OUTER JOIN %s v ON v.view_id = t.view_id
              LEFT OUTER JOIN %s m ON m.module_guid = v.module_guid
             WHERE t.version_id = %d AND t.lng_id = %d";
    $params = array($this->tableTopicsVersionsTrans, $this->tableAuthUser,
      $this->tableViews, $this->tableModules, $versionId, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->savedVersion['TRANSLATION'] = $row;
        $result = TRUE;
      }
      $res->free();
    }
    return $result;
  }

  /**
  * Load version translations info
  *
  * @access public
  */
  function loadVersionTranslationsInfo() {
    if (isset($this->params['version_id']) && isset($this->savedVersion)
        && is_array($this->savedVersion) && isset($this->savedVersion['version_id'])) {
      unset($this->savedVersion['TRANSLATIONINFOS']);
      $sql = "SELECT ttv.topic_id, ttv.lng_id, ttv.version_published, ttv.topic_title
                FROM %s ttv
              WHERE ttv.version_id = %d";
      $params = array($this->tableTopicsVersionsTrans,
        (int)$this->savedVersion['version_id']);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->savedVersion['TRANSLATIONINFOS'][$row['lng_id']] = $row;
        }
      }
    }
  }

  /**
  * Remove old versions
  *
  * @access public
  * @return boolean
  */
  function removeOldVersions() {
    $sql = "SELECT version_time
              FROM %s
             WHERE topic_id = %d
             ORDER BY version_time DESC";
    $params = array($this->tableTopicsVersions, $this->topicId);
    $maxVersions = $this->papaya()->options->get(
      'PAPAYA_VERSIONS_MAXCOUNT', $this->maxVersions
    );
    if ($maxVersions > 0) {
      if ($res = $this->databaseQueryFmt($sql, $params, 1, $maxVersions)) {
        if ($row = $res->fetchRow()) {
          $border = $row[0];
          $sql = "SELECT version_id
                    FROM %s
                   WHERE topic_id = %d
                     AND version_time < '%d'";
          $params = array($this->tableTopicsVersions, $this->topicId, $border);
          if ($res = $this->databaseQueryFmt($sql, $params)) {
            $ids = array();
            while ($row = $res->fetchRow()) {
              $ids[] = (int)$row[0];
            }
            if (count($ids) > 0) {
              if (
                FALSE === $this->databaseDeleteRecord(
                  $this->tableTopicsVersionsTrans, array('version_id' => $ids)
                )
              ) {
                return FALSE;
              }
            }
            return FALSE !== $this->databaseDeleteRecord(
              $this->tableTopicsVersions, array('version_id' => $ids)
            );
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Deliver i-frame deklaration for output of content edit/preview
   *
   * @param string $fileName
   * @param string $caption
   * @param null $views
   * @param null $currentView
   * @return string
   * @access public
   */
  function getContentFrame($fileName, $caption, $views = NULL, $currentView = NULL) {
    $domains = $this->getVirtualDomains();
    $select = new PapayaUiToolbarSelect('tt/preview_domain', $domains);
    $current = 'http://'.PapayaUtilServerName::get();
    if (!in_array($current, $select->options)) {
      $select->defaultCaption = $current;
    }
    if (isset($this->params['preview_domain'])) {
      $this->papaya()->session->setValue('PAGE_PREVIEW_DOMAIN', (string)$this->params['preview_domain']);
    } elseif (isset($this->papaya()->session->values['PAGE_PREVIEW_DOMAIN'])) {
      $select->setCurrentValue($this->papaya()->session->values['PAGE_PREVIEW_DOMAIN']);
    }
    $buttons = new PapayaUiToolbarSelectButtons('tt/viewmode', $views);
    $buttons->setCurrentValue($currentView);

    $frame = new PapayaUiPanelFrame($caption, 'preview', '1400');
    $frame->toolbars->topLeft->elements[] = $select;
    $frame->toolbars->topRight->elements[] = $buttons;
    $frame->reference()->setRelative($fileName);
    return $frame->getXml();
  }

  /**
  * Load the virtual domains and build an key => value array with them.
  *
  * @return array(id => string)
  */
  function getVirtualDomains() {
    $domains = new PapayaContentDomains();
    $domains->load(array('mode' => PapayaContentDomain::MODE_VIRTUAL_DOMAIN));
    $result = array();
    $schemes = array(
      0 => 'http://',
      1 => 'http://',
      2 => 'https://',
    );
    foreach ($domains as $domain) {
      $scheme = isset($schemes[$domain['scheme']]) ? $schemes[$domain['scheme']] : $schemes[0];
      $value = $scheme.$domain['host'];
      $result[$value] = $value;
    }
    return $result;
  }

  /**
  * Edit section as XML
  *
  * @access public
  * @return mixed boolean FALSE or string xml form
  */
  function getEditContent() {
    $result = FALSE;
    $moduleData = $this->topic['TRANSLATION'];
    if (isset($moduleData['module_guid']) && $moduleData['module_guid'] != '') {
      $plugin = $this->papaya()->plugins->get($moduleData['module_guid'], $this);
      if ($plugin instanceof PapayaPluginEditable) {
        $plugin->content()->setXml($moduleData['topic_content']);
        $pluginNode = $this->layout->values()->getValueByPath('/page/centercol');
        if ($plugin->content()->editor()) {
          $plugin->content()->editor()->context()->merge(
            array(
              $this->paramName => array(
                'mode' => 1,
                'page_id' => $this->topicId,
                'lng_id' => $this->topic['TRANSLATION']['lng_id']
              )
            )
          );
          $pluginNode->append($plugin->content()->editor());
          if ($plugin->content()->modified()) {
            if ($this->saveContent($moduleData['module_guid'], $plugin->content()->getXml())) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            }
          }
        }
        return TRUE;
      } elseif ($plugin instanceof base_content) {
        $plugin->setData($moduleData['topic_content']);
        $plugin->layout = $this->layout;
        $plugin->initialize();
        $plugin->execute();
        $plugin->initializeDialog(
          array(
            'mode' => 1,
            'page_id' => $this->topicId,
            'lng_id' => $this->topic['TRANSLATION']['lng_id']
          )
        );
        if ($plugin->modified()) {
          if ($plugin->checkData()) {
            if ($this->saveContent($moduleData['module_guid'], $plugin->getData())) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            }
          }
        }
        $result = $plugin->getForm(
          $this->papaya()->administrationLanguage->title,
          $this->papaya()->administrationLanguage->image
        );
        if (!$result) {
          $this->addMsg(MSG_WARNING, 'No edit dialog for this module.');
        }
      }
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('No module/view selected.'));
    }
    return $result;
  }

  /**
  * Initialize properties dialog
  *
  * @access public
  */
  function initializePropertiesDialog() {
    $authUser = $this->papaya()->administrationUser;
    if (!(isset($this->dialogProperties) && is_object($this->dialogProperties))) {
      $hidden = array(
        'page_id' => $this->topicId,
        'cmd' => 'save_properties'
      );
      $data = array();
      $fields = array();
      if (isset($this->topic['TRANSLATION'])) {
        $data = $this->topic['TRANSLATION'];
        $fields['topic_title'] = array('Title', new PapayaFilterNotEmpty(), TRUE,
          'input', 400, '', 1);
        if ($authUser->hasPerm(Administration\Permissions::PAGE_METADATA_EDIT) &&
            !$this->topic['meta_useparent']) {
          $fields[] = 'Metatags';
          $fields['meta_title'] = array('Page Title', new PapayaFilterNotEmpty(), FALSE,
            'input', 400, '', '');
          $fields['meta_keywords'] = array('Keywords', new PapayaFilterNotEmpty(),
            FALSE, 'input', 400, '', '');
          $fields['meta_descr'] = array('Description', new PapayaFilterNotEmpty(),
            FALSE, 'textarea', 6, '', '');
        }
      }
      $fields[] = 'Language independent';
      $data['topic_mainlanguage'] = $this->topic['topic_mainlanguage'];
      $fields['topic_mainlanguage'] = array ('Default language',
        'isNum', TRUE, 'function', 'getContentLanguageCombo', '', 1);
      $data['linktype_id'] = $this->topic['linktype_id'];
      $fields['linktype_id'] = array('Linktype', 'isNum', TRUE, 'combo',
        $this->getLinkTypes(), '', 1);
      $data['topic_protocol'] = $this->topic['topic_protocol'];
      if (defined('PAPAYA_DEFAULT_PROTOCOL')) {
        switch(PAPAYA_DEFAULT_PROTOCOL) {
        case 2:
          $systemProtocol = $this->_gt('https');
          break;
        case 1:
          $systemProtocol = $this->_gt('http');
          break;
        default:
          $systemProtocol = $this->_gt('No specific protocol');
        }
      } else {
        $systemProtocol = $this->_gt('No specific protocol');
      }
      $fields['topic_protocol'] = array(
        'Protocol',
        'isNum',
        TRUE,
        'combo',
        array(
          0 => $this->_gt('System').' ('.$systemProtocol.')',
          1 => $this->_gt('http'),
          2 => $this->_gt('https')
        )
      );
      if ($authUser->hasPerm(Administration\Permissions::PAGE_METADATA_EDIT)) {
        $data['meta_useparent'] = !($this->topic['meta_useparent']);
        $fields['meta_useparent'] = array('Define Metatags', 'isNum', TRUE,
          'yesno', '', '', 1);
      }

      $fields[] = 'Sitemap options';
      $data['topic_changefreq'] = $this->topic['topic_changefreq'];
      $fields['topic_changefreq'] = array('Change frequency', 'isNum', TRUE, 'combo',
        base_statictables::getChangeFrequencyValues(), '', 2);
      $data['topic_priority'] = $this->topic['topic_priority'];
      $fields['topic_priority'] = array('Priority', 'isNum', TRUE, 'combo',
        base_statictables::getPriorityValues(), '', 50);
      $systemCachePages = defined('PAPAYA_CACHE_TIME_PAGES')
        ? (int)PAPAYA_CACHE_TIME_PAGES : 0;
      $cacheModesContent = array(
        0 => $this->_gt('No Cache'),
        1 => $this->_gt('System time').': '.$systemCachePages,
        2 => $this->_gt('Own time'),
      );
      $systemCacheBrowser = defined('PAPAYA_CACHE_TIME_BROWSER')
        ? (int)PAPAYA_CACHE_TIME_BROWSER : 0;
      $cacheModesBrowser = array(
        0 => $this->_gt('No Cache'),
        1 => $this->_gt('System time').': '.$systemCacheBrowser,
        2 => $this->_gt('Own time'),
      );
      $sessionMode = defined('PAPAYA_SESSION_ACTIVATION')
        ? (int)PAPAYA_SESSION_ACTIVATION : 1;
      $sessionModes = array(
        1 => $this->_gt('Always'),
        2 => $this->_gt('Never'),
        3 => $this->_gt('Dynamic'),
      );
      array_unshift($sessionModes, $this->_gt('System').': '.$sessionModes[$sessionMode]);
      $data['topic_cachemode'] = $this->topic['topic_cachemode'];
      $data['topic_cachetime'] = $this->topic['topic_cachetime'];
      $data['topic_expiresmode'] = $this->topic['topic_expiresmode'];
      $data['topic_expirestime'] = $this->topic['topic_expirestime'];
      $data['topic_sessionmode'] = $this->topic['topic_sessionmode'];
      if ($authUser->hasPerm(Administration\Permissions::PAGE_CACHE_CONFIGURE)) {
        $fields[] = 'Content Cache (Server)';
        $fields['topic_cachemode'] = array(
          'Mode', 'isNum', TRUE, 'combo', $cacheModesContent, '', 1
        );
        $fields['topic_cachetime'] = array('Time (seconds)', 'isNum', TRUE, 'input', 10, '', 0);
        $fields[] = 'Browser Cache';
        $fields['topic_expiresmode'] = array(
          'Mode', 'isNum', TRUE, 'combo', $cacheModesBrowser, '', 1
        );
        $fields['topic_expirestime'] = array('Time (seconds)', 'isNum', TRUE, 'input', 10, '', 0);
        $fields[] = 'Session';
        $fields['topic_sessionmode'] = array(
          'Mode', 'isNum', TRUE, 'combo', $sessionModes, '', 0
        );
      }
      $this->dialogProperties = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogProperties->loadParams();
      $this->dialogProperties->dialogTitle =
        $this->papaya()->administrationLanguage->title.' - '.$this->_gt('Properties');
      $this->dialogProperties->dialogIcon = $this->papaya()->administrationLanguage->image;
      $this->dialogProperties->dialogDoubleButtons = FALSE;
      $this->dialogProperties->textYes = 'Yes';
      $this->dialogProperties->textNo = 'No';
    }
  }

  /**
  * Get available link types list
  * @return array
  */
  function getLinkTypes() {
    $linkTypeObj = new base_linktypes;
    $minimal = TRUE;
    $linkTypes = $linkTypeObj->loadLinkTypes($minimal);
    if (is_array($linkTypes) && count($linkTypes) > 0) {
      return $linkTypes;
    } else {
      return array(1 => $this->_gt('visible'), 0 => $this->_gt('hidden'));
    }
  }

  /**
  * Change general data
  *
  * @access public
  * @return string
  */
  function getPropertiesDialog() {
    $this->initializePropertiesDialog();
    return $this->dialogProperties->getDialogXML();
  }

  /**
  * Get Page properties info dialog
  *
  * @access public
  * @return string
  */
  function getPropertiesReadOnly() {
    return '';
  }

  /**
  * edit permissions
  *
  * @access public
  * @return string xml
  */
  function getEditPerm() {
    $result = sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Edit permissions'))
    );
    $result .= '<cols>';
    $result .= '<col/>';
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Author'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Group'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('All'))
    );
    $result .= '</cols>';
    $result .= '<items>';
    $result .= $this->getEditPermElement($this->_gt('Read'), PERM_READ);
    $result .= $this->getEditPermElement($this->_gt('Write'), PERM_WRITE);
    $result .= $this->getEditPermElement($this->_gt('Create'), PERM_CREATE);
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * Get edit permission element
  *
  * @param string $caption
  * @param integer $perm
  * @access public
  * @return string
  */
  function getEditPermElement($caption, $perm) {
    $images = $this->papaya()->images;
    $result = sprintf(
      '<listitem title="%s">',
      papaya_strings::escapeHTMLChars($caption)
    );
    $result .= '<subitem align="center">';
    $authUser = $this->papaya()->administrationUser;
    if ($authUser->userId == $this->topic['author_id']) {
      $result .= sprintf(
        '<glyph src="%s"/>',
        papaya_strings::escapeHTMLChars(
          $this->hasPerm($perm, PERM_OWNER)
            ? $images['status-node-checked-disabled']
            : $images['status-node-empty-disabled']
        )
      );
    } else {
      $result .= sprintf(
        '<a href="%s"><glyph src="%s"/></a>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'chmod',
              'perm' => (int)$perm,
              'val' => $this->getPermValue($perm, PERM_OWNER)
            )
          )
        ),
        papaya_strings::escapeHTMLChars(
          $this->hasPerm($perm, PERM_OWNER)
            ? $images['status-node-checked']
            : $images['status-node-empty']
        )
      );
    }
    $result .= '</subitem>';
    $result .= '<subitem align="center">';
    if ($authUser->inGroup($this->topic['author_group']) &&
        $authUser->userId != $this->topic['author_id'] &&
        !$authUser->isAdmin()) {
      $result .= sprintf(
        '<glyph src="%s"/>',
        papaya_strings::escapeHTMLChars(
          $this->hasPerm($perm, PERM_GROUP)
            ? $images['status-node-checked-disabled']
            : $images['status-node-empty-disabled']
        )
      );
    } else {
      $result .= sprintf(
        '<a href="%s"><glyph src="%s"/></a>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'chmod',
              'perm' => (int)$perm,
              'val' => $this->getPermValue($perm, PERM_GROUP)
            )
          )
        ),
        papaya_strings::escapeHTMLChars(
          $this->hasPerm($perm, PERM_GROUP)
            ? $images['status-node-checked']
            : $images['status-node-empty']
        )
      );
    }
    $result .= '</subitem>';
    $result .= '<subitem align="center">';
    $result .= sprintf(
      '<a href="%s"><glyph src="%s"/></a>',
      papaya_strings::escapeHTMLChars(
        $this->getLink(
          array(
            'cmd' => 'chmod',
            'perm' => (int)$perm,
            'val' => $this->getPermValue($perm, PERM_ALL)
          )
        )
      ),
      papaya_strings::escapeHTMLChars(
        $this->hasPerm($perm, PERM_ALL)
          ? $images['status-node-checked']
          : $images['status-node-empty']
      )
    );
    $result .= '</subitem>';
    $result .= '</listitem>';
    return $result;
  }

  /**
  * Get edit user
  *
  * @param object papaya_user $editUser
  * @access public
  * @return string
  */
  function getEditUser($editUser) {
    $result = '';
    if (isset($editUser->users) && is_array($editUser->users) &&
        isset($editUser->groups) && is_array($editUser->groups)) {
      $result = sprintf(
        '<dialog title="%s" action="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Author')),
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="chown"/>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= '<lines class="dialogSmall">'.LF;
      $result .= sprintf(
        '<line caption="%s" align="left">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Group'))
      );
      $result .= sprintf(
        '<select name="%s[gid]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      foreach ($editUser->groups as $groupId => $group) {
        if ($groupId == $this->topic['author_group']) {
          $result .= sprintf(
            '<option value="%s" selected="selected">&raquo;%s&laquo;</option>'.LF,
            papaya_strings::escapeHTMLChars($groupId),
            papaya_strings::escapeHTMLChars($group['grouptitle'])
          );
        } else {
          $result .= sprintf(
            '<option value="%s">%s</option>'.LF,
            papaya_strings::escapeHTMLChars($groupId),
            papaya_strings::escapeHTMLChars($group['grouptitle'])
          );
        }
      }
      $result .= '</select>'.LF;
      $result .= '</line>'.LF;
      $result .= '<line caption="Autor">'.LF;
      $result .= sprintf(
        '<select name="%s[uid]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      foreach ($editUser->users as $aUser) {
        if ($aUser['user_id'] == $this->topic['author_id']) {
          $result .= sprintf(
            '<option value="%s" selected="selected">&raquo;%s (%s)&laquo;</option>'.LF,
            papaya_strings::escapeHTMLChars($aUser['user_id']),
            papaya_strings::escapeHTMLChars($aUser['fullname']),
            papaya_strings::escapeHTMLChars(
              $editUser->groups[(int)$aUser['group_id']]['grouptitle']
            )
          );
        } else {
          $result .= sprintf(
            '<option value="%s">%s (%s)</option>'.LF,
            papaya_strings::escapeHTMLChars($aUser['user_id']),
            papaya_strings::escapeHTMLChars($aUser['fullname']),
            papaya_strings::escapeHTMLChars(
              $editUser->groups[(int)$aUser['group_id']]['grouptitle']
            )
          );
        }
      }
      $result .= '</select>'.LF;
      $result .= '</line>'.LF;
      $result .= '</lines>'.LF;
      $result .= sprintf(
        '<dlgbutton value="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Change'))
      );
      $result .= '</dialog>'.LF;
    }
    return $result;
  }


  /**
  * List of modules and views
  *
  * @access public
  * @return string
  */
  function getEditView() {
    if (isset($this->topic['TRANSLATION'])) {
      $selectView = new base_selectview();
      if (preg_match_all('/\d+/', $this->topic['prev_path'], $regs)) {
        $pageIds = $regs[0];
      } else {
        $pageIds = array();
      }
      $pageIds[] = (int)$this->topic['prev'];
      $pageIds[] = (int)$this->topicId;
      $selectView->load(
        (int)$this->topic['TRANSLATION']['view_id'], 'page', array_unique($pageIds)
      );
      $selectView->actionLink = $this->getLink(
        array('cmd' => 'chg_view'),
        $this->paramName
      ).'&'.$this->paramName.'[view_id]=';
      return $selectView->getXMLViewList();
    } else {
      return '';
    }
  }

  /**
  * Check edit content module
  *
  * @access public
  * @return boolean
  */
  function checkEditView() {
    if (isset($this->params['view_id']) && $this->params['view_id'] > 0) {
      $selectView = new base_selectview();
      if ($selectView->loadView($this->params['view_id'])) {
        $dependency = $this->getDependencyBlocker()->dependency();
        if ($dependency->isOrigin($this->topicId)) {
          $pageViews = $this->getDependencyBlocker()->getSynchronizedViews(
            $this->topic['TRANSLATION']['lng_id']
          );
          foreach ($pageViews as $pageId => $view) {
            if ($view['module_id'] != $selectView->currentView['module_guid']) {
              $this->papaya()->messages->dispatch(
                new PapayaMessageDisplayTranslated(
                  PapayaMessage::SEVERITY_WARNING,
                  'Dependend page #%d uses a view with a different module and is not synced'.
                  ' automatically. Can not change view.',
                  array(
                    $pageId
                  )
                )
              );
              return FALSE;
            }
          }
        } elseif ($dependency->isDependency($this->topicId)) {
          $dependency->load($this->topicId);
          //check if new view is compatible to current view of origin
          if (($dependency->synchronization & \Papaya\Content\Page\Dependency::SYNC_VIEW) xor
              ($dependency->synchronization & \Papaya\Content\Page\Dependency::SYNC_CONTENT)) {
            // load view of origin page - new view module must be equal to module of origin page
            $originTranslation = new \Papaya\Content\Page\Translation();
            $originTranslation->load(
              array(
                'id' => $dependency->originId,
                'language_id' => $this->topic['TRANSLATION']['lng_id']
              )
            );
            if ($originTranslation->moduleGuid != $selectView->currentView['module_guid']) {
              $this->papaya()->messages->dispatch(
                new PapayaMessageDisplayTranslated(
                  PapayaMessage::SEVERITY_WARNING,
                  'The selected view is not compatible with the view of the origin page'.
                  ' Can not change view.'
                )
              );
              return FALSE;
            }
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Check edit content module
  *
  * @access public
  * @return boolean
  */
  function hasImportView() {
    if (isset($this->topic['TRANSLATION']['view_id']) &&
        $this->topic['TRANSLATION']['view_id'] > 0) {
      $importConf = new papaya_import();
      return $importConf->hasImportView($this->topic['TRANSLATION']['view_id']);
    }
    return FALSE;
  }

  /**
  * Get permission value
  *
  * @param integer $perm
  * @param integer $for optional, default value 0
  * @access public
  * @return integer
  */
  function getPermValue($perm, $for = 0) {
    $a = (int)$this->topic['author_perm'][$perm];
    if ($for > 0) {
      if ($this->hasPerm($perm, $for)) {
        $a = ($a & (~$for));
      } else {
        $a = ($a | $for);
      }
    }
    return $a;
  }

  /**
  * Set user
  *
  * @param integer $uid
  * @param integer $gid
  * @access public
  * @return boolean
  */
  function setUser($uid, $gid) {
    $sql = "SELECT user_id
              FROM %s
             WHERE user_id = '%s'";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableAuthUser, $uid))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $cGid = 0;
        $cUid = $row['user_id'];
        $sql = "SELECT group_id
                  FROM %s
                 WHERE group_id = '%d'";
        if ($gid == -1) {
          $cGid = -1;
        } elseif ($res = $this->databaseQueryFmt($sql, array($this->tableAuthGroups, $gid))) {
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $cGid = $row['group_id'];
          }
        }
        if ($cUid == $uid && $cGid == $gid && ($cGid > 0 || $cGid == -1)) {
          $data = array(
            'author_id' => $cUid,
            'author_group' => $cGid
          );
          if (
            FALSE !== $this->databaseUpdateRecord(
              $this->tableTopics, $data, 'topic_id', (int)$this->topicId
            )
          ) {
            $this->topic['author_id'] = $cUid;
            $this->topic['author_group'] = $cGid;
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Set permission value
  *
  * @param integer $perm
  * @param integer $value
  * @access public
  * @return mixed
  */
  function setPermValue($perm, $value) {
    $this->topic['author_perm'][$perm] = $value;
    $this->topic['author_perm'] = str_pad($this->topic['author_perm'], 3, 0);
    $data = array('author_perm' => $this->topic['author_perm']);
    return $this->databaseUpdateRecord(
      $this->tableTopics, $data, 'topic_id', (int)$this->topicId
    );
  }

  /**
  * Has permission
  *
  * @param integer $perm
  * @param integer $for
  * @access public
  * @return integer
  */
  function hasPerm($perm, $for) {
    return (((int)$this->topic['author_perm'][$perm]) & $for);
  }

  /**
  * Has permission user
  *
  * @param integer $perm
  * @param base_auth $user
  * @param mixed $topicId optional, default value NULL
  * @param mixed $permData optional, default value NULL
  * @access public
  * @return boolean
  */
  function hasPermUser($perm, $user, $topicId = NULL, $permData = NULL) {
    if (is_object($user)) {
      if ($user->isAdmin()) {
        return TRUE;
      }
      $s = 0;
      $authorId = '';
      $authorGroup = 0;
      if ((!isset($topicId)) || ($topicId == $this->topicId)) {
        if (isset($permData)) {
          $s = (int)$permData;
        } elseif (isset($this->topic['author_perm'][$perm])) {
          $s = (int)$this->topic['author_perm'][$perm];
        } else {
          $s = 0;
        }
        $authorId = $this->topic['author_id'];
        $authorGroup = $this->topic['author_group'];
      } elseif ($topicId > 0) {
        $sql = "SELECT t.topic_id, t.author_id, t.author_group,
                       t.author_perm, u.group_id
                  FROM %s t, %s u
                 WHERE t.topic_id = %d
                   AND u.user_id = t.author_id";
        $params = array($this->tableTopics, $this->tableAuthUser, (int)$topicId);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $s = (int)$row['author_perm'][$perm];
            $authorId = $row['author_id'];
            $authorGroup = ($row['author_group'] != 0) ?
              (int)$row['author_group'] : (int)$row['group_id'];
          }
        }
      }
      if ($user->userId == $authorId) {
        return ($s & PERM_OWNER);
      }
      if ($user->inGroup($authorGroup)) {
        return ($s & PERM_GROUP);
      }
      return ($s & PERM_ALL);
    }
    return FALSE;
  }

  /**
  * Change permission execute
  *
  * @access public
  */
  function changePermExecute($user) {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'chmod':
        if ($this->params['val'] > 0 && isset($this->params['perm'])) {
          $perm = (int)$this->params['perm'];
          $permVal = (int)$this->params['val'];
          $authUser = $this->papaya()->administrationUser;
          if ($this->hasPermUser($perm, $user) ==
                $this->hasPermUser($perm, $authUser, NULL, $permVal)) {
            if ($this->setPermValue($perm, $permVal)) {
              $this->addMsg(
                MSG_INFO,
                sprintf($this->_gt('%s modified.'), $this->_gt('Permissions'))
              );
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_PAGES,
                sprintf(
                  'Edit permissions of page "%s (%s)" changed.',
                  papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                  $this->topicId
                )
              );
            } else {
              $this->addMsg(
                MSG_WARNING,
                $this->_gt('Database error! Changes not saved.')
              );
            }
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('You can not change your own permissions.')
            );
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('Input error! Changes not saved.')
          );
        }
        break;
      case 'chown':
        if ($this->setUser($this->params['uid'], $this->params['gid'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%s modified.'), $this->_gt('Owner'))
          );
          $this->logMsg(
            MSG_INFO,
            PAPAYA_LOGTYPE_PAGES,
            sprintf(
              'Owner of page "%s (%s)" changed.',
              papaya_strings::escapeHTMLChars(
                $this->topic['TRANSLATION']['topic_title']
              ),
              $this->topicId
            )
          );
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('Database error! Changes not saved.')
          );
        }
        break;
      }
    }
  }

  /**
   * Change Box use parent - on deactivate fetch the current inherited box ids and insert them
   *
   * @access public
   * @param integer $mode
   * @return boolean
   */
  function saveBoxUseParent($mode) {
    $mode = (int)$mode;
    $deleteBoxLinks = in_array(
      $mode,
      array(base_boxeslinks::INHERIT_ALL, base_boxeslinks::INHERIT_BOXES)
    );
    $deleteGroupLinks = in_array(
      $mode,
      array(base_boxeslinks::INHERIT_ALL, base_boxeslinks::INHERIT_GROUPS)
    );
    $copyBoxLinks =
      in_array(
        $mode,
        array(base_boxeslinks::INHERIT_NONE, base_boxeslinks::INHERIT_GROUPS)
      ) &&
      in_array(
        $this->topic['box_useparent'],
        array(base_boxeslinks::INHERIT_ALL, base_boxeslinks::INHERIT_BOXES)
      );
    $copyGroupLinks =
      in_array(
        $mode,
        array(base_boxeslinks::INHERIT_NONE, base_boxeslinks::INHERIT_BOXES)
      ) &&
      in_array(
        $this->topic['box_useparent'],
        array(base_boxeslinks::INHERIT_ALL, base_boxeslinks::INHERIT_GROUPS)
      );

    if ($deleteBoxLinks) {
      $this->databaseDeleteRecord(
        $this->tableBoxesLinks,
        array(
          'topic_id' => $this->topicId,
          'boxgroup_id' => 0
        )
      );
    }
    if ($deleteGroupLinks) {
      $this->databaseDeleteRecord(
        $this->tableBoxesLinks,
        array(
          'topic_id' => $this->topicId,
          'box_id' => 0
        )
      );
    }
    $linkData = array();
    if ($copyBoxLinks) {
      $boxTopicId = $this->getBoxParent();
      $sql = "SELECT box_id, box_sort
                FROM %s
               WHERE topic_id = %d AND boxgroup_id = 0";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableBoxesLinks, $boxTopicId))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $linkData[] = array(
            'box_id' => $row['box_id'],
            'box_sort' => $row['box_sort'],
            'boxgroup_id' => 0,
            'topic_id' => $this->topicId
          );
        }
      }
    }
    if ($copyGroupLinks) {
      $boxTopicId = $this->getBoxGroupParent();
      $sql = "SELECT boxgroup_id
                FROM %s
               WHERE topic_id = %d AND box_id = 0";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableBoxesLinks, $boxTopicId))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $linkData[] = array(
            'box_id' => 0,
            'group_id' => $row['boxgroup_id'],
            'topic_id' => $this->topicId
          );
        }
      }
    }
    if (!empty($linkData)) {
      $this->databaseInsertRecords($this->tableBoxesLinks, $linkData);
    }
    $data = array(
      'box_useparent' => (int)$mode,
      'topic_modified' => time()
    );
    $this->topic['box_useparent'] = $mode;
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableTopics, $data, 'topic_id', $this->topicId
    );
  }

  /**
  * Get surfer permission list items
  *
  * @access public
  * @return string
  */
  function getSurferPermListItems() {
    $permsIds = $this->getSurferPermIDs();
    $result = FALSE;
    if (isset($permsIds) && is_array($permsIds)) {
      unset($ownPermIds);
      if ($this->topic['surfer_useparent'] == 3) {
        if (preg_match_all('/\d+/', $this->topic['surfer_permids'], $matches, PREG_PATTERN_ORDER)) {
          $ownPermIds = array_unique($matches[0]);
        }
      }
      $images = $this->papaya()->images;
      $filter = $this->databaseGetSQLCondition('surferperm_id', $permsIds);
      $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
                FROM %s
               WHERE $filter";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableSurferPerm))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['surferperm_active']) {
            $active = $this->_gt('Yes');
          } else {
            $active = $this->_gt('No');
          }

          if ($this->topic['surfer_useparent'] == 1) {
            $image = $images['items-permission'];
          } elseif (isset($ownPermIds) &&
                    is_array($ownPermIds) &&
                    in_array($row['surferperm_id'], $ownPermIds)) {
            $image = $images['items-permission'];
          } else {
            $image = $images['status-permission-inherited'];
          }
          $result .= sprintf(
            '<listitem title="%s" image="%s"><subitem>%s</subitem></listitem>',
            papaya_strings::escapeHTMLChars($row['surferperm_title']),
            papaya_strings::escapeHTMLChars($image),
            papaya_strings::escapeHTMLChars($active)
          );
        }
      }
    }
    if ($result) {
      $result = sprintf(
        '<listview title="%s" hint="%s" width="100%%"><cols>'.
        '<col>%s</col><col>%s</col></cols><items>%s</items></listview>',
        papaya_strings::escapeHTMLChars($this->_gt('Access permissions')),
        papaya_strings::escapeHTMLChars(
          $this->_gt(
            'Access to the page is restricted to registered visitors with the'.
            ' defined permissions. A page without any permission is available'.
            ' to all visitors.'
          )
        ),
        papaya_strings::escapeHTMLChars($this->_gt('Permission')),
        papaya_strings::escapeHTMLChars($this->_gt('Active')),
        $result
      );
      return $result;
    }
    return '';
  }

  /**
  * Get topic information
  *
  * @access public
  * @return string
  */
  function getTopicInformation() {
    $metaData = $this->loadMetaData();
    $this->loadTranslationsInfo();
    $boxesPageId = $this->getBoxesTopicID();
    $listview = new PapayaUiListview();
    $listview->caption = new PapayaUiStringTranslated('Information');
    $listview->items[] = $item = new PapayaUiListviewItem(
      'categories-properties', new PapayaUiStringTranslated('General')
    );
    $item->columnSpan = 2;
    $listview->items[] = $item = new PapayaUiListviewItem(
      '', new PapayaUiStringTranslated('Page title')
    );
    $item->indentation = 1;
    $item->subitems[] = new PapayaUiListviewSubitemText($metaData['meta_title']);
    $listview->items[] = $item = new PapayaUiListviewItem(
      '', new PapayaUiStringTranslated('Author')
    );
    $item->indentation = 1;
    $item->subitems[] = new PapayaUiListviewSubitemText(
      $this->topic['author_givenname'].' '.$this->topic['author_surname']
    );
    $listview->items[] = $item = new PapayaUiListviewItem(
      '', new PapayaUiStringTranslated('Boxes')
    );
    $item->indentation = 1;
    $item->subitems[] = new PapayaUiListviewSubitemText(
      ($boxesPageId == $this->topicId)
        ? new PapayaUiStringTranslated('own')
        : new PapayaUiStringTranslated('Page #%d', array($boxesPageId))
    );
    $listview->items[] = $item = new PapayaUiListviewItem(
      '', new PapayaUiStringTranslated('Created')
    );
    $item->indentation = 1;
    $item->subitems[] = new PapayaUiListviewSubitemDate((int)$this->topic['topic_created']);
    $listview->items[] = $item = new PapayaUiListviewItem(
      '', new PapayaUiStringTranslated('Modified')
    );
    $item->indentation = 1;
    $item->subitems[] = new PapayaUiListviewSubitemDate((int)$this->topic['topic_modified']);

    if ($this->topic['topic_published_created'] > 0) {
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Prepared')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemDate(
        (int)$this->topic['topic_published_created']
      );
    }

    if ($this->topic['published_from'] > 0 &&
        $this->topic['published_from'] < $this->topic['published_to']) {
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Published from')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemDate(
        (int)$this->topic['published_from']
      );
    }

    if ($this->topic['published_to'] > 0 &&
        $this->topic['published_from'] < $this->topic['published_to']) {
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Published to')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemDate(
        (int)$this->topic['published_to']
      );
    }
    if ($this->topic['published_from'] > 0 &&
        $this->topic['published_from'] == $this->topic['published_to']) {
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Published to')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemText(
        new PapayaUiStringTranslated('unlimited')
      );
    }
    foreach ($this->papaya()->languages as $languageId => $language) {
      if ($language['is_content'] || isset($this->topic['TRANSLATIONINFOS'][$languageId])) {
        $listview->items[] = $item = new PapayaUiListviewItem(
          './pics/language/'.$language['image'],
          $language['title'].' ('.$language['code'].')'
        );
        $item->columnSpan = 2;
        if (isset($this->topic['TRANSLATIONINFOS'][$languageId])) {
          $translation = $this->topic['TRANSLATIONINFOS'][$languageId];
          $listview->items[] = $item = new PapayaUiListviewItem(
            '', new PapayaUiStringTranslated('Title')
          );
          $item->indentation = 1;
          $item->subitems[] = new PapayaUiListviewSubitemText(
            $translation['topic_title']
          );
          $listview->items[] = $item = new PapayaUiListviewItem(
            '', new PapayaUiStringTranslated('View')
          );
          $item->indentation = 1;
          $item->subitems[] = new PapayaUiListviewSubitemText(
            $translation['view_title']
          );
          if (isset($translation['topic_trans_published'])) {
            if ($translation['topic_trans_published'] <
                $translation['topic_trans_modified']) {
              $listview->items[] = $item = new PapayaUiListviewItem(
                '', new PapayaUiStringTranslated('Status')
              );
              $item->indentation = 1;
              $item->subitems[] = new PapayaUiListviewSubitemText(
                new PapayaUiStringTranslated('modified')
              );
            } else {
              $listview->items[] = $item = new PapayaUiListviewItem(
                '', new PapayaUiStringTranslated('Status')
              );
              $item->indentation = 1;
              $item->subitems[] = new PapayaUiListviewSubitemText(
                new PapayaUiStringTranslated('published')
              );
            }
            $listview->items[] = $item = new PapayaUiListviewItem(
              '', new PapayaUiStringTranslated('Published')
            );
            $item->indentation = 1;
            $item->subitems[] = new PapayaUiListviewSubitemDate(
              (int)$translation['topic_trans_published']
            );
          } else {
            $listview->items[] = $item = new PapayaUiListviewItem(
              '', new PapayaUiStringTranslated('Status')
            );
            $item->indentation = 1;
            $item->subitems[] = new PapayaUiListviewSubitemText(
              new PapayaUiStringTranslated('created')
            );
          }
          $listview->items[] = $item = new PapayaUiListviewItem(
            '', new PapayaUiStringTranslated('Modified')
          );
          $item->indentation = 1;
          $item->subitems[] = new PapayaUiListviewSubitemDate(
            (int)$translation['topic_trans_modified']
          );
        } else {
          $listview->items[] = $item = new PapayaUiListviewItem(
            '', new PapayaUiStringTranslated('Status')
          );
          $item->indentation = 1;
          $item->subitems[] = new PapayaUiListviewSubitemText(
            new PapayaUiStringTranslated('no content')
          );
        }
      }
    }
    if (
         $this->papaya()->administrationUser->hasPerm(
           Administration\Permissions::PAGE_CACHE_CONFIGURE
         )
       ) {
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Marked As Cacheable')
      );
      $item->columnSpan = 2;
      if (isset($this->topic['TRANSLATIONINFOS']) && is_array($this->topic['TRANSLATIONINFOS'])) {
        $boxCacheStatus = $this->loadBoxViewCacheStatus($boxesPageId);
        foreach ($this->topic['TRANSLATIONINFOS'] as $languageId => $translation) {
          if (isset($this->papaya()->languages[$languageId])) {
            $language = $this->papaya()->languages[$languageId];
            $listview->items[] = $item = $aggregation = new PapayaUiListviewItem(
              './pics/language/'.$language['image'],
              $language['title'].' ('.$language['code'].')'
            );
            $item->indentation = 0;
            $cacheable = TRUE;
            if (!$translation['view_is_cacheable']) {
              $cacheable = FALSE;
              $listview->items[] = $item = new PapayaUiListviewItem(
                'categories-content',
                new PapayaUiStringTranslated('Content')
              );
              $item->indentation = 1;
              $item->subitems[] = new PapayaUiListviewSubitemText(
                $translation['view_title']
              );
            }
            if (isset($boxCacheStatus[$languageId])) {
              foreach ($boxCacheStatus[$languageId] as $boxStatus) {
                $cacheable = FALSE;
                $listview->items[] = $item = new PapayaUiListviewItem(
                  'status-box-published',
                  $boxStatus['box_name']
                );
                $item->indentation = 1;
                $item->subitems[] = new PapayaUiListviewSubitemText(
                  $boxStatus['view_title']
                );
              }
            }
            $aggregation->subitems[] = new PapayaUiListviewSubitemImage(
              $cacheable ? 'status-sign-ok' : 'status-sign-problem',
              new PapayaUiStringTranslated($cacheable ? 'Yes' : 'No')
            );
          }
        }
      }
    }
    return $listview->getXml();
  }

  function loadBoxViewCacheStatus($boxesPageId) {
    $databaseAccess = $this->getDatabaseAccess();
    $sql = "SELECT bp.box_id, bp.box_name, bpt.lng_id, bpt.view_id, v.view_title
              FROM %s pb, %s bp, %s bpt, %s v
             WHERE pb.topic_id = '%d'
               AND v.view_is_cacheable = 0
               AND bpt.box_id = pb.box_id
               AND bp.box_id = pb.box_id
               AND v.view_id = bpt.view_id";
    $parameters = array(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_BOXES),
      $databaseAccess->getTableName(PapayaContentTables::BOX_PUBLICATIONS),
      $databaseAccess->getTableName(PapayaContentTables::BOX_PUBLICATION_TRANSLATIONS),
      $databaseAccess->getTableName(PapayaContentTables::VIEWS),
      $boxesPageId
    );
    $result = array();
    if ($databaseResult = $databaseAccess->queryFmt($sql, $parameters)) {
      foreach ($databaseResult as $row) {
        $result[$row['lng_id']][$row['box_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * Get information
  *
  * @access public
  * @return string
  */
  function getInformation() {
    $result = $this->getTopicInformation();
    $result .= $this->getSurferPermListItems();
    return $result;
  }

  /**
  * Get delete form
  *
  * @access public
  * @return string
  */
  function getDelForm() {
    $hidden = array(
      'cmd' => 'del_topic',
      'page_id' => $this->topicId,
      'del_topic_confirm' => 1,
    );
    $title = (isset($this->topic['TRANSLATION']['topic_title'])) ?
      $this->topic['TRANSLATION']['topic_title'] : $this->_gt('No title');
    if ($this->topic['is_deleted']) {
      $msg = sprintf($this->_gt('Delete page "%s" (%s)?'), $title, (int)$this->topicId);
    } else {
      $msg = sprintf(
        $this->_gt('Move page "%s" (%s) to trash folder?'), $title, (int)$this->topicId
      );
    }
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->dialogTitle = $this->_gt('Delete page');
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get Versions list
  *
  * @access public
  * @return string
  */
  function getVersionsList() {
    if (isset($this->versions) && is_array($this->versions)) {
      $listview = new PapayaUiListview();
      $listview->caption = new PapayaUiStringTranslated('Versions');
      $listview->parameterGroup($this->paramName);

      $paging = new PapayaUiToolbarPaging(
        array($this->paramName, 'version_offset'),
        (int)$this->versionsCount,
        PapayaUiToolbarPaging::MODE_OFFSET
      );
      if (isset($this->params['version_offset']) && $this->params['version_offset'] > 0) {
        $paging->currentOffset = (int)$this->params['version_offset'];
      }
      $paging->itemsPerPage = 10;
      $paging->buttonLimit = 9;
      $paging->reference->setParameters(array('page_id' => $this->topicId), $this->paramName);
      $listview->toolbars->topLeft->elements[] = $paging;

      $listview->columns[] = new PapayaUiListviewColumn(
        new PapayaUiStringTranslated('Version time')
      );
      $listview->columns[] = new PapayaUiListviewColumn(
        new PapayaUiStringTranslated('User')
      );
      $listview->columns[] = new PapayaUiListviewColumn(
        '', PapayaUiOptionAlign::CENTER
      );
      $listview->columns[] = new PapayaUiListviewColumn(
        '', PapayaUiOptionAlign::CENTER
      );
      $listview->columns[] = new PapayaUiListviewColumn(
        '', PapayaUiOptionAlign::CENTER
      );
      foreach ($this->versions as $id => $version) {
        $listitem = new PapayaUiListviewItem(
          'items-page',
          new PapayaUiStringDate($version['version_time']),
          array(
            'page_id' => $this->topicId,
            'version_id' => $id,
            'version_offset' => $paging->currentOffset
          ),
          (isset($this->params['version_id']) && $id == $this->params['version_id'])
        );
        $listitem->text = PapayaUtilString::truncate(
          $version['version_message'], 100, FALSE, "\xE2\x80\xA6"
        );
        $listitem->subitems[] = new PapayaUiListviewSubitemText($version['fullname']);
        $listitem->subitems[] = new PapayaUiListviewSubitemImage(
          'actions-recycle',
          new PapayaUiStringTranslated('Recycle'),
          array(
            'cmd' => 'restore_version',
            'page_id' => $this->topicId,
            'version_id' => $id,
            'version_offset' => $paging->currentOffset
          )
        );
        $listitem->subitems[] = new PapayaUiListviewSubitemImage(
          'categories-preview',
          new PapayaUiStringTranslated('Preview'),
          array(
            'cmd' => 'chg_mode',
            'mode' => 5,
            'page_id' => $this->topicId,
            'version_datetime' => (int)$version['version_time'],
            'version_offset' => $paging->currentOffset
          )
        );
        $listitem->subitems[] = new PapayaUiListviewSubitemImage(
          'places-trash',
          new PapayaUiStringTranslated('Delete'),
          array(
            'cmd' => 'del_version',
            'version_id' => $version['version_id'],
            'page_id' => $this->topicId,
            'version_offset' => $paging->currentOffset
          )
        );
        $listview->items[] = $listitem;
      }
      return $listview->getXml();
    }
    return '';
  }

  /**
  * Get version infos
  *
  * @access public
  * @return string
  */
  function getVersionInfos() {
    if (isset($this->savedVersion) && is_array($this->savedVersion)) {
      $images = $this->papaya()->images;
      $yesno = array($this->_gt('No'), $this->_gt('Yes'));
      $linkTypes = $this->getLinkTypes();
      $result = sprintf(
        '<listview title="Infos [%s]" width="300">',
        date('Y-m-d H:i:s', $this->savedVersion['version_time'])
      );
      $result .= '<items>';
      $result .= sprintf(
        '<listitem title="%s" indent="0" span="2"></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Properties'))
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s %s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('User')),
        papaya_strings::escapeHTMLChars($this->savedVersion['givenname']),
        papaya_strings::escapeHTMLChars($this->savedVersion['surname'])
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Linktype')),
        papaya_strings::escapeHTMLChars($linkTypes[$this->savedVersion['linktype_id']])
      );
      switch ($this->savedVersion['topic_protocol']) {
      case 2 :
        $protocol = $this->_gt('System');
        break;
      case 1 :
        $protocol = $this->_gt('http');
        break;
      case 0 :
      default :
        $protocol = $this->_gt('https');
        break;
      }
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Protocol')),
        papaya_strings::escapeHTMLChars($protocol)
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Metatags')),
        papaya_strings::escapeHTMLChars($yesno[!$this->savedVersion['meta_useparent']])
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Sort')),
        papaya_strings::escapeHTMLChars($this->savedVersion['topic_weight'])
      );
      foreach ($this->papaya()->languages as $lngId => $lng) {
        if ($lng['image'] != '' &&
            file_exists($this->getBasePath(TRUE).'pics/language/'.$lng['image'])) {
          $image = sprintf(
            ' image="./pics/language/%s"',
            papaya_strings::escapeHTMLChars($lng['image'])
          );
        } else {
          $image = '';
        }
        $result .= sprintf(
          '<listitem title="%s"%s><subitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($lng['title']),
          $image,
          papaya_strings::escapeHTMLChars($lng['code'])
        );
        if (isset($this->savedVersion['TRANSLATIONINFOS']) &&
            isset($this->savedVersion['TRANSLATIONINFOS'][$lngId])) {
          $imageIdx =
            ($this->savedVersion['TRANSLATIONINFOS'][$lngId]['version_published'])
              ? 'status-page-published' : 'status-page-created';
          $result .= sprintf(
            '<listitem title="%s" indent="1" image="%s"><subitem>%s</subitem></listitem>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Title')),
            papaya_strings::escapeHTMLChars($images[$imageIdx]),
            papaya_strings::escapeHTMLChars(
              $this->savedVersion['TRANSLATIONINFOS'][$lngId]['topic_title']
            )
          );
        } else {
          $result .= sprintf(
            '<listitem title="%s" indent="2"><subitem>%s</subitem></listitem>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Status')),
            papaya_strings::escapeHTMLChars($this->_gt('No Content'))
          );
        }
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addRight($result);
      $result = sprintf(
        '<panel title="%s"><sheet width="100%%" align="center">'.
        '<text><div style="padding: 0px 5px 0px 5px;">%s</div></text></sheet></panel>',
        papaya_strings::escapeHTMLChars($this->_gt('Message')),
        papaya_strings::escapeHTMLChars($this->savedVersion['version_message'])
      );
      $this->layout->add($result);
    }
  }

  /**
  * Version execute
  *
  * @access public
  * @return string
  */
  function versionExecute() {
    $result = '';
    if (isset($this->params['cmd']) &&
        isset($this->savedVersion) &&
        is_array($this->savedVersion)) {
      switch ($this->params['cmd']) {
      case 'del_version' :
        if (isset($this->params['delconfirm']) && $this->params['delconfirm']) {
          if ($this->delVersion()) {
            $this->addMsg(MSG_INFO, $this->_gt('Version deleted.'));
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'Version of "%s (%s)" deleted.',
                papaya_strings::escapeHTMLChars(
                  $this->topic['TRANSLATION']['topic_title']
                ),
                (int)$this->topicId
              )
            );
            unset($this->savedVersion);
            unset($this->params['version_id']);
          }
        } else {
          $result = $this->getVersionDelForm();
        }
        break;
      case 'restore_version' :
        if (isset($this->params['restoreconfirm']) &&
            $this->params['restoreconfirm']) {
          if (isset($this->params['meta_info']) &&
              $this->params['meta_info'] == 'Yes') {
            if ($this->restoreVersion($this->params['version_id'])) {
              $this->addMsg(
                MSG_INFO,
                $this->_gt('Metainfo for version restored.')
              );
            }
          }
          if (isset($this->params['restore_languages']) &&
              is_array($this->params['restore_languages']) &&
              count($this->params['restore_languages']) != 0) {
            if (!isset($this->params['meta_info']) ||
                $this->params['meta_info'] != 'Yes') {
              if ($this->setTopicModified()) {
                $this->addMsg(
                  MSG_INFO,
                  $this->_gt('Modification date set.')
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Modification date couldn\'t be set.')
                );
              }
            }
            foreach ($this->params['restore_languages'] as $lngId) {
              if (!$this->restoreVersionTrans($this->params['version_id'], $lngId)) {
                $this->addMsg(
                  MSG_INFO,
                  sprintf($this->_gt('Version for language %s restored.'), $lngId)
                );
              }
            }
          }
        } else {
          $result = $this->getVersionRestoreForm();
        }
        break;
      }
    }
    return $result;
  }

  /**
  * Get version delete form
  *
  * @access public
  * @return string
  */
  function getVersionDelForm() {
    $hidden = array(
      'cmd' => 'del_version',
      'page_id' => $this->topicId,
      'version_id' => (int)$this->params['version_id'],
      'delconfirm' => 1
    );
    $msg = sprintf(
      $this->_gt('Delete version for "%s" from %s?'),
      $this->topic['TRANSLATION']['topic_title'],
      date('Y-m-d H:i:s', $this->savedVersion['version_time'])
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->dialogTitle = $this->_gt('Delete version');
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get version restore form
  *
  * @access public
  * @return string
  */
  function getVersionRestoreForm() {
    $hidden = array(
      'cmd' => 'restore_version',
      'page_id' => $this->topicId,
      'version_id' => (int)$this->params['version_id'],
      'restoreconfirm' => 1,
    );
    $data = array();
    $msg = sprintf(
      $this->_gt('Restore version for "%s" from %s?'),
      $this->topic['TRANSLATION']['topic_title'],
      date('Y-m-d H:i:s', $this->savedVersion['version_time'])
    );
    $fields = array(
      'meta_info' => array(
        '', 'isSomeText', FALSE, 'function', 'callbackLanguageIndependent', '', '', 'left'
      ),
      'Languages',
      'restore_languages' => array(
        '', 'isSomeText', FALSE, 'function',
        'callbackRestoreLanguages', '', '', 'left'
      )
    );
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $msg;
    $dialog->buttonTitle = 'Restore';
    $dialog->inputFieldSize = 'x-large';
    return $dialog->getDialogXML();
  }

  /**
  * Get XML for language independent checkbox control
  * @param string $name
  * @return string
  */
  function callbackLanguageIndependent($name) {
    $result = '';
    $result .= sprintf(
      '<input type="checkbox" class="dialogCheckbox" name="%s[%s]" value="Yes"/>%s'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars($this->_gt('Language independent information'))
    );
    return $result;
  }

  /**
  * Callback restore languages
  *
  * @param string $name
  * @access public
  * @return string
  */
  function callbackRestoreLanguages($name) {
    $result = '';
    $sql = 'SELECT lng_id
              FROM %s
             WHERE topic_id = %d
               AND version_id = %d';
    $params = array($this->tableTopicsVersionsTrans, (int)$this->topicId,
      $this->params['version_id']);
    $languages = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $lngId = $row[0];
        if (isset($this->papaya()->languages[$lngId])) {
          $languages[$lngId] = $this->papaya()->languages[$lngId];
        }
      }
    }
    foreach ($languages as $lngId => $lng) {
      $result .= sprintf(
        '<input type="checkbox" class="dialogCheckbox" name="%s[%s][]" value="%d"/>%s (%s)'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        (int)$lngId,
        papaya_strings::escapeHTMLChars($languages[$lngId]['title']),
        papaya_strings::escapeHTMLChars($languages[$lngId]['code'])
      );
    }
    return $result;
  }


  /**
  * Delete version
  *
  * @access public
  * @return mixed
  */
  function delVersion() {
    return $this->databaseDeleteRecord(
      $this->tableTopicsVersions, 'version_id', (int)$this->params['version_id']
    );
  }

  /**
   * Restore version
   *
   * @access public
   * @param integer $versionId
   * @return boolean
   */
  function restoreVersion($versionId) {
    $sql = "SELECT linktype_id,
                   topic_protocol,
                   meta_useparent,
                   box_useparent,
                   topic_mainlanguage,
                   topic_weight,
                   topic_changefreq,
                   topic_priority
              FROM %s
             WHERE topic_id = %d
               AND version_id = %d";
    $params = array($this->tableTopicsVersions, $this->topicId, $versionId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $data = array(
          'linktype_id' => $row['linktype_id'],
          'topic_protocol' => $row['topic_protocol'],
          'meta_useparent' => $row['meta_useparent'],
          'box_useparent' => $row['box_useparent'],
          'topic_mainlanguage' => $row['topic_mainlanguage'],
          'topic_weight' => $row['topic_weight'],
          'topic_changefreq' => $row['topic_changefreq'],
          'topic_priority' => $row['topic_priority']
        );
        $condition = array('topic_id' => $this->topicId);
        return FALSE !== $this->databaseUpdateRecord(
          $this->tableTopics, $data, $condition
        );
      }
    }
    return FALSE;
  }

  /**
   * Restore version translation
   *
   * @access public
   * @param integer $versionId
   * @param integer $lngId
   * @return boolean
   */
  function restoreVersionTrans($versionId, $lngId) {
    $sql = "SELECT topic_title, topic_content,
                   view_id, topic_trans_weight,
                   meta_title, meta_keywords,
                   meta_descr
              FROM %s
             WHERE topic_id = %d
               AND lng_id = %d
               AND version_id = %d";
    $params = array(
      $this->tableTopicsVersionsTrans,
      $this->topicId,
      $lngId,
      $versionId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $data = array(
          'topic_title' => $row['topic_title'],
          'topic_content' => $row['topic_content'],
          'view_id' => $row['view_id'],
          'topic_trans_weight' => $row['topic_trans_weight'],
          'meta_title' => $row['meta_title'],
          'meta_keywords' => $row['meta_keywords'],
          'meta_descr' => $row['meta_descr']
        );
        $condition = array('topic_id' => $this->topicId, 'lng_id' => $lngId);
        return FALSE !== $this->databaseUpdateRecord(
          $this->tableTopicsTrans, $data, $condition
        );
      }
    }
    return $res;
  }

  /**
  * Set topic modified
  *
  * @access public
  * @return boolean
  */
  function setTopicModified() {
    $data = array('topic_modified' => time());
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableTopics, $data, 'topic_id', (int)$this->topicId
    );
  }

  /**
   * Get public data
   *
   * @access public
   * @return string
   */
  function getPublicData() {
    $images = $this->papaya()->images;
    $result = sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Published'))
    );
    $result .= '<cols>';
    $result .= '<col/>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Modified'))
    );
    $result .= '<col/>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Published'))
    );
    $result .= '<col/>';
    $result .= '</cols>';
    $result .= '<items>';
    if ($pubDate = $this->getPublicDate()) {
      $pubDateStr = date('Y-m-d H:i:s', $pubDate);
    } else {
      $pubDateStr = '';
    }
    if ($this->topic['is_deleted']) {
      $imageIndex = 'status-page-deleted'; //deleted
      $imageHint = 'Deleted';
    } elseif (isset($this->topic['topic_published']) &&
              $this->topic['topic_published'] < $this->topic['topic_modified']) {
      $imageIndex = 'status-page-modified'; //published and modified
      $imageHint = 'Modified';
    } elseif (isset($this->topic['topic_published']) &&
              $this->topic['topic_published'] >= $this->topic['topic_modified']) {
      $imageIndex = 'status-page-published'; //published and up to date
      $imageHint = 'Published';
    } else {
      $imageIndex = 'status-page-created'; //created
      $imageHint = 'Created';
    }
    $result .= sprintf(
      '<listitem title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('General'))
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      date('Y-m-d H:i:s', $this->topic['topic_modified'])
    );
    $result .= sprintf(
      '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
      papaya_strings::escapeHTMLChars($images[$imageIndex]),
      papaya_strings::escapeHTMLChars($this->_gt($imageHint))
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      papaya_strings::escapeHTMLChars($pubDateStr)
    );
    if ($pubDate) {
      $result .= sprintf(
        '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array('cmd' => 'del_public', 'page_id' => $this->topicId)
          )
        ),
        papaya_strings::escapeHTMLChars($images['actions-publication-delete']),
        papaya_strings::escapeHTMLChars($this->_gt('Remove publication'))
      );
    } else {
      $result .= '<subitem/>';
    }
    $result .= '</listitem>';
    foreach ($this->papaya()->languages as $lngId => $lng) {
      if ($lng['image'] != '' &&
          file_exists($this->getBasePath(TRUE).'pics/language/'.$lng['image'])) {
        $image = sprintf(
          ' image="./pics/language/%s"',
          papaya_strings::escapeHTMLChars($lng['image'])
        );
      } else {
        $image = '';
      }
      $result .= sprintf(
        '<listitem title="%s" indent="1"%s>'.LF,
        papaya_strings::escapeHTMLChars($lng['title']),
        $image
      );
      if (isset($this->topic['TRANSLATIONINFOS']) &&
          isset($this->topic['TRANSLATIONINFOS'][$lngId])) {
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          date(
            'Y-m-d H:i:s',
            $this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_modified']
          )
        );
        if (isset($this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_published'])) {
          if ($this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_published'] >=
              $this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_modified']) {
            $result .= sprintf(
              '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
              papaya_strings::escapeHTMLChars($images['status-page-published']),
              papaya_strings::escapeHTMLChars($this->_gt('Published'))
            );
          } else {
            $result .= sprintf(
              '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
              papaya_strings::escapeHTMLChars($images['status-page-modified']),
              papaya_strings::escapeHTMLChars($this->_gt('Modified'))
            );
          }
          $result .= sprintf(
            '<subitem align="center">%s</subitem>'.LF,
            date(
              'Y-m-d H:i:s',
              $this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_published']
            )
          );
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'del_public_trans',
                  'lng' => $lngId,
                  'page_id' => $this->topicId
                )
              )
            ),
            papaya_strings::escapeHTMLChars($images['actions-publication-delete']),
            papaya_strings::escapeHTMLChars($this->_gt('Remove publication'))
          );
        } else {
          $result .= sprintf(
            '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
            papaya_strings::escapeHTMLChars($images['status-page-created']),
            papaya_strings::escapeHTMLChars($this->_gt('Created'))
          );
          $result .= '<subitem/>';
          $result .= '<subitem/>';
        }
      } else {
        $result .= '<subitem/><subitem/><subitem/><subitem/>'.LF;
      }
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * publish execute
  *
  * @access public
  * @return string
  */
  function publishExecute() {
    $result = '';
    if (isset($this->params['cmd'])) {
      $authUser = $this->papaya()->administrationUser;
      switch ($this->params['cmd']) {
      case 'handoff':
        //hand off form
        $this->initializeHandoffDialog();
        $this->dialogHandoff->loadParams();
        if (isset($this->params['handoff_confirm']) &&
            $this->params['handoff_confirm'] &&
          $this->dialogHandoff->checkDialogInput()) {
          $this->tasks()->params = array(
            'cmd' => 'handoff',
            'topic_id' => $this->topicId,
            'title' => $this->_gt('Check and Publish'),
            'priority' => 0,
            'status' => 0,
            'date_to' => 0,
            'user_id_from' => $authUser->userId,
            'user_id_to' => $this->params['user_id_to'],
            'comment' => $this->params['comment'],
          );
          if ($this->tasks()->createTodo()) {
            $this->addMsg(MSG_INFO, $this->_gt('Page forwarded.'));
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'Page "%s (%s)" forwarded.',
                papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                $this->topicId
              )
            );
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t handoff this page.'));
          }
        } else {
          $result = $this->getHandoffForm();
        }
        break;
      case 'publish':
        if ($authUser->hasPerm(Administration\Permissions::PAGE_PUBLISH)) {
          if (!isset($_POST['audit'])) {
            $this->initializePublishDialog();
            if (isset($this->params['publish_confirm']) &&
                $this->params['publish_confirm'] &&
                $this->dialogPublish->execute()) {
              $this->sessionParams['last_publish_message'] =
                $this->params['commit_message'];

              if ($this->getViewId() > 0) {
                if ($this->publishTopic()) {
                  $this->addMsg(MSG_INFO, $this->_gt('Page published.'));
                  $this->logMsg(
                    MSG_INFO,
                    PAPAYA_LOGTYPE_PAGES,
                    sprintf(
                      'Page "%s (%s)" published.',
                      papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                      $this->topicId
                    )
                  );
                  $this->sychronizations()->synchronizeAction(
                    \Papaya\Content\Page\Dependency::SYNC_PUBLICATION,
                    $this->topicId,
                    empty($this->params['public_languages'])
                      ? NULL : $this->params['public_languages']
                  );
                  if (defined('PAPAYA_PUBLISH_SOCIALMEDIA') && PAPAYA_PUBLISH_SOCIALMEDIA) {
                    $this->layout->addCenter($this->getSocialMediaDialogXml());
                  }
                } else {
                  $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t publish this page.'));
                }
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Couldn\'t publish this page: no view selected.')
                );
              }
            } else {
              $result = $this->getPublishForm();
            }
          } else {
            if ($this->addTopicAudit()) {
              $this->addMsg(
                MSG_INFO, $this->_gt('Page audited.')
              );
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_PAGES,
                sprintf(
                  'Page "%s (%s)" audited.',
                  papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                  $this->topicId
                )
              );
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t audit this page.'));
            }
          }
        }
        break;
      case 'del_public':
        if ($authUser->hasPerm(Administration\Permissions::PAGE_PUBLISH)) {
          if (isset($this->params['del_public_confirm']) && $this->params['del_public_confirm']) {
            if ($this->deletePublicTopic()) {
              $this->addMsg(MSG_INFO, 'Published page deleted.');
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_PAGES,
                sprintf(
                  'Published page "%s (%s)" deleted.',
                  papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                  $this->topicId
                )
              );
            } else {
              $this->addMsg(MSG_ERROR, 'Couldn\'t delete published page.');
            }
          } else {
            $result = $this->getDelPublicForm();
          }
        }
        break;
      case 'del_public_trans':
        if ($authUser->hasPerm(Administration\Permissions::PAGE_PUBLISH)) {
          if (isset($this->params['del_public_trans_confirm']) &&
              $this->params['del_public_trans_confirm']) {
            if ($this->deletePublicTopicTrans()) {
              $this->addMsg(MSG_INFO, 'Translation of published page deleted.');
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_PAGES,
                sprintf(
                  'Translation of published page "%s (%s)" deleted.',
                  papaya_strings::escapeHTMLChars($this->topic['TRANSLATION']['topic_title']),
                  $this->topicId
                )
              );
            } else {
              $this->addMsg(
                MSG_ERROR,
                'Couldn\'t delete translation of published page.'
              );
            }
          } else {
            $result = $this->getDelPublicTransForm();
          }
        }
        break;
      }
    }
    return $result;
  }

  /**
  * Social media execute
  *
  */
  public function socialMediaExecute() {
    if (isset($this->params['cmd']) && $this->params['cmd'] == 'social') {
      $twitter = $this->papaya()->plugins->get('3239c62be16c65bc389f45f95cfef6e8');
      if (!is_object($twitter)) {
        return;
      }
      $options = $twitter->getOptions();
      if (empty($options)) {
        $this->addMsg(MSG_ERROR, 'Cannot send tweets. Twitter account data not configured.');
        return;
      }
      $twitter->setConfiguration($options);
      $languages = isset($this->params['languages']) ?
        unserialize($this->params['languages']) :
        NULL;
      if (!empty($languages)) {
        $count = 0;
        $success = 0;
        foreach ($languages as $languageId) {
          if (isset($this->params['tweet_'.$languageId]) &&
            $this->params['tweet_'.$languageId] == TRUE &&
            isset($this->params['message_'.$languageId]) &&
            !empty($this->params['message_'.$languageId])) {
            $try = $twitter->update($this->params['message_'.$languageId]);
            $count++;
            $success += $try ? 1 : 0;
          }
        }
        if ($count > 0) {
          if ($success == $count) {
            $this->addMsg(MSG_INFO, sprintf('%d tweet(s) successfully sent.', $count));
          } else {
            $this->addMsg(
              MSG_ERROR,
              sprintf('Could not send %d out of %d tweet(s).', $count - $success)
            );
          }
        }
      }
    }
  }

  /**
  * Initialize publish form
  *
  * @access public
  */
  function initializePublishDialog() {
    if (!(isset($this->dialogPublish) && is_object($this->dialogPublish)) &&
        isset($this->topic['TRANSLATION'])) {
      $this->dialogPublish = $dialog = new PapayaUiDialog();
      $dialog->caption = new PapayaUiStringTranslated('Publish');
      $dialog->parameterGroup($this->paramName);
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => 'publish',
          'page_id' => $this->topicId,
          'id' => $this->topicId,
          'publish_confirm' => 1,
        )
      );
      $now = time();
      $dialog->data()->merge(
        array(
          'commit_message' => empty($this->sessionParams['last_publish_message'])
            ? '' : $this->sessionParams['last_publish_message'],
          'topic_created' =>
            (
             empty($this->topic['topic_published_created']) ||
             $this->topic['topic_published_created'] <= 0
            )
            ? $now : $this->topic['topic_published_created'],
          'published_from' =>
            (
             empty($this->topic['published_from']) ||
             $this->topic['published_from'] <= 0
            )
            ? $now : $this->topic['published_from'],
          'published_to' =>
            (
             empty($this->topic['published_to']) ||
             $this->topic['published_to'] <= 0
            )
            ? 0 : $this->topic['published_to'],
          'public_languages' => array_keys($this->topic['TRANSLATIONINFOS'])
        )
      );

      $dialog->fields[] = $group = new PapayaUiDialogFieldGroup(
        ''
      );
      $counter = $this->getDependencyBlocker()->counter();
      if ($counter->getDependencies() > 0 && $counter->getReferences() > 0) {
        $message = new PapayaUiStringTranslated(
          'Please be aware that this page has dependent and referenced pages.'
        );
      } elseif ($counter->getDependencies() > 0) {
        $message = new PapayaUiStringTranslated(
          'Please be aware that this page has dependent pages.'
        );
      } elseif ($counter->getReferences() > 0) {
        $message = new PapayaUiStringTranslated(
          'Please be aware that this page has referenced pages.'
        );
      } else {
        $message = NULL;
      }
      if ($message) {
        $group->fields[] = new PapayaUiDialogFieldInformation(
          $message, 'items-publication'
        );
      }
      $group->fields[] = $field = new PapayaUiDialogFieldInput(
        new PapayaUiStringTranslated('message'),
        'commit_message',
        200,
        '',
        new PapayaFilterNotEmpty()
      );
      $field->setMandatory(TRUE);
      if ($this->papaya()->options->get('PAPAYA_PUBLICATION_CHANGE_LEVEL', FALSE)) {
        $group->fields[] = new PapayaUiDialogFieldSelect(
          new PapayaUiStringTranslated('Change level'),
          'change_level',
          base_statictables::getChangeLevels()
        );
      }
      $group->fields[] = new PapayaUiDialogFieldInputTimestamp(
        new PapayaUiStringTranslated('Created'),
        'topic_created',
        NULL,
        TRUE,
        PapayaFilterDate::DATE_MANDATORY_TIME
      );
      $dialog->fields[] = $group = new PapayaUiDialogFieldGroup(
        new PapayaUiStringTranslated('Publication period')
      );
      $group->fields[] = new PapayaUiDialogFieldInputTimestamp(
        new PapayaUiStringTranslated('Published from'),
        'published_from',
        time(),
        TRUE,
        PapayaFilterDate::DATE_MANDATORY_TIME
      );
      $group->fields[] = new PapayaUiDialogFieldInputTimestamp(
        new PapayaUiStringTranslated('Published to'),
        'published_to',
        0,
        FALSE,
        PapayaFilterDate::DATE_MANDATORY_TIME
      );
      $dialog->fields[] = $group = new PapayaUiDialogFieldGroup(
        new PapayaUiStringTranslated('Languages')
      );
      $group->fields[] = new PapayaUiDialogFieldSelectCheckboxes(
        new PapayaUiStringTranslated('Languages'),
        'public_languages',
        PapayaUtilArrayMapper::byIndex(
          iterator_to_array($this->papaya()->administrationLanguage->languages()),
          'title'
        )
      );

      if ($this->papaya()->options->get('PAPAYA_PUBLICATION_AUDITING', FALSE)) {
        $dialog->buttons[] = new PapayaUiDialogButtonSubmitNamed(
          new PapayaUiStringTranslated('Audited'), 'audit', 1, PapayaUiDialogButton::ALIGN_LEFT
        );
      }
      $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
        new PapayaUiStringTranslated('Publish')
      );
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get publish form
  *
  * @access public
  * @return string
  */
  function getPublishForm() {
    $this->initializePublishDialog();
    if (isset($this->dialogPublish) && is_object($this->dialogPublish)) {
      return $this->dialogPublish->getXml();
    }
    return '';
  }

  /**
  * Get social media form
  *
  * @return string
  */
  function getSocialMediaDialogXml() {
    $this->initializeSocialMediaDialog();
    if (isset($this->dialogSocialMedia) && is_object($this->dialogSocialMedia)) {
      return $this->dialogSocialMedia->getXml();
    }
    return '';
  }

  /**
  * Initialize social media dialog
  */
  function initializeSocialMediaDialog() {
    $languages = $this->dialogPublish->data()->get('public_languages', array());
    $connector = $this->papaya()->plugins->get('3239c62be16c65bc389f45f95cfef6e8');
    if (is_object($connector)) {
      $this->dialogSocialMedia = new PapayaUiDialog();
      $this->dialogSocialMedia->caption = new PapayaUiStringTranslated('Social media');
      $this->dialogSocialMedia->parameterGroup($this->paramName);
      $this->dialogSocialMedia->hiddenFields()->merge(
        array(
          'cmd' => 'social',
          'page_id' => $this->topicId,
          'id' => $this->topicId,
          'languages' => serialize($languages)
        )
      );
      foreach ($languages as $languageId) {
        $languageName = $this
          ->papaya()
          ->languages
          ->getLanguage($languageId)
          ->title;
        $languageIdentifier = $this
          ->papaya()
          ->languages
          ->getLanguage($languageId)
          ->identifier;
        $this->dialogSocialMedia->fields[] =
          new PapayaUiDialogFieldInputCheckbox(
            new PapayaUiStringTranslated(
              'Send tweet (%s)',
              array($languageName)
            ),
            'tweet_'.$languageId
          );
        $pageTitle = $this->getShortTitle($this->topicId, $languageId);
        if (!empty($pageTitle)) {
          $message = new PapayaUiStringTranslated(
            'New page "%s" out: %s',
            array(
              $pageTitle,
              $this->getShortWebLink($this->topicId, $languageIdentifier)
            )
          );
        } else {
          $message = new PapayaUiStringTranslated(
            'New page out: %s',
            array(
              $this->getShortWebLink($this->topicId, $languageIdentifier)
            )
          );
        }
        $this->dialogSocialMedia->fields[] =
          new PapayaUiDialogFieldInput(
            'Message',
            'message_'.$languageId,
            140,
            $message
          );
      }
      $this->dialogSocialMedia->buttons[] = new PapayaUiDialogButtonSubmit(
        new PapayaUiStringTranslated('Send tweets')
      );
    }
  }

  /**
  * Get a shortened link using the link shortener connector
  *
  * @param integer $topicId
  * @param string $languageIdentifier
  * @return string
  */
  function getShortWebLink($topicId, $languageIdentifier) {
    $reference = $this->papaya()->pageReferences->get($languageIdentifier, $topicId);
    $reference->setPreview(FALSE);
    $link = $reference->get();
    $shortener = $this->papaya()->plugins->get('6451e8560ad3c880d9ba17075d0a408d');
    if (is_object($shortener)) {
      $link = $shortener->getShort($link);
    }
    return $link;
  }

  /**
  * Get the page title; shorten if necessary
  *
  * @param integer $topicId
  * @param string $languageIdentifier
  * @return string
  */
  function getShortTitle($topicId, $languageId) {
    $pages = new \Papaya\Content\Pages\Publications();
    $pages->load(array('id' => array($topicId), 'language_id' => $languageId));
    $pageTitles = PapayaUtilArrayMapper::byIndex($pages, 'title');
    $pageTitle = isset($pageTitles[$topicId]) ? $pageTitles[$topicId] : '';
    if (strlen($pageTitle) > 60) {
      $pageTitle = substr($pageTitle, 0, 57)."...";
    }
    return $pageTitle;
  }

  /**
   * Initialize publish form
   *
   * @access public
   */
  function initializeHandoffDialog() {
    if (!(isset($this->dialogHandoff) && is_object($this->dialogHandoff)) &&
      isset($this->topic['TRANSLATION'])) {
      $group = NULL;
      $authUser = $this->papaya()->administrationUser;
      if ($authUser->user['handoff_group_id'] != 0) {
        $group = $authUser->user['handoff_group_id'];
      }
      $hidden = array(
        'cmd' => 'handoff',
        'topic_id' => $this->topicId,
        'page_id' => $this->topicId,
        'handoff_confirm' => 1,
      );

      $fields = array(
        'user_id_to' => array(
          'User', 'isSometext', TRUE, 'combo', $this->tasks()->getUserList($group), '', ''
        ),
        'comment' => array('Comment', 'isSometext', FALSE, 'input', 30, '', '')
      );
      $data = array();
      $this->dialogHandoff = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogHandoff->dialogTitle = $this->_gt('Handoff to');
      $this->dialogHandoff->buttonTitle = 'Handoff page';
      $this->dialogHandoff->inputFieldSize = 'x-large';
    }
  }

  /**
   * @param papaya_todo $tasks
   * @return papaya_todo
   */
  public function tasks(papaya_todo $tasks = NULL) {
    if (isset($tasks)) {
      $this->_tasks = $tasks;
    } elseif (NULL === $this->_tasks) {
      $this->_tasks = new papaya_todo();
      $this->_tasks->papaya($this->papaya());
    }
    return $this->_tasks;
  }

  /**
  * Get hendof form
  *
  * @access public
  * @return string
  */
  function getHandoffForm() {
    $this->initializeHandoffDialog();
    if (isset($this->dialogHandoff) && is_object($this->dialogHandoff)) {
      return $this->dialogHandoff->getDialogXML();
    }
    return '';
  }

  /**
  * Callback publish languages
  *
  * @param string $name
  * @param array $element
  * @param array $data
  * @access public
  * @return string $result
  */
  function callbackPublishLanguages($name, $element, $data) {
    $result = '';
    foreach ($this->papaya()->languages as $lngId => $lng) {
      if (isset($this->topic['TRANSLATIONINFOS']) &&
          isset($this->topic['TRANSLATIONINFOS'][$lngId])) {
        if (is_array($data) && in_array($lngId, $data)) {
          $selected = ' checked="checked"';
        } elseif ((!is_array($data)) && $this->papaya()->administrationLanguage->id == $lngId) {
          $selected = ' checked="checked"';
        } else {
          $selected = '';
        }
        if (isset($this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_published']) &&
              $this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_published'] >=
              $this->topic['TRANSLATIONINFOS'][$lngId]['topic_trans_modified']) {
          $disabled = ' disabled="disabled"';
        } else {
          $disabled = '';
        }
        $result .= sprintf(
          '<input type="checkbox" name="%s[%s][]" value="%d" %s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          (int)$lngId,
          $selected,
          $disabled
        );
        $result .=
          papaya_strings::escapeHTMLChars($this->papaya()->languages[$lngId]['title']).' ('.
          papaya_strings::escapeHTMLChars($this->papaya()->languages[$lngId]['code']).')';
      }
    }
    return $result;
  }


  /**
  * Get delete public form
  *
  * @access public
  * @return string
  */
  function getDelPublicForm() {
    $hidden = array(
      'cmd' => 'del_public',
      'page_id' => $this->topicId,
      'del_public_confirm' => 1,
    );
    $msg = sprintf(
      $this->_gt('Delete published page "%s" (%s)?'),
      $this->topic['TRANSLATION']['topic_title'],
      (int)$this->topicId
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }
  /**
  * Get delete translated form
  *
  * @access public
  * @return string
  */
  function getDelTransForm() {
    if (isset($this->topic['TRANSLATION']) &&
        is_array($this->topic['TRANSLATION']) &&
        count($this->topic['TRANSLATION']) > 0) {
      $hidden = array(
        'cmd' => 'del_trans',
        'page_id' => $this->topicId,
        'del_trans_confirm' => 1,
      );
      $data = array();
      if (isset($this->topic['TRANSLATION']) &&
          $this->topic['TRANSLATION']['topic_title'] == '') {
        $topicTitle = $this->topic['TRANSLATION']['topic_title'];
      } else {
        $topicTitle = $this->_gt('No title');
      }
      $dialogTitle = sprintf(
        $this->_gt('Delete language versions of page "%s" (%s)?'),
        $topicTitle,
        (int)$this->topicId
      );
      $fields = array(
        'del_trans_language' => array('Languages', 'isSomeText', FALSE,
          'function', 'callbackDeleteLanguages', '', '', 'left')
      );
      $dialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $dialog->dialogTitle = $dialogTitle;
      $dialog->buttonTitle = 'Delete';
      $dialog->inputFieldSize = 'medium';
      return $dialog->getDialogXML();
    }
    return '';
  }
  /**
  * Call back delete languages
  *
  * @param string $name
  * @access public
  * @return string
  */
  function callbackDeleteLanguages($name) {
    $result = '';
    $sql = 'SELECT lng_id FROM %s WHERE topic_id=%d';
    $params = array($this->tableTopicsTrans, (int)$this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $lngId = $row[0];
        $languages[$lngId] = $this->papaya()->languages[$lngId];
      }
    }
    if (isset($languages) && is_array($languages)) {
      foreach ($languages as $lngId => $lng) {
        $result .= sprintf(
          '<input type="checkbox" class="dialogCheckbox" name="%s[%s][]" value="%d"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          (int)$lngId
        );
        $result .= papaya_strings::escapeHTMLChars($lng['title']).' ('.
          papaya_strings::escapeHTMLChars($lng['code']).')';
      }
    }
    return $result;
  }


  /**
  * Get delete public translation form
  *
  * @access public
  * @return string
  */
  function getDelPublicTransForm() {
    $hidden = array(
      'cmd' => 'del_public_trans',
      'page_id' => $this->topicId,
      'lng' => $this->params['lng'],
      'del_public_trans_confirm' => 1,
    );
    if (isset($this->topic['TRANSLATION']) &&
        $this->topic['TRANSLATION']['topic_title'] != '') {
      $topicTitle = $this->topic['TRANSLATION']['topic_title'];
    } else {
      $topicTitle = $this->_gt('No title');
    }
    $msg = sprintf(
      $this->_gt('Delete published translation of page "%s" (%s)?'),
      $topicTitle,
      (int)$this->topicId
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Editable
  *
  * @param base_auth $user
  * @access public
  * @return boolean
  */
  function editable($user) {
    if (($user->hasPerm(Administration\Permissions::PAGE_MANAGE) || $user->isAdmin()) &&
        (
         $this->getLevel($user->startNode) <= $user->subLevel ||
         $user->subLevel == 0
        )
       ) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Add topic ID dialog
  *
  * @access public
  */
  function addTopicIdDialog() {
    $dialog = new PapayaUiDialog($this);
    $dialog->caption = new PapayaUiStringTranslated('Go to page');
    $dialog->parameterGroup($this->paramName);
    $dialog->options()->useToken = FALSE;
    $dialog->options()->captionStyle = PapayaUiDialogOptions::CAPTION_NONE;
    $dialog->options()->protectChanges = FALSE;
    $dialog
      ->fields()
      ->add(
        new PapayaUiDialogFieldInputPage(
          new PapayaUiStringTranslated('Page Id'),
          'page_id',
          $this->topicId,
          TRUE
        )
      );
    $dialog
      ->buttons()
      ->add(
        new PapayaUiDialogButtonSubmit(
          new PapayaUiStringTranslated('GoTo')
        )
      );
    $this->layout->addLeft($dialog->getXml());
  }

  /**
  * Get versions preview list
  *
  * @access public
  * @return string $result xml
  */
  function getVersionsPreviewList() {
    $result = '';
    $this->loadVersionsList();
    $images = $this->papaya()->images;
    $result .= sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Versions'))
    );
    $result .= papaya_paging_buttons::getPagingButtons(
      $this,
      array('page_id' => $this->topicId),
      empty($this->params['version_offset']) ? 0 : (int)$this->params['version_offset'],
      10,
      $this->versionsCount,
      5,
      'version_offset'
    );
    $result .= '<items>';
    if (empty($this->params['version_datetime']) || $this->params['version_datetime'] <= 0) {
      $selected = ' selected="selected"';
      $haveSelect = TRUE;
    } else {
      $selected = '';
      $haveSelect = FALSE;
    }
    $href = $this->getLink(array('page_id' => $this->topicId, 'version_datetime' => 0));
    $result .= sprintf(
      '<listitem title="%s (%s)" image="%s" href="%s"%s><subitem/></listitem>',
      date('Y-m-d H:i:s', $this->topic['topic_modified']),
      papaya_strings::escapeHTMLChars($this->_gt('Current')),
      papaya_strings::escapeHTMLChars($images['status-page-created']),
      papaya_strings::escapeHTMLChars($href),
      $selected
    );
    if (isset($this->versions) && is_array($this->versions)) {
      foreach ($this->versions as $id => $version) {
        if ((!$haveSelect) &&
            isset($this->params['version_datetime']) &&
            $version['version_time'] <= $this->params['version_datetime']) {
          $selected = ' selected="selected"';
          $haveSelect = TRUE;
        } else {
          $selected = '';
        }
        $params = array(
          'page_id' => (int)$this->topicId,
          'version_datetime' => (int)$version['version_time'],
          'version_offset' => empty($this->params['version_offset'])
            ? 0 : (int)$this->params['version_offset']
        );
        $result .= sprintf(
          '<listitem title="%s" image="%s" href="%s"%s>',
          date('Y-m-d H:i:s', $version['version_time']),
          papaya_strings::escapeHTMLChars($images['status-page-published']),
          papaya_strings::escapeHTMLChars($this->getLink($params)),
          $selected
        );
        $params = array(
          'page_id' => (int)$this->topicId,
          'version_id' => (int)$id,
          'cmd' => 'restore_version',
          'mode' => 7);
        $result .= sprintf(
          '<subitem align="right"><a href="%s"><glyph src="%s" title="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars($this->getLink($params)),
          papaya_strings::escapeHTMLChars($images['actions-recycle']),
          papaya_strings::escapeHTMLChars($this->_gt('Restore'))
        );
        $result .= '</listitem>';
      }
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * Add version preview dialog
  *
  * @access public
  */
  function addVersionPreviewDialog() {
    $result = $this->getVersionsPreviewList();
    $result .= sprintf(
      '<dialog action="%s" title="%s" width="250" name="selectPreviewVersion">',
      papaya_strings::escapeHTMLChars($this->baseLink),
      papaya_strings::escapeHTMLChars($this->_gt('Select Version'))
    );
    $result .= '<lines class="dialogSmall">';
    $selectionTime = empty($this->params['version_datetime'])
      ? time() : $this->params['version_datetime'];
    $result .= sprintf(
      '<line caption="%s"><input type="text" class="dialogInput dialogScale"'.
        ' name="%s[version_date]" maxlength="10" value="%s"/></line>',
      papaya_strings::escapeHTMLChars($this->_gt('Date')),
      papaya_strings::escapeHTMLChars($this->paramName),
      date('Y-m-d', $selectionTime)
    );
    $result .= sprintf(
      '<line caption="%s"><input type="text" class="dialogInput dialogScale"'.
        ' name="%s[version_time]" maxlength="10" value="%s"/></line>',
      papaya_strings::escapeHTMLChars($this->_gt('Time')),
      papaya_strings::escapeHTMLChars($this->paramName),
      date('H:i:s', $selectionTime)
    );
    $result .= '</lines>';
    $result .= sprintf(
      '<dlgbutton value="%s" />',
      papaya_strings::escapeHTMLChars($this->_gt('Preview'))
    );
    $result .= '</dialog>';
    $this->layout->addLeft($result);
  }

  /**
  * Initialie object for add translation dialog
  * @return void
  */
  function initializeAddTranslationDialog() {
    if (!(isset($this->dialogAddTranslation) && is_object($this->dialogAddTranslation))) {
      $this->loadTranslationsInfo();
      $hidden = array(
        'cmd' => 'add_translation',
        'page_id' => $this->topicId,
        'lng_id' => $this->papaya()->administrationLanguage->id
      );
      $data = array();
      if (isset($this->topic['TRANSLATIONINFOS']) &&
          is_array($this->topic['TRANSLATIONINFOS'])) {
        $exists = array_key_exists(
          $this->topic['topic_mainlanguage'], $this->topic['TRANSLATIONINFOS']
        );
        if ($exists) {
          $data['copy_lng_id'] = $this->topic['topic_mainlanguage'];
        } else {
          foreach ($this->topic['TRANSLATIONINFOS'] as $translation) {
            if ($translation['lng_id'] != $this->papaya()->administrationLanguage->id) {
              $data['copy_lng_id'] = $translation['lng_id'];
              break;
            }
          }
        }
      }
      $translations = array(
        0 => $this->_gt('None')
      );
      if (isset($this->topic['TRANSLATIONINFOS']) &&
          is_array($this->topic['TRANSLATIONINFOS'])) {
        foreach ($this->topic['TRANSLATIONINFOS'] as $translation) {
          $translations[$translation['lng_id']] = '';
          if (isset($this->papaya()->languages[$translation['lng_id']])) {
            $translations[$translation['lng_id']] =
              $this->papaya()->languages[$translation['lng_id']]['title'].' ('.
              $this->papaya()->languages[$translation['lng_id']]['code'].')';
          }
          $translations[$translation['lng_id']] .= ' - '.$translation['topic_title'];
        }
      }
      $fields = array(
        'copy_lng_id' => array('Copy translation', 'isNum', FALSE, 'combo', $translations)
      );

      $this->dialogAddTranslation = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogAddTranslation->dialogTitle = sprintf(
        $this->_gt('Add content for language "%s" (%s) to page "%s"?'),
        papaya_strings::escapeHTMLChars($this->papaya()->administrationLanguage->title),
        papaya_strings::escapeHTMLChars($this->papaya()->administrationLanguage->code),
        (int)$this->topicId
      );
      $this->dialogAddTranslation->buttonTitle = 'Add';
    }
  }

  /**
  * Add translation dialog
  *
  * @see base_msgdialog::getMsgDialog
  * @access public
  * @return string
  */
  function addTranslationDialog() {
    $this->initializeAddTranslationDialog();
    return $this->dialogAddTranslation->getDialogXML();
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
    $selector = new base_language_select;
    $selector->loadLanguages();
    $lngId = defined('PAPAYA_CONTENT_LANGUAGE') ? PAPAYA_CONTENT_LANGUAGE : 0;
    if (!empty($selector->languages[$lngId])) {
      $currentMainLanguage = ': '.$selector->languages[$lngId]['lng_title'];
      $currentMainLanguage .= ' ('.$selector->languages[$lngId]['lng_short'].')';
    } else {
      $currentMainLanguage = '';
    }
    return $selector->getContentLanguageCombo(
      $this->paramName,
      $name,
      $element,
      $data,
      $this->_gt('System default').$currentMainLanguage
    );
  }

  /**
  * add Audittime to last Version
  *
  * @access public
  * @return boolean
  */
  function addTopicAudit() {
    $sql = "SELECT v.version_id, v.version_time, v.version_author_id, v.version_message,
                   v.topic_id, v.topic_modified, v.topic_change_level,
                   v.topic_audited, v.linktype_id,
                   v.topic_weight, v.meta_useparent, v.box_useparent, topic_mainlanguage
              FROM %s v
             WHERE v.topic_id = '%d'
             ORDER BY v.version_id DESC";
    $params = array($this->tableTopicsVersions, $this->topic["topic_id"]);
    if ($res = $this->databaseQueryFmt($sql, $params, 1)) {
      $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
      $row['version_id'] = '';
      $row['topic_audited'] = time();
      $newVersionId =
        $this->databaseInsertRecord($this->tableTopicsVersions, "version_id", $row);
      $sql = "INSERT INTO %s (version_id, lng_id, topic_id, topic_title,
                     topic_content, author_id,
                     view_id, meta_title, meta_keywords, meta_descr)
              SELECT '%d', tt.lng_id, tt.topic_id, tt.topic_title, tt.topic_content, tt.author_id,
                     tt.view_id, tt.meta_title, tt.meta_keywords, tt.meta_descr
                FROM %s tt
               WHERE tt.topic_id = %d";
      $params = array($this->tableTopicsVersionsTrans, $newVersionId,
        $this->tableTopicsTrans, $this->topic["topic_id"]);
      if ($this->databaseQueryFmtWrite($sql, $params)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get XML for import (file) dialog
  *
  * @return string
  */
  function getImportDialogXML() {
    if (!(isset($this->dialogImport) && is_object($this->dialogImport))) {
      $hidden = array(
        'cmd' => 'import',
        'import_confirm' => 1,
        'page_id' => $this->topicId
      );
      $fields = array(
        'import_file' => array(
          'File', 'isSometext', TRUE, 'file', '', '', ''
        )
      );
      $data = array();
      $this->dialogImport = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogImport->uploadFiles = TRUE;
      $this->dialogImport->dialogTitle = $this->_gt('Import');
      $this->dialogImport->buttonTitle = 'Import';
      $this->dialogImport->inputFieldSize = 'x-large';
    }
    return $this->dialogImport->getDialogXML();
  }

  /**
  * Get child page count
  * @param integer $topicId
  * @return integer
  */
  function getSubPageCount($topicId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE prev = '%d'";
    $params = array(
      $this->tableTopics,
      $topicId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return 0;
  }

  /**
  * Initialize object for move page dialog
  * @return base_dialog
  */
  function initMovePageDialog() {
    $directions = array(1 => 'Up', 0 => 'Down');
    $fields = array(
      'steps' => array('Steps', 'isNum', TRUE, 'input', 5, '', 1),
      'direction' => array('Direction', 'isNum', TRUE, 'combo', $directions, '', 1)
    );
    $data = array();
    $hidden = array(
      'cmd' => 'move_page_dialog',
      'page_id' => $this->topicId,
      'confirm_move' => 1
    );
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Move page');
    $dialog->buttonTitle = 'Move';

    $subPages = $this->getSubPageCount($this->topic['prev']);
    $stepsUp = $this->topic['topic_weight'] - 100000;
    $stepsDown = $subPages - $stepsUp + 1;
    if ($stepsUp > 1) {
      $dialog->addButton('move_pos_first', 'First');
    }
    if ($stepsUp > 10) {
      $dialog->addButton('move_pos_10up', 'Ten up (-10)');
    }
    if ($stepsUp > 5) {
      $dialog->addButton('move_pos_5up', 'Five up (-5)');
    }
    if ($stepsDown > 5) {
      $dialog->addButton('move_pos_5down', 'Five down (+5)');
    }
    if ($stepsDown > 10) {
      $dialog->addButton('move_pos_10down', 'Ten down (+10)');
    }
    if ($stepsDown > 1) {
      $dialog->addButton('move_pos_last', 'Last');
    }
    return $dialog;
  }

  /**
  * Get Move page dialog xml
  * @return string
  */
  function getMovePageDialog() {
    $dialog = $this->initMovePageDialog();
    return $dialog->getDialogXML();
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
    if (NULL !== $synchronizations) {
      $this->_synchronizations = $synchronizations;
    } elseif (NULL === $this->_synchronizations) {
      $this->_synchronizations = new Administration\Pages\Dependency\Synchronizations();
    }
    return $this->_synchronizations;
  }
}
