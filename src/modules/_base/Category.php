<?php
/**
 * A simple CMS box for loading a page teaser
 *
 * @copyright  2013 by papaya Software GmbH - All rights reserved.
 * @link       http://www.papaya-cms.com/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package    Papaya-Library
 * @subpackage Modules-Standard
 * @version    $Id: Box.php 39795 2014-05-06 15:35:52Z weinert $
 */

namespace Papaya\Modules\Core {

  /*
  * A simple CMS box for loading a page teaser
  *
  * @package Papaya-Library
  * @subpackage Modules-Standard
  */

  use Papaya\Cache\Identifier\Definition\BooleanValue;
  use Papaya\Plugin\Cacheable as CacheablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Plugin\Filter as PluginFilter;
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\XML\Element as XMLElement;

  class Category extends Article {

    use ContextAwarePlugin\Aggregation;
    use EditablePlugin\Content\Aggregation;
    use CacheablePlugin\Aggregation;
    use PluginFilter\Aggregation;

    const FIELD_TEASER_ORDER = 'teaser-order';
    const FIELD_TEASER_LIMIT = 'teaser-limit';
    const FIELD_TEASER_IMAGE_RESIZE = 'teaser-image-resize-mode';
    const FIELD_TEASER_IMAGE_WIDTH = 'teaser-image-width';
    const FIELD_TEASER_IMAGE_HEIGHT = 'teaser-image-height';

    const _DEFAULTS = [
      self::FIELD_TITLE => '',
      self::FIELD_SUBTITLE => '',
      self::FIELD_OVERLINE => '',
      self::FIELD_IMAGE => '',
      self::FIELD_TEASER => '',
      self::FIELD_TEXT => '',
      self::FIELD_TEASER_ORDER => PageTeaserFactory::ORDER_POSITION_ASCENDING,
      self::FIELD_TEASER_LIMIT => 10,
      self::FIELD_TEASER_IMAGE_RESIZE => 'max',
      self::FIELD_TEASER_IMAGE_WIDTH => 0,
      self::FIELD_TEASER_IMAGE_HEIGHT => 0
    ];

    /**
     * @var PageTeaserFactory
     */
    private $_contentTeasers;

    public function __construct($page) {
      $this->_page = $page;
    }

    /**
     * Append the page output xml to the DOM.
     *
     * @param XMLElement $parent
     * @see PapayaXmlAppendable::appendTo()
     */
    public function appendTo(XMLElement $parent) {
      parent::appendTo($parent);
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      $pageId = $this->_page->getPageId();
      if ($pageId !== '') {
        $teasers = $this->teaserFactory()->byParent($pageId, $content[self::FIELD_TEASER_LIMIT]);
        $parent->append($teasers);
      }
    }

    /**
     * The editor is used to change the stored data in the administration interface.
     *
     * In this case it the editor creates an dialog from a field definition.
     *
     * @param EditablePlugin\Content $content
     * @return PluginEditor
     * @see PapayaPluginEditableContent::editor()
     */
    public function createEditor(EditablePlugin\Content $content) {
      $editor = parent::createEditor($content);
      $dialog = $editor->dialog();
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Teasers')
      );
      $group->fields[] = $field = new DialogField\Select(
        new TranslatedText('Order'),
        self::FIELD_TEASER_ORDER,
        new TranslatedList(
          [
            PageTeaserFactory::ORDER_POSITION_ASCENDING => 'Position Ascending',
            PageTeaserFactory::ORDER_POSITION_DESCENDING => 'Position Descending',
            PageTeaserFactory::ORDER_CREATED_ASCENDING => 'Created Ascending',
            PageTeaserFactory::ORDER_CREATED_DESCENDING => 'Created Descending',
            PageTeaserFactory::ORDER_MODIFIED_ASCENDING => 'Modified/Published Ascending',
            PageTeaserFactory::ORDER_MODIFIED_DESCENDING => 'Modified/Published Descending'
          ]
        ),
        TRUE
      );
      $field->setDefaultValue(self::_DEFAULTS[self::FIELD_TEASER_ORDER]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Limit'),
        self::FIELD_TEASER_LIMIT,
        self::_DEFAULTS[self::FIELD_TEASER_LIMIT]
      );
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Teaser Images')
      );
      $group->fields[] = $field = new DialogField\Select\Radio(
        new TranslatedText('Resize Mode'),
        self::FIELD_TEASER_IMAGE_RESIZE,
        new TranslatedList(
          [
            'max' => 'Maximum',
            'min' => 'Minimum',
            'mincrop' => 'Minimum crop',
            'abs' => 'Absolute',
          ]
        ),
        TRUE
      );
      $field->setDefaultValue(self::_DEFAULTS[self::FIELD_TEASER_IMAGE_RESIZE]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Width'),
        self::FIELD_TEASER_IMAGE_WIDTH,
        self::_DEFAULTS[self::FIELD_TEASER_IMAGE_WIDTH]
      );
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Height'),
        self::FIELD_TEASER_IMAGE_HEIGHT,
        self::_DEFAULTS[self::FIELD_TEASER_IMAGE_HEIGHT]
      );
      return $editor;
    }

    /**
     * Get/set the \Papaya\UI\Content\Teasers\Factory Object
     *
     * @param PageTeaserFactory $teasers
     * @return PageTeaserFactory
     */
    public function teaserFactory(PageTeaserFactory $teasers = NULL) {
      if (NULL !== $teasers) {
        $this->_contentTeasers = $teasers;
      } elseif (NULL === $this->_contentTeasers) {
        $imageHeight = $this->content()->get('teaser_image_height', 0);
        $imageWidth = $this->content()->get('teaser_image_width', 0);
        $this->_contentTeasers = new PageTeaserFactory($imageWidth, $imageHeight, 'max');
      }
      return $this->_contentTeasers;
    }

    public function createCacheDefinition() {
      return new BooleanValue(TRUE);
    }

  }
}
