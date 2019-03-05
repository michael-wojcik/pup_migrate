<?php

namespace Drupal\pup_migrate\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Obtain JSON data for migration.
 *
 * @DataParser(
 *   id = "filtered_json",
 *   title = @Translation("Filtered JSON")
 * )
 */
class FilteredJson extends Json implements ContainerFactoryPluginInterface {

  /**
   * Retrieves the JSON data and returns it as an array.
   *
   * @param string $url
   *   URL of a JSON feed.
   *
   * @return array
   *   The selected data to be iterated.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  protected function getSourceData($url) {
    $response = $this->getDataFetcherPlugin()->getResponseContent($url);

    // Convert objects to associative arrays.
    $source_data = json_decode($response, TRUE);

    // If json_decode() has returned NULL, it might be that the data isn't
    // valid utf8 - see http://php.net/manual/en/function.json-decode.php#86997.
    if (is_null($source_data)) {
      $utf8response = utf8_encode($response);
      $source_data = json_decode($utf8response, TRUE);
    }

    // Backwards-compatibility for depth selection.
    if (is_int($this->itemSelector)) {
      return $this->selectByDepth($source_data);
    }

    // Otherwise, we're using xpath-like selectors.
    $selectors = explode('/', trim($this->itemSelector, '/'));
    foreach ($selectors as $selector) {
      if (!empty($selector)) {
        // Allows for xpath-like filtering.
        // E.g., 'SELECTOR[KEY=VALUE]' to find selector with matching subvalue.
        preg_match('/\[(\w*=\w*)\]/', $selector, $matches);
        // If selector contains a filter (e.g., [KEY=VALUE])...
        if (!empty($matches[1])) {
          // Extracts selector itself.
          $selector = strtok($selector, '[');
          // Extracts filter key.
          $filter_key = strtok($matches[1], '=');
          // Extracts filter value.
          $filter_value = strtok('=');
          // Searches current level of data using selector with its filter.
          $has_filter_value = FALSE;
          foreach ($source_data[$selector] as $key => $value) {
            // Returns first matching value.
            if (!empty($value[$filter_key]) && $value[$filter_key] == $filter_value) {
              $source_data = $source_data[$selector][$key];
              $has_filter_value = TRUE;
              break;
            }
          }
          if (!$has_filter_value) {
            $source_data = $source_data[$selector];
          }
        }
        // Returns data based on simple selector.
        else {
          $source_data = $source_data[$selector];
        }
      }
    }
    return $source_data;
  }

}
