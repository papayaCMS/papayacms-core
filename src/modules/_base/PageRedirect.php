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

  use Papaya\CMS\Plugin\PageModule;
  use Papaya\Plugin\Routable as RoutablePlugin;
  use Papaya\Response;
  use Papaya\CMS\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Plugin\Editable\Content as PluginContent;
  use Papaya\Plugin\Quoteable as QuotablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Router;
  use Papaya\CMS\Output\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Document;
  use Papaya\XML\Element as XMLElement;

  class PageRedirect implements PageModule, EditablePlugin, QuotablePlugin, RoutablePlugin, Partials\QueryString {

    use EditablePlugin\Content\Aggregation;
    use PageModule\Aggregation;
    use Partials\QueryStringAggregation;

    private const FIELD_PAGE_ID = 'target-page-id';

    private const _PAGE_REDIRECT_DEFAULTS = [
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
      $defaults = $this->getDefaultContent();
      $editor = new PluginDialog($content);
      $editor->papaya($this->papaya());
      $dialog = $editor->dialog();
      $dialog->fields[] = new DialogField\Input(
        new TranslatedText('Page Id'),
        self::FIELD_PAGE_ID,
        255,
        $defaults[self::FIELD_PAGE_ID]
      );
      $this->appendQueryStringFieldsToDialog($dialog, $content);
      return $editor;
    }

    /**
     * Redirect to target page if provided.
     *
     * @param Router $router
     * @param NULL|object $context
     * @param int $level
     * @return Response\Failure|Response\Redirect
     */
    public function __invoke(Router $router, $context = NULL, $level = 0) {
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $pageId = $content[self::FIELD_PAGE_ID] ?: $this->getPage()->getParentID(1);
      $reference = $this->papaya()->pageReferences->get(
        $this->papaya()->request->languageIdentifier, $pageId
      );
      if ($pageId > 0 && $reference->valid()) {
        return new Response\Redirect($this->appendQueryStringToURL($reference));
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
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $pageId = $content[self::FIELD_PAGE_ID] ?: $this->getPage()->getParentID(1);
      if (
        $pageId > 0 &&
        ($teasers = $this->teaserFactory()->byPageId($pageId)) &&
        isset($teasers->pages()[$pageId])
      ) {
        $teaser = new Document();
        $fragment = $teaser->appendElement('fragment');
        $teasers->appendTeaserTo($fragment, $teasers->pages()[$pageId]);
        if ($fragment->firstChild) {
          foreach ($teaser->xpath()->evaluate('/*/teaser/*') as $node) {
            $parent->appendChild($parent->ownerDocument->importNode($node, TRUE));
          }
          $redirect = $parent->appendElement('redirect-page');
          foreach ($teaser->xpath()->evaluate('/*/teaser/@*') as $attribute) {
            $redirect->setAttribute($attribute->name, $attribute->value);
          }
        }
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

    /**
     * @return array
     */
    protected function getDefaultContent() {
      return self::_PAGE_REDIRECT_DEFAULTS;
    }
  }
}
