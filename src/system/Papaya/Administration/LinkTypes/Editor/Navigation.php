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
namespace Papaya\Administration\LinkTypes\Editor;

use Papaya\Content\Link\Types as LinkTypes;
use Papaya\UI\ListView;
use Papaya\UI\Toolbar;
use Papaya\UI\Text\Translated as TranslatedText;
use Papaya\XML\Element as XMLElement;

class Navigation extends \Papaya\Administration\Page\Part {
  /**
   * @var ListView
   */
  private $_listView;

  /**
   * @var
   */
  private $_linkTypes;

  /**
   * Append navigation to parent xml element
   *
   * @param XMLElement $parent
   */
  public function appendTo(XMLElement $parent) {
    $parent->append($this->listView());
    $linkTypeId = $this->parameters()->get('id', 0);
    $this->toolbar()->elements[] = $button = new Toolbar\Button();
    $button->caption = new TranslatedText('Add link type');
    $button->image = 'icon.items.link.add';
    $button->reference()->setParameters(
      [
        'cmd' => 'edit',
        'skin_id' => 0
      ],
      $this->parameterGroup()
    );
    if (0 < $linkTypeId) {
      $this->toolbar()->elements[] = $button = new Toolbar\Button();
      $button->caption = new TranslatedText('Delete link type');
      $button->image = 'icon.items.link.remove';
      $button->reference()->setParameters(
        [
          'cmd' => 'delete',
          'id' => $linkTypeId
        ],
        $this->parameterGroup()
      );
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
      $this->_listView->caption = new TranslatedText('Link Types');
      $this->_listView->builder(
        $builder = new ListView\Items\Builder(
          $this->linkTypes()
        )
      );
      $builder->callbacks()->onCreateItem = function($builder, ListView\Items $items, array $linkType) {
        $items[] = $item = new ListView\Item(
          $linkType['id'] < 0 ? 'icon.items.link.locked' : 'icon.items.link',
          $linkType['name']
        );
        $item->papaya($this->papaya());
        if ($linkType['id'] > 0) {
          $item->reference->setParameters(
            [
              'cmd' => 'edit',
              'id' => $linkType['id']
            ],
            $this->parameterGroup()
          );
        }
        $item->selected = (
          $this->parameters()->get('id', '') === $linkType['id']
        );
      };
      $builder->callbacks()->onCreateItem->context = $builder;
      $this->_listView->parameterGroup($this->parameterGroup());
      $this->_listView->parameters($this->parameters());
    }
    return $this->_listView;
  }

  public function linkTypes(LinkTypes $linkTypes = NULL) {
    if (NULL !== $linkTypes) {
      $this->_linkTypes = $linkTypes;
    } elseif (NULL === $this->_linkTypes) {
      $this->_linkTypes = new LinkTypes();
      $this->_linkTypes->papaya($this->papaya());
      $this->_linkTypes->activateLazyLoad();
    }
    return $this->_linkTypes;
  }
}
