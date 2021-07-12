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
  use Papaya\Router;
  use Papaya\UI\Dialog\Field\Textarea\Richtext;

  class PageFragment extends Partials\Teaser implements RoutablePlugin {

    use PageModule\Aggregation;

    protected $teaserRTEMode = Richtext::RTE_DEFAULT;

    /**
     * Redirect to parent page.
     *
     * @param Router $router
     * @param NULL|object $context
     * @param int $level
     * @return Response\Failure|Response\Redirect
     */
    public function __invoke(Router $router, $context = NULL, $level = 0) {
      $reference = $this->papaya()->pageReferences->get(
        $this->papaya()->request->languageIdentifier,
        $this->getPage()->getParentID(1)
      );
      return new Response\Redirect((string)$reference);
    }
  }
}
