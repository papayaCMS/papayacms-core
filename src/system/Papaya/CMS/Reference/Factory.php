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
namespace Papaya\CMS\Reference;

use Papaya\Application;
use Papaya\UI;

class Factory implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * @var string[]
   */
  private $_patterns = [
    'page' => /** @lang TEXT */'(
      ^
      (?:(?P<category_id>\\d+)\\.)? # category id
      (?P<page_id>\\d+) # page id
      (?:\\.(?P<language>[a-zA-Z]{2,4}))? # language identifier
      (?:\\.(?P<mode>[a-z]+))? # output mode
      (?:\\?(?P<query>[^?#]*))? # query string
      (?:\\#(?P<fragment>[^?#]*))? # query string
      $
     )x',
    'absolute_url' => /** @lang TEXT */'(
      ^
      (?P<url>\w+://[^\\r\\n]*)
      $
     )x',
    'relative_url' => /** @lang TEXT */'(
      ^
      (?P<path>[^?#]+)
      (?P<query>[^#]+)?
      (?P<fragment>[^\\s]+)?
      $
     )x'
  ];

  /**
   * @param $string
   * @return UI\Reference|Page
   */
  public function byString($string) {
    foreach ($this->_patterns as $type => $pattern) {
      if (\preg_match($pattern, $string, $matches)) {
        switch ($type) {
          case 'page' :
            $reference = $this->createPageReference($matches);
          break;
          case 'absolute_url' :
            $reference = new UI\Reference(new \Papaya\URL($string));
          break;
          case 'relative_url' :
          default :
            $reference = new UI\Reference(
              clone $this->papaya()->request->getURL()
            );
            $reference->setRelative($string);
        }
        return $reference;
      }
    }
    return new UI\Reference(
      clone $this->papaya()->request->getURL()
    );
  }

  /**
   * @param $options
   * @return Page
   */
  private function createPageReference($options) {
    $reference = new Page();
    $reference->papaya($this->papaya());
    if (!empty($options['page_id'])) {
      $reference->setPageId($options['page_id']);
    }
    if (!empty($options['language'])) {
      $reference->setPageLanguage($options['language']);
    }
    if (!empty($options['category_id'])) {
      $reference->setCategoryId($options['category_id']);
    }
    if (!empty($options['mode'])) {
      $reference->setOutputMode($options['mode']);
    }
    return $reference;
  }
}
