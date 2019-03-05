<?php

namespace Drupal\pup_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;

/**
 * Extracts tagline.
 *
 * @MigrateProcessPlugin(
 *   id = "tagline_processor"
 * )
 */
class TaglineProcessor extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Decodes HTML entites.
    $tagline = Html::decodeEntities($value);
    // Searches for bolded taglines.
    if (preg_match('/(?<=<b>).*(?=<\/b>)/', $tagline, $matches)) {
      $tagline = reset($matches);
    }
    // Re-escape special chars for security.
    return Xss::filter($tagline);
  }

}
