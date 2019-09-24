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

namespace Papaya\Modules\Core {

  use Papaya\Administration\Plugin\Editor\Dialog as EditorDialog;
  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editable\Content as EditableContent;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\UI\Content\Sitemap;
  use Papaya\UI\Dialog\Field\Group as FieldGroup;
  use Papaya\UI\Dialog\Field\Input\Range as RangeField;
  use Papaya\UI\Dialog\Field\Input\Page as PageIdField;
  use Papaya\UI\Dialog\Field\Select\Radio as RadioGroupField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\XML\Element as XMLElement;

  class Navigation implements ApplicationAccess, AppendablePlugin, EditablePlugin {

    use ApplicationAccess\Aggregation;
    use EditableContent\Aggregation;

    const FIELD_MODE = 'sitemap-mode';
    const FIELD_INCLUDE_HIDDEN = 'sitemap-include-hidden';
    const FIELD_ANCESTOR_PAGE_ID = 'ancestor-page-id';
    const FIELD_ANCESTOR_OFFSET = 'ancestor-offset';
    const FIELD_ANCESTOR_LEVELS = 'ancestor-levels';

    const _DEFAULTS = [
      self::FIELD_MODE => Sitemap::MODE_PATH,
      self::FIELD_INCLUDE_HIDDEN => FALSE,
      self::FIELD_ANCESTOR_PAGE_ID => 0,
      self::FIELD_ANCESTOR_OFFSET => 0,
      self::FIELD_ANCESTOR_LEVELS => 1
    ];

    private $_sitemap;

    /**
     * @param EditableContent $content
     *
     * @return PluginEditor
     */
    public function createEditor(EditableContent $content) {
      $editor = new EditorDialog($content);
      $editor->papaya($this->papaya());
      $dialog = $editor->dialog();
      $dialog->fields[] = $group = new FieldGroup(new TranslatedText('Ancestor'));
      $group->fields[] = new PageIdField(
        new TranslatedText('Page'), self::FIELD_ANCESTOR_PAGE_ID, self::_DEFAULTS[self::FIELD_ANCESTOR_PAGE_ID], TRUE
      );
      $group->fields[] = new RangeField(
        new TranslatedText('Offset'), self::FIELD_ANCESTOR_OFFSET, self::_DEFAULTS[self::FIELD_ANCESTOR_OFFSET], 0, 10
      );
      $group->fields[] = new RangeField(
        new TranslatedText('Levels'), self::FIELD_ANCESTOR_LEVELS, self::_DEFAULTS[self::FIELD_ANCESTOR_LEVELS], 0, 20
      );
      $dialog->fields[] = $group = new FieldGroup(new TranslatedText('Options'));
      $group->fields[] = $field = new RadioGroupField(
        new TranslatedText('Load Path'),
        self::FIELD_MODE,
        new TranslatedList([Sitemap::MODE_PATH => 'Yes', Sitemap::MODE_FIX => 'No']
        ),
        TRUE
      );
      $field->setDefaultValue(self::_DEFAULTS[self::FIELD_MODE]);
      $field->setHint(new TranslatedText('Load sibling/child pages for current page path'));
      $group->fields[] = $field = new RadioGroupField(
        new TranslatedText('Include Hidden'),
        self::FIELD_INCLUDE_HIDDEN,
        new TranslatedList(['1' => 'Yes', '0' => 'No']
        ),
        TRUE
      );
      $field->setHint(new TranslatedText('Include pages with hidden link types.'));
      return $editor;
    }

    /**
     * Create dom node structure of the given object and append it to the given xml
     * element node.
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $navigation = $parent->appendElement('navigation');
      $navigation->append($this->sitemap());
    }

    public function sitemap(Sitemap $sitemap = NULL) {
      if (NULL !== $sitemap) {
        $this->_sitemap = $sitemap;
      } elseif (NULL === $this->_sitemap) {
        $content = $this->content()->withDefaults(self::_DEFAULTS);
        $this->_sitemap = new Sitemap(
          $content[self::FIELD_ANCESTOR_PAGE_ID],
          $this->papaya()->request->pageId,
          $this->papaya()->request->languageId,
          $content[self::FIELD_MODE]
        );
        $this->_sitemap->papaya($this->papaya());
        $this->_sitemap->setOffsetAndStaticDepth(
          $content[self::FIELD_ANCESTOR_OFFSET], $content[self::FIELD_ANCESTOR_LEVELS]
        );
        $this->_sitemap->setIncludeHiddenPages($content[self::FIELD_INCLUDE_HIDDEN]);
      }
      return $this->_sitemap;
    }
  }
}

