<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\UI\Route {

  use Papaya\Response;
  use Papaya\Router;
  use Papaya\Template\Engine\XSLT;
  use Papaya\XML\Element;
  use Papaya\XML\Errors;

  /**
   * Popups are defined by an XML structure inside an XSLT template.
   *
   * @package Papaya\Router\Route
   */
  class Popup implements \Papaya\Router\PathRoute {
    const XMLNS = 'http://papaya-cms.com/administration/popup';

    /**
     * @var string
     */
    private $_file;

    /**
     * @var callable
     */
    private $_fetch;

    /**
     * @param string| $file
     * @param callable|null $fetchData
     */
    public function __construct($file, callable $fetchData = NULL) {
      $this->_file = $file;
      $this->_fetch = $fetchData;
    }

    /**
     * @param Router $router
     * @param Router\Path $address
     * @param int $level
     * @return null|Response|\Papaya\Router\Route
     */
    public function __invoke(Router $router, $address = NULL, $level = 0) {
      $xslDocument = new \Papaya\XML\Document();
      $xslDocument->load($this->_file);
      $xslDocument->registerNamespace('popup', self::XMLNS);

      $popup = $xslDocument->xpath()->evaluate('//popup:popup[1]')[0];

      if ($popup) {
        $xmlDocument = new \Papaya\XML\Document();
        $xmlDocument->registerNamespace('popup', self::XMLNS);
        /** @var \Papaya\XML\Element $popup */
        $popup = $xmlDocument->appendChild($xmlDocument->importNode($popup, TRUE));
        $this->fetchData($router, $popup);

        $template = new XSLT();
        $template->setTemplateDocument($xslDocument);
        $template->values($xmlDocument);

        $errors = new Errors();
        $html = $errors->encapsulate(
          function() use ($template) {
            $template->prepare();
            $template->run();
            return $template->getResult();
          }
        );

        if ($html) {
          $response = new Response();
          $response->content(new Response\Content\Text($html));
          return $response;
        }
      }
      return new Router\Route\Error(
        'Broken route.', 500
      );
    }

    private function fetchData(Router $router, Element $popup) {
      $document = $popup->ownerDocument;
      /** @var \Papaya\XML\Element $node */
      // translate phrases to current language
      $phrases = $router->papaya()->administrationPhrases;
      foreach ($document->xpath()->evaluate('//popup:phrase', $popup) as $node) {
        $identifier = $node->getAttribute('identifier') ?: $node->textContent;
        $node->setAttribute('identifier', $identifier);
        $node->textContent = $phrases->get($node->textContent, [], 'popups');
      }
      // add option values
      $options = $router->papaya()->options;
      foreach ($document->xpath()->evaluate('//popup:option[@name != ""]', $popup) as $node) {
        $node->textContent = $options->get($node->getAttribute('name'), '');
      }

      if (NULL !== ($fetcher = $this->_fetch)) {
        $fetcher($popup);
      }
    }
  }
}
