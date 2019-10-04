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
  use Papaya\UI\Dialog\Field as DialogField;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\Response;

  class TeaserRedirect extends Partials\Teaser implements RoutablePlugin {

    use PageModule\Aggregation;

    const FIELD_TARGET_URL = 'target-url';

    const _DEFAULTS = [
      self::FIELD_TITLE => '',
      self::FIELD_SUBTITLE => '',
      self::FIELD_OVERLINE => '',
      self::FIELD_IMAGE => '',
      self::FIELD_TEASER => '',
      self::FIELD_TARGET_URL => null,
    ];

    /**
     * @param EditablePlugin\Content $content
     *
     * @return PluginEditor|PluginDialog
     */
    public function createEditor(EditablePlugin\Content $content) {
      $editor = parent::createEditor($content);
      $dialog = $editor->dialog();
      $dialog->fields[] = new DialogField\Input\URL(
        new TranslatedText('URL'),
        self::FIELD_TARGET_URL,
        self::_DEFAULTS[self::FIELD_TARGET_URL]
      );
      return $editor;
    }

    /**
     * Redirect to target page if provided, try parent page otherwise.
     *
     * @param \Papaya\Router $router
     * @param NULL|object $context
     * @param int $level
     * @return Response\Failure|Response\Redirect
     */
    public function __invoke(\Papaya\Router $router, $context = NULL, $level = 0) {
      $content = $this->content()->withDefaults(self::_DEFAULTS);
      $targetURL = $content['target-page-url'];
      if (empty($targetURL)) {
        $reference = $this->papaya()->pageReferences->get(
          $this->papaya()->request->languageIdentifier,
          $this->getPage()->getParentID()
        );
        $targetURL = (string)$reference;
      }
      return new Response\Redirect($targetURL);
    }
  }
}
