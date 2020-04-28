<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Domain\HostName {

  class Variants implements \Countable, \IteratorAggregate {

    private $_variants;

    public function __construct($hostName) {
      $this->_variants = $this->createVariants($hostName);
    }

    public function count() {
      return count($this->_variants);
    }

    public function getIterator() {
      return new \ArrayIterator($this->_variants);
    }

    private function createVariants($hostName) {
      $hostParts = explode('.', $hostName);
      //does it have more then two parts?
      if (is_array($hostParts) && count($hostParts) > 1) {
        $hostNames[] = '*';
        $hostParts = array_reverse($hostParts);
        // last part of the hostname to the buffer
        $buffer = $hostParts[0];
        $tldLength = strlen($hostParts[0]);
        for ($i = 1, $c = count($hostParts); $i < $c; $i++) {
          //prefix hostname parts in buffer with a "*." and replace tld with *
          if ($i > 1) {
            $hostNames[] = '*.'.substr($buffer, 0, -1 * $tldLength).'*';
          }
          $hostNames[] = $hostParts[$i].'.'.substr($buffer, 0, -1 * $tldLength).'*';
          //prefix hostname parts in buffer with a "*."
          $hostNames[] = '*.'.$buffer;
          //add hostname part to the buffer
          $buffer = $hostParts[$i].'.'.$buffer;
        }
        //try to load domain data for a list of wildcard domains like *.domain.tld
        usort(
          $hostNames,
          static function($a, $b) {
            if (strlen($a) === strlen($b)) {
              return 0;
            }
            return strlen($a) > strlen($b) ? -1 : 1;
          }
        );
        return $hostNames;
      }
      return [];
    }
  }
}
