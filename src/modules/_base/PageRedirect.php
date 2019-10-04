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

  use Papaya\Plugin\PageModule;
  use Papaya\Plugin\Routable as RoutablePlugin;
  use Papaya\Response;
  use Papaya\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Plugin\Editable\Content as PluginContent;
  use Papaya\Plugin\Quoteable as QuotablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class PageRedirect implements PageModule, EditablePlugin, QuotablePlugin, RoutablePlugin {

    use EditablePlugin\Aggregation;
    use PageModule\Aggregation;

    const FIELD_PAGE_ID = 'target-page-id';

    const _DEFAULTS = [
      self::FIELD_PAGE_ID => 0
    ];
    /**
     * @var PageTeaserFactory
     */
    private $_contentTeasers;

    /**
     * @param PluginContent $content
     *
     * @return PluginDialog
     */
    public function createEditor(PluginContent $content) {
      $editor = new PluginDialog($content);
      $editor->papaya($this->papaya());
      $dialog = $editor->dialog();
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Page Id'), self::FIELD_PAGE_ID, 255, self::_DEFAULTS[self::FIELD_PAGE_ID]
      );
      return $editor;
    }

    /**
     * Redirect to target page if provided.
     *
     * @param \Papaya\Router $router
     * @param NULL|object $context
     * @param int $level
     * @return Response\Failure|Response\Redirect
     */
    public function __invoke(\Papaya\Router $router, $context = NULL, $level = 0) {
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      $reference = $this->papaya()->pageReferences->get(
        $this->papaya()->request->languageIdentifier,
        $content['target-page_id']
      );
      if ($content['target-page_id'] > 0 && $reference->valid()) {
        return new Response\Redirect((string)$reference);
      }
      return new Response\Failure('Invalid Redirect Target.');
    }

    /**
     * Append short content (aka "quote") to the parent xml element.
     *
     * @param XMLElement $parent
     *
     * @return XMLElement
     */
    public function appendQuoteTo(XMLElement $parent) {
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      if (
        ($pageId = $content['target-page_id']) > 0 &&
        ($teasers = $this->teaserFactory()->byPageId($pageId)) &&
        isset($teasers[$pageId])
      ) {
        $teasers->appendTeaserTo($parent, $teasers->pages()[$pageId]);
      }
      return $parent;
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
        $this->_contentTeasers = new PageTeaserFactory(0, 0, 'max');
      }
      return $this->_contentTeasers;
    }
  }
}
