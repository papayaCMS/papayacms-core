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

  use Papaya\Plugin\Routable as RoutablePlugin;
  use Papaya\Response;
  use Papaya\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Plugin\Editable\Content as PluginContent;
  use Papaya\Plugin\Quoteable as QuotablePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\XML\Element as XMLElement;

  class PageRedirect implements EditablePlugin, QuotablePlugin, RoutablePlugin {

    use EditablePlugin\Aggregation;

    const FIELD_PAGE_ID = 'target-page_id';

    const _DEFAULTS = [
      self::FIELD_PAGE_ID => 0
    ];

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
     * Create dom node structure of the given object and append it to the given xml
     * element node.
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

      return $parent;
    }
  }
}
