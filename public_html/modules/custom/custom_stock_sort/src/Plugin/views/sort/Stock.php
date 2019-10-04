<?php
namespace Drupal\custom_stock_sort\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\Date;

/**
 * Basic sort handler for Stock.
 *
 * @ViewsSort("stock")
 */
class Stock extends Date {

  /**
   * Called to add the sort to a query.
   */
  public function query() {
    $this->ensureMyTable();

    $date_alias = "UNIX_TIMESTAMP($this->tableAlias.$this->realField)";

    // Is this stock in the past?
    $this->query->addOrderBy(NULL,
      "UNIX_TIMESTAMP() > $date_alias",
      $this->options['order'],
      "in_past"
    );

    // How far in the past/future is this stock?
    $this->query->addOrderBy(NULL,
      "ABS($date_alias - UNIX_TIMESTAMP())",
      $this->options['order'],
      "distance_from_now"
    );
  }
}
