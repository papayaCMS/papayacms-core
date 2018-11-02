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
namespace Papaya\Administration\UI\Route {

  use Papaya\Administration\UI;
  use Papaya\Response;
  use Papaya\Template\Engine\XSLT;
  use Papaya\XML\Errors;

  /**
   * Popups are defined by an XML structure inside an XSLT template.
   *
   * @package Papaya\Administration\UI\Route
   */
  class Popup implements UI\Route {

    const XMLNS = 'http://papaya-cms.com/administration/popup';

    /**
     * @var string
     */
    private $_file;

    /**
     * @param string| $file
     */
    public function __construct($file) {
      $this->_file = $file;
    }

    /**
     * @param \Papaya\Administration\UI $ui
     * @param Address $path
     * @param int $level
     * @return null|Response|\Papaya\Administration\UI\Route
     */
    public function __invoke(UI $ui, Address $path, $level = 0) {
      $xslDocument = new \Papaya\XML\Document();
      $xslDocument->load($this->_file);
      $xslDocument->registerNamespace('popup', self::XMLNS);

      $popup = $xslDocument->xpath()->evaluate('//popup:popup[1]')[0];

      if ($popup) {
        $xmlDocument = new \Papaya\XML\Document();
        $xmlDocument->registerNamespace('popup', self::XMLNS);
        $popup = $xmlDocument->appendChild($xmlDocument->importNode($popup, TRUE));

        /** @var \Papaya\XML\Element $node */

        // translate phrases to current language
        $phrases = $ui->papaya()->administrationPhrases;
        foreach ($xmlDocument->xpath()->evaluate('//popup:phrase', $popup) as $node) {
          $identifier = $node->getAttribute('identifier') ?: $node->textContent;
          $node->setAttribute('identifier', $identifier);
          $node->textContent = $phrases->get($node->textContent, [], 'popups');
        }

        // add option values
        $options = $ui->papaya()->options;
        foreach ($xmlDocument->xpath()->evaluate('//popup:option[@name != ""]', $popup) as $node) {
          $node->textContent = $options->get($node->getAttribute('name'), '');
        }

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
      return new Error(
        'Broken route.', 500
      );
    }
  }
}
