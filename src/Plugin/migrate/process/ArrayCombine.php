<?php

namespace Drupal\pup_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Combines two arrays.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: array_combine
 *     source:
 *       - array_1
 *       - array_2
 *     keys:
 *       - target_id
 *       - target_revision_id
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "array_combine"
 * )
 */
class ArrayCombine extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Validates source values.
    if (
      empty($value[0]) ||
      empty($value[1])
    ) {
      throw new MigrateException($this->t('<em>array_combine</em> plugin expects two arrays as source values.'));
    }

    foreach ($value as &$v) {
      if (!is_array($v)) {
        $v = [$v];
      }
    }

    $results = [];
    $combined_array = array_combine($value[0], $value[1]);
    $keys = (!empty($this->configuration['keys']) && is_array($this->configuration['keys'])) ? $this->configuration['keys'] : [0, 1];
    $key_1 = reset($keys);
    $key_2 = end($keys);
    foreach ($combined_array as $key => $value) {
      $results[] = [
        $key_1 => $key,
        $key_2 => $value,
      ];
    }
    return $results;
  }

}
