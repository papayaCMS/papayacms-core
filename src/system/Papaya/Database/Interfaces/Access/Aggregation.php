<?php

namespace Papaya\Database\Interfaces\Access {

  use Papaya\Database\Accessible\Aggregation as DatabaseAccessibleAggregation;

  /**
   * @deprecated Renamed to \Papaya\Database\Accessible\Aggregation
   */
  trait Aggregation {
    use DatabaseAccessibleAggregation;
  }
}
