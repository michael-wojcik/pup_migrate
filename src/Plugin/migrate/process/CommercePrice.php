<?php

namespace Drupal\pup_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Processes arrays of prices and currencies as commerce_price fields.
 *
 * Available configuration keys:
 * - source: Source property.
 *   Must be an array containing two arrays:
 *     - An array of three-digit currency codes
 *     - An array of price amounts (Format: 0.00)
 *   The two sub-arrays must be of equal length,
 *   and must be pre-sorted so that the numerical
 *   array indices correspond to the correct matching
 *   pairs of currencies and amounts.
 *     E.g.:
 *       - [ 0 => 'USD', 1 => 'GBP', 2 => 'EUR']
 *       - [ 0 => '1.00', 1 => '0.77', 2 => '0.88']
 *
 * See code example below:
 *
 * @code
 * source:
 *   prices
 * process:
 *   destination_field:
 *     plugin: price
 *     source:
 *       - currencies
 *       - prices
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 * @see https://drupal.stackexchange.com/a/255726
 *
 * @MigrateProcessPlugin(
 *   id = "price",
 *   handle_multiples = TRUE
 * )
 */
class CommercePrice extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Validates source values.
    if (
      empty($value[0]) ||
      empty($value[1]) ||
      count($value[0]) != count($value[1])
    ) {
      throw new MigrateException($this->t('Price plugin expects two arrays of equal length.'));
    }
    $currencies = $value[0];
    $prices = $value[1];
    foreach ($currencies as $currency) {
      if (!ctype_alpha($currency)) {
        throw new MigrateException($this->t('Currency codes must only contain letters.'));
      }
    }
    foreach ($prices as $price) {
      if (!is_numeric($price)) {
        throw new MigrateException($this->t('Prices must be valid numeric amounts.'));
      }
    }

    // Combines source values into price field sub-values.
    $results = [];
    $combined_costs = array_combine($value[0], $value[1]);
    foreach ($combined_costs as $currency => $price) {
      $results[] = [
        'number' => $price,
        'currency_code' => $currency,
      ];
    }
    return $results;
  }

}
