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
namespace Papaya\CMS\Administration\UI\Navigation {

  use Papaya\CMS\Administration;
  use Papaya\CMS\CMSConfiguration;
  use Papaya\Iterator\Filter\Callback as CallbackFilterIterator;
  use Papaya\CMS\Plugin\Types as PluginTypes;
  use Papaya\UI;
  use Papaya\Utility;
  use Papaya\XML\Element;

  class Main extends UI\Control {
    /**
     * @var array
     *
     * Groups of menu button definitions. The keys are used
     * as captions for the groups and the routes (hrefs) for the buttons.
     * Each definition can contain:
     *
     *  0) image - button icon
     *  1) caption - button caption
     *  2) hint - tooltip
     *  3) permission - user permission needed
     *  4) access_key
     *  5) target - default "_self"
     *  6) feature_flag - setting needed
     */
    private static $_groups = [
      'General' => [
        Administration\UI::OVERVIEW => [
          'places-home',
          'Overview',
          'Last messages, todos and page changes'
        ],
        Administration\UI::MESSAGES => [
          'status-mail-open',
          'Messages',
          'Messages, ToDo',
          Administration\Permissions::MESSAGES
        ]
      ],
      'Pages' => [
        Administration\UI::PAGES_SITEMAP => [
          'categories-sitemap',
          'Sitemap',
          'All pages in a tree view',
          Administration\Permissions::PAGE_MANAGE,
          'T'
        ],
        Administration\UI::PAGES_SEARCH => [
          'actions-search',
          'Search',
          'Search pages',
          Administration\Permissions::PAGE_MANAGE
        ],
        Administration\UI::PAGES_EDIT => [
          'items-page',
          'Edit',
          'Edit pages',
          Administration\Permissions::PAGE_MANAGE,
          'E'
        ]
      ],
      'Additional Content' => [
        Administration\UI::CONTENT_BOXES => [
          'items-box',
          'Boxes',
          'Edit boxes',
          Administration\Permissions::BOX_MANAGE
        ],
        Administration\UI::CONTENT_FILES => [
          'items-folder',
          'Files',
          'Media database',
          Administration\Permissions::FILE_MANAGE,
          'M'
        ],
        Administration\UI::CONTENT_FILES.'.refactor' => [
          'items-folder',
          'Files (refactor)',
          'Media database',
          Administration\Permissions::FILE_MANAGE,
          'feature_flag' => CMSConfiguration::FEATURE_MEDIA_DATABASE_2
        ],
        Administration\UI::CONTENT_IMAGES => [
          'items-graphic',
          'Graphics',
          'Configure dynamic/generated images',
          Administration\Permissions::IMAGE_GENERATOR
        ],
        Administration\UI::CONTENT_ALIASES => [
          'items-alias',
          'Aliases',
          'Manage page aliases',
          Administration\Permissions::BOX_MANAGE
        ],
        Administration\UI::CONTENT_TAGS => [
          'items-tag',
          'Tags',
          'Manage tags',
          Administration\Permissions::TAG_MANAGE
        ]
      ],
      'Applications' => [
        Administration\UI::EXTENSIONS => [
          'categories-applications',
          'Applications',
          'Applications list'
        ]
      ],
      'Administration' => [
        Administration\UI::ADMINISTRATION_USERS => [
          'items-user-group',
          'Users',
          'Manage users',
          Administration\Permissions::USER_MANAGE
        ],
        Administration\UI::ADMINISTRATION_VIEWS => [
          'items-view',
          'Views',
          'Manage views',
          Administration\Permissions::VIEW_MANAGE
        ],
        Administration\UI::ADMINISTRATION_PLUGINS => [
          'items-plugin',
          'Plugins',
          'Manage Plugins',
          Administration\Permissions::MODULE_MANAGE
        ],
        Administration\UI::ADMINISTRATION_THEMES => [
          'items-theme',
          'Themes',
          'Manage theme skins',
          Administration\Permissions::SYSTEM_THEME_SKIN_MANAGE
        ],
        Administration\UI::ADMINISTRATION_SETTINGS => [
          'items-option',
          'Settings',
          'System configuration',
          Administration\Permissions::SYSTEM_SETTINGS
        ],
        Administration\UI::ADMINISTRATION_PROTOCOL => [
          'categories-protocol',
          'Protocol',
          'System protocol',
          Administration\Permissions::SYSTEM_PROTOCOL
        ],
        Administration\UI::ADMINISTRATION_PHRASES => [
          'items-translation',
          'Translation',
          'Manage languages and interface translations',
          Administration\Permissions::SYSTEM_TRANSLATE
        ],
      ]
    ];

    private static $_moduleGroupCaption = 'Applications';

    private $_menu;

    private $_favorites;

    private function _createMenu() {
      $menu = new UI\Menu();
      $menu->papaya($this->papaya());
      $menu->identifier = 'main';
      $currentURL = $this->papaya()->request->getURL()->getPathURL();
      foreach (self::$_groups as $groupCaption => $buttons) {
        $menu->elements[] = $group = new UI\Toolbar\Group(
          new UI\Text\Translated($groupCaption)
        );
        if (self::$_moduleGroupCaption === $groupCaption) {
          foreach ($this->favorites() as $favorite) {
            $button = new UI\Toolbar\Button();
            $button->reference->setRelative(
              Administration\UI::EXTENSIONS.'.'.$favorite['guid']
            );
            if ('' !== \trim($favorite['image'])) {
              $button->image = Administration\UI::EXTENSIONS_IMAGE.'?module='.\urlencode($favorite['guid']);
            }
            $button->caption = new UI\Text\Translated($favorite['title']);
            $button->target = '_self';
            if ($button->reference->url()->getPathURL() === $currentURL) {
              $button->selected = TRUE;
            }
            $group->elements[] = $button;
          }
        }
        foreach ($buttons as $href => $buttonData) {
          $image = Utility\Arrays::get($buttonData, ['image', 0], '');
          $buttonCaption = Utility\Arrays::get($buttonData, ['caption', 1], '');
          $hint = Utility\Arrays::get($buttonData, ['hint', 2], '');
          $permission = Utility\Arrays::get($buttonData, ['permission', 3], 0);
          $featureFlag = Utility\Arrays::get($buttonData, ['feature_flag', 6], '');
          $accessKey = Utility\Arrays::get($buttonData, ['access_key', 4], '');
          $target = Utility\Arrays::get($buttonData, ['target', 5], '_self');

          $isEnabled = (
            empty($featureFlag) ||
            $this->papaya()->options->get($featureFlag, FALSE)
          );
          $isAllowed = (
            0 === $permission ||
            $this->papaya()->administrationUser->hasPerm($permission)
          );

          if ($isEnabled && $isAllowed) {
            $button = new UI\Toolbar\Button();
            $button->papaya($this->papaya());
            $button->reference->setRelative($href);
            $button->image = $image;
            $button->caption = new UI\Text\Translated($buttonCaption);
            $button->hint = new UI\Text\Translated($hint);
            if (1 === \strlen($accessKey)) {
              $button->accessKey = $accessKey;
            }
            $button->target = $target;
            if ($button->reference->url()->getPathURL() === $currentURL) {
              $button->selected = TRUE;
            }
            $group->elements[] = $button;
          }
        }
      }
      return $menu;
    }

    /**
     * @param Element $parent
     */
    public function appendTo(Element $parent) {
      $parent->append($this->menu());
    }

    /**
     * @param UI\Menu|NULL $menu
     * @return UI\Menu
     */
    public function menu(UI\Menu $menu = NULL) {
      if (NULL !== $menu) {
        $this->_menu = $menu;
      } elseif (NULL === $this->_menu) {
        $this->_menu = $this->_createMenu();
      }
      return $this->_menu;
    }

    /**
     * @param \Traversable|NULL $favorites
     * @return CallbackFilterIterator|\Traversable
     */
    public function favorites(\Traversable $favorites = NULL) {
      if (NULL !== $favorites) {
        $this->_favorites = $favorites;
      } elseif (NULL === $this->_favorites) {
        $administrationUser = $this->papaya()->administrationUser;
        $plugins = $this->papaya()->plugins->plugins()->withType(PluginTypes::ADMINISTRATION);
        $this->_favorites = new CallbackFilterIterator(
          $plugins,
          static function(
            /** @noinspection PhpUnusedParameterInspection */
            $plugin, $pluginGuid
          ) use ($administrationUser) {
            return (
              \is_array($administrationUser->userModules) &&
              \in_array($pluginGuid, $administrationUser->userModules, TRUE) &&
              (
                $administrationUser->isAdmin() ||
                $administrationUser->hasModulePerm(1, $pluginGuid)
              )
            );
          }
        );
      }
      return $this->_favorites;
    }
  }
}
