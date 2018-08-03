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
use Papaya\Administration\Permissions;

/**
* Create base navigagtion for papaya admin area
*
* @package Papaya
* @subpackage Administration
*/
class papaya_navigation extends base_object {

  /**
  * Menu
  * @var array $menu
  *   array values
  *   0) title
  *   1) tooltip
  *   2) icon
  *   3) permission
  *   4) url
  *   5) target
  *   6) force button down
  *   7) access key
  *   8) do not translate button title and tooltip
  */
  var $menu = array(
    'general' => array(
      array(
        'Overview',
        'Last messages, todos and page changes',
        'places-home',
        0,
        'index.php',
        '_self',
        FALSE
      ),
      array(
        'Messages',
        'Messages / ToDo',
        'status-mail-open',
        Administration\Permissions::MESSAGES,
        'msgbox.php',
        '_self',
        FALSE
      ),
    ),
    'pages' => array (
      array(
        'Sitemap',
        'All pages in a tree',
        'categories-sitemap',
        Administration\Permissions::PAGE_MANAGE,
        'tree.php',
        '_self',
        FALSE,
        'T'
      ),
      array(
        'Search',
        'Search pages',
        'actions-search',
        Administration\Permissions::PAGE_MANAGE,
        'search.php',
        '_self',
        FALSE
      ),
      array(
        'Edit',
        'Edit pages',
        'items-page',
        Administration\Permissions::PAGE_MANAGE,
        'topic.php',
        '_self',
        FALSE,
        'E'
      )
    ),
    'additional' => array(
      array(
        'Boxes',
        'Edit boxes',
        'items-box',
        Administration\Permissions::BOX_MANAGE,
        'boxes.php'
      ),
      array(
        'Files',
        'Media database',
        'items-folder',
        Administration\Permissions::FILE_MANAGE,
        'mediadb.php',
        '_self',
        FALSE,
        'M'
      ),
      array(
        'Aliases',
        'Aliases for pages',
        'items-alias',
        Administration\Permissions::ALIAS_MANAGE,
        'alias.php'
      ),
      array(
        'Tags',
        'Manage Tags',
        'items-tag',
        Administration\Permissions::TAG_MANAGE,
        'tags.php'
      ),
    ),
    'modules' => array(
    ),
    'administration' => array(
      array(
        'Users',
        'User management',
        'items-user-group',
        Administration\Permissions::USER_MANAGE,
        'auth.php'
      ),
      array(
        'Views',
        'Configure Views',
        'items-view',
        Administration\Permissions::VIEW_MANAGE,
        'views.php'
      ),
      array(
        'Modules',
        'Modules management',
        'items-plugin',
        Administration\Permissions::MODULE_MANAGE,
        'modules.php'
      ),
      array(
        'Themes',
        'Configure Dynamic Themes',
        'items-theme',
        Administration\Permissions::SYSTEM_THEMESET_MANAGE,
        'themes.php'
      ),
      array(
        'Images',
        'Configure Dynamic Images',
        'items-graphic',
        Administration\Permissions::IMAGE_GENERATOR,
        'imggen.php'
      ),
      array(
        'Settings',
        'System configuration',
        'items-option',
        Administration\Permissions::SYSTEM_SETTINGS,
        'options.php'
      ),
      array(
        'Protocol',
        'Event protocol',
        'categories-protocol',
        Administration\Permissions::SYSTEM_PROTOCOL,
        'log.php'
      ),
      array(
        'Translations',
        'Interface Translations',
        'items-translation',
        Administration\Permissions::SYSTEM_TRANSLATE,
        'phrases.php'
      )
    ),
  );

  var $menuGroups = array(
    'general' => 'General',
    'pages' => 'Pages',
    'additional' => 'Additional Content',
    'modules' => 'Applications',
    'administration' => 'Administration'
  );

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
  * Initialization
  *
  * @access public
  */
  function initialize($fileName = NULL) {
    $this->getEditModules();
    $menuStr = $this->getMenuBar($fileName);
    $this->layout->addMenu($menuStr);
    $this->getNewMessageCount();
  }

  /**
  * Get count of new message for the current user
  */
  function getNewMessageCount() {
    $messages = new base_messages();
    $counts = $messages->loadMessageCounts(array(0), TRUE);
    $this->layout->parameters()->set(
      'PAPAYA_MESSAGES_INBOX_NEW',
      empty($counts[0]) ? 0 : (int)$counts[0]
    );
  }

  /**
  * Get edit modules
  *
  * @access public
  */
  function getEditModules() {
    $obj = new papaya_editmodules(empty($_GET['p_module']) ? '' : $_GET['p_module']);
    $obj->loadModulesList();
    $modules = $obj->getButtonArray();
    if (isset($modules) && is_array($modules)) {
      $this->menu['modules'] = array_merge($this->menu['modules'], $modules);
    }
  }

  /**
  * Get main manubar xml
  * @param string $fileName
  * @return string
  */
  function getMenuBar($fileName = '') {
    $menu = new \PapayaUiMenu();
    $menu->identifier = 'main';
    $currentUrl = $this->papaya()->request->getUrl()->getPathUrl();
    foreach ($this->menuGroups as $groupId => $groupTitle) {
      if (isset($this->menu[$groupId])) {
        $group = new \Papaya\Ui\Toolbar\Group(new \Papaya\Ui\Text\Translated($groupTitle));
        foreach ($this->menu[$groupId] as  $buttonData) {
          if (empty($buttonData[3]) ||
              $this->papaya()->administrationUser->hasPerm($buttonData[3])) {
            $button = new \Papaya\Ui\Toolbar\Button();
            $button->image = $buttonData[2];
            if (isset($buttonData[8]) && $buttonData[8]) {
              $button->caption = empty($buttonData[0]) ? '' : $buttonData[0];
              $button->hint = empty($buttonData[1]) ? '' : $buttonData[1];
            } else {
              $button->caption = new \Papaya\Ui\Text\Translated(
                empty($buttonData[0]) ? '' : $buttonData[0]
              );
              $button->hint = new \Papaya\Ui\Text\Translated(
                empty($buttonData[1]) ? '' : $buttonData[1]
              );
            }
            if (!empty($buttonData[7])) {
              $button->accessKey = $buttonData[7];
            }
            $button->target = empty($buttonData[5]) ? '_self' : $buttonData[5];
            $button->reference->setRelative(
              empty($buttonData[4]) ? '' : $buttonData[4]
            );
            if ($button->reference->url()->getPathUrl() == $currentUrl) {
              $button->selected = TRUE;
            }
            $group->elements[] = $button;
          }
        }
        $menu->elements[] = $group;
      }
    }
    return $menu->getXml();
  }
}
