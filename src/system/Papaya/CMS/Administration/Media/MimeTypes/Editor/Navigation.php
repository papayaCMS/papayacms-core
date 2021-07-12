<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\Media\MimeTypes\Editor {

  use Papaya\CMS\Administration\Page\Part as PagePart;
  use Papaya\CMS\Content\Media\MimeType\Groups as MimeTypeGroups;
  use Papaya\CMS\Content\Media\MimeTypes;
  use Papaya\Iterator\Tree\Items as GroupedItemsIterator;
  use Papaya\UI\ListView;
  use Papaya\UI\ListView\Items\Builder as ListViewBuilder;
  use Papaya\UI\Toolbar;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class Navigation extends PagePart {
    /**
     * @var ListView
     */
    private $_listView;

    /**
     * @var
     */
    private $_mimeTypes;

    /**
     * @var
     */
    private $_mimeGroups;

    /**
     * Append navigation to parent xml element
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $parent->append($this->listView());
      $mimeGroupId = $this->parameters()->get('group_id', 0);
      $mimeTypeId = $this->parameters()->get('type_id', 0);
      $this->toolbar()->elements[] = $button = new Toolbar\Button();
      $button->caption = new TranslatedText('Add Group');
      $button->image = 'icon.items.mime-group.add';
      $button->reference()->setParameters(
        [
          'cmd' => 'group_edit',
          'group_id' => 0,
          'type_id' => 0
        ],
        $this->parameterGroup()
      );
      if (0 < $mimeGroupId) {
        $this->toolbar()->elements[] = $button = new Toolbar\Button();
        $button->caption = new TranslatedText('Delete Group');
        $button->image = 'icon.items.mime-group.remove';
        $button->reference()->setParameters(
          [
            'cmd' => 'group_delete',
            'group_id' => $mimeGroupId,
            'type_id' => 0
          ],
          $this->parameterGroup()
        );
        $this->toolbar()->elements[] = new Toolbar\Separator();
        $this->toolbar()->elements[] = $button = new Toolbar\Button();
        $button->caption = new TranslatedText('Add MimeType');
        $button->image = 'icon.items.mime-type.add';
        $button->reference()->setParameters(
          [
            'cmd' => 'type_edit',
            'group_id' => $mimeGroupId,
            'type_id' => 0
          ],
          $this->parameterGroup()
        );
        if (0 < $mimeTypeId) {
          $this->toolbar()->elements[] = $button = new Toolbar\Button();
          $button->caption = new TranslatedText('Delete MimeType');
          $button->image = 'icon.items.mime-type.remove';
          $button->reference()->setParameters(
            [
              'cmd' => 'type_delete',
              'group_id' => $mimeGroupId,
              'type_id' => $mimeTypeId
            ],
            $this->parameterGroup()
          );
        }
      }
    }

    /**
     * @param ListView $listView
     * @return ListView
     */
    public function listView(ListView $listView = NULL) {
      if (NULL !== $listView) {
        $this->_listView = $listView;
      } elseif (NULL === $this->_listView) {
        $this->_listView = new ListView();
        $this->_listView->caption = new TranslatedText('Mime Types');
        $this->_listView->builder(
          $builder = new ListViewBuilder(
            new \RecursiveIteratorIterator(
              $this->createList($this->parameters()->get('group_id', 0)),
              \RecursiveIteratorIterator::SELF_FIRST
            )
          )
        );
        $builder->callbacks()->onCreateItem = function ($builder, $items, $element) {
          $this->createItem($items, $element);
        };
        $this->_listView->parameterGroup($this->parameterGroup());
        $this->_listView->parameters($this->parameters());
      }
      return $this->_listView;
    }

    public function createList($groupId) {
      $iterator = new GroupedItemsIterator(
        $this->mimeGroups(), GroupedItemsIterator::ATTACH_TO_KEYS
      );
      $iterator->attachItemIterator($groupId, $this->mimeTypes());
      return $iterator;
    }

    public function createItem(ListView\Items $items, array $element) {
      $mimeGroupId = $this->parameters()->get('group_id', 0);
      $mimeTypeId = $this->parameters()->get('type_id', 0);
      if (isset($element['group_id'])) {
        $items[] = $item = new ListView\Item(
          $this->getIconRoute($element['icon'], 'icon.items.mime-group'),
          $element['type'],
          [
            'cmd' => 'type_edit',
            'group_id' => $element['group_id'],
            'type_id' => $element['id']
          ]
        );
        $item->indentation = 1;
        $item->selected = (int)$element['id'] === $mimeTypeId;
      } else {
        $items[] = $item = new ListView\Item(
          $this->getIconRoute($element['icon'], 'icon.items.mime-type'),
          empty($element['title']) ? '['.(new TranslatedText('Group')).']' : $element['title'],
          [
            'cmd' => 'group_edit',
            'group_id' => $element['id'],
            'type_id' => 0
          ]
        );
        $item->selected = (int)$element['id'] === $mimeGroupId && 0 === $mimeTypeId;
      }
    }

    private function getIconRoute($icon, $fallback) {
      return empty($icon)
        ? $fallback
        : 'icon.mimetypes.'.str_replace(['.svg', '.png'], '', $icon);
    }

    public function mimeGroups(MimeTypeGroups $mimeGroups = NULL) {
      if (NULL !== $mimeGroups) {
        $this->_mimeGroups = $mimeGroups;
      } elseif (NULL === $this->_mimeGroups) {
        $this->_mimeGroups = new MimeTypeGroups();
        $this->_mimeGroups->papaya($this->papaya());
        $this->_mimeGroups->activateLazyLoad(
          [
            'language_id' => $this->papaya()->administrationLanguage->id
          ]
        );
      }
      return $this->_mimeGroups;
    }

    public function mimeTypes(MimeTypes $mimeTypes = NULL) {
      if (NULL !== $mimeTypes) {
        $this->_mimeTypes = $mimeTypes;
      } elseif (NULL === $this->_mimeTypes) {
        $this->_mimeTypes = new MimeTypes();
        $this->_mimeTypes->papaya($this->papaya());
        $this->_mimeTypes->activateLazyLoad(
          [
            'group_id' => $this->parameters()->get('group_id', 0)
          ]
        );
      }
      return $this->_mimeTypes;
    }
  }
}
