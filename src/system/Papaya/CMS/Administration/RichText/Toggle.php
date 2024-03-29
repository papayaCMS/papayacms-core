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
namespace Papaya\CMS\Administration\RichText {

  use Papaya\CMS\CMSConfiguration as CMSSettings;
  use Papaya\Response;
  use Papaya\UI;
  use Papaya\XML\Element as XMLElement;

  class Toggle extends UI\Control\Interactive {

    const USE_RICHTEXT_FLAG = 'PAPAYA_ADMINISTRATION_USE_RICHTEXT';

    /**
     * @var bool|null
     */
    private $_isAvailable;

    /**
     * @var bool|null
     */
    private $_isActive;

    /**
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $reference = new UI\Reference($this->papaya()->request->getURL());
      if ($this->parameters()->has(self::USE_RICHTEXT_FLAG)) {
        $shouldBeActive = $this->parameters()->get(self::USE_RICHTEXT_FLAG, FALSE);
        if ($shouldBeActive !== $this->isActive()) {
          // change and reload
          $this->papaya()->session->setValue(
            self::USE_RICHTEXT_FLAG, $this->_isActive = $shouldBeActive
          );
          $reference->setParameters([]);
          $reload = new Response\Redirect($reference->get());
          $reload->send(TRUE);
        }
      }
      if ($this->isAvailable()) {
        $links = $parent->appendElement(
          'links',
          [
            'align' => 'right',
            'title' => new UI\Text\Translated('Richtext Editor')
          ]
        );
        $currentValue = $this->isActive() ? 1 : 0;
        foreach ([1 => 'On', 0 => 'Off'] as $value => $label) {
          $toggleReference = clone $reference;
          $toggleReference->setParameters([self::USE_RICHTEXT_FLAG => $value]);
          $links->appendElement(
            'link',
            [
              'title' => new UI\Text\Translated($label),
              'href' => (string)$toggleReference,
              'selected' => $currentValue === $value ? 'selected' : NULL
            ]
          );
        }
      }
    }

    /**
     * Return true if richtext editors are active currently
     *
     * @return bool
     */
    public function isActive() {
      if (NULL !== $this->_isActive) {
        return $this->_isActive;
      }
      return $this->_isActive = $this->papaya()->session->getValue(
        self::USE_RICHTEXT_FLAG,
        $this->isAvailable()
      );
    }

    /**
     * Return true if richtext editors are available at all
     *
     * @return bool
     */
    public function isAvailable() {
      if (NULL !== $this->_isAvailable) {
        return $this->_isAvailable;
      }
      if (isset($this->papaya()->administrationUser->options[CMSSettings::USE_RICHTEXT])) {
        return $this->_isAvailable = (bool)$this->papaya()->administrationUser->options[CMSSettings::USE_RICHTEXT];
      }
      return $this->_isAvailable = (bool)$this->papaya()->options->get(CMSSettings::USE_RICHTEXT, FALSE);
    }
  }

}
