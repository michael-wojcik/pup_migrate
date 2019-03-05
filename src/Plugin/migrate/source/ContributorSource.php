<?php

namespace Drupal\pup_migrate\Plugin\migrate\source;

use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drupal\migrate\Row;

/**
 * Source plugin for retrieving data via URLs.
 *
 * @MigrateSource(
 *   id = "contributor_source"
 * )
 */
class ContributorSource extends Url {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Checks for non-person contributor.
    if (empty($row->getSourceProperty('name_inverted'))) {
      $alt_name = (!empty($corporate_name = $row->getSourceProperty('corporate_name'))) ? $corporate_name : 'N/A';
      $row->setSourceProperty('name_inverted', $alt_name);
    }
    return parent::prepareRow($row);
  }

}
