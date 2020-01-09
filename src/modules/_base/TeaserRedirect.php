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

  use Papaya\Administration\Plugin\Editor\Dialog as PluginDialog;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\PageModule;
  use Papaya\Plugin\Routable as RoutablePlugin;
  use Papaya\Router;
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\Response;
  use Papaya\Utility\Arrays as ArrayUtilities;

  class TeaserRedirect extends Partials\Teaser implements RoutablePlugin, Partials\QueryString {

    use PageModule\Aggregation;
    use Partials\QueryStringAggregation;

    const FIELD_TARGET_URL = 'target-url';

    const _REDIRECT_DEFAULTS = [
      self::FIELD_TARGET_URL => NULL,
    ];

    /**
     * @param EditablePlugin\Content $content
     *
     * @return PluginEditor|PluginDialog
     */
    public function createEditor(EditablePlugin\Content $content) {
      $defaults = $this->getDefaultContent();
      $editor = parent::createEditor($content);
      $dialog = $editor->dialog();
      $dialog->fields[] = new DialogField\Input\URL(
        new TranslatedText('URL'),
        self::FIELD_TARGET_URL,
        $defaults[self::FIELD_TARGET_URL]
      );
      $this->appendQueryStringFieldsToDialog($dialog, $content);
      return $editor;
    }

    /**
     * Redirect to target page if provided, try parent page otherwise.
     *
     * @param Router $router
     * @param NULL|object $context
     * @param int $level
     * @return Response\Failure|Response\Redirect
     */
    public function __invoke(Router $router, $context = NULL, $level = 0) {
      $content = $this->content()->withDefaults($this->getDefaultContent());
      $targetURL = $content['target-page-url'];
      if (empty($targetURL)) {
        $reference = $this->papaya()->pageReferences->get(
          $this->papaya()->request->languageIdentifier,
          $this->getPage()->getParentID(1)
        );
        $targetURL = (string)$reference;
      }
      return new Response\Redirect($this->appendQueryStringToURL($targetURL));
    }

    protected function getDefaultContent() {
      return ArrayUtilities::merge(
        parent::getDefaultContent(),
        self::_REDIRECT_DEFAULTS
      );
    }
  }
}
