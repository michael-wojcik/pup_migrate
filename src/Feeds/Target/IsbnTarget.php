<?php

namespace Drupal\pup_migrate\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\feeds\FieldTargetDefinition;
use Drupal\feeds\Feeds\Target\StringTarget;
use Nicebooks\Isbn\IsbnTools as Isbn;

/**
 * Defines a ISBN field mapper.
 *
 * @FeedsTarget(
 *   id = "isbn",
 *   field_types = {
 *     "isbn"
 *   }
 * )
 */
class IsbnTarget extends StringTarget {

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    $definition = FieldTargetDefinition::createFromFieldDefinition($field_definition)
      ->addProperty('value')
      ->markPropertyUnique('value');

    if ($field_definition->getType() === 'string') {
      $definition->markPropertyUnique('value');
    }
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    $value = trim($values['value']);

    // Checks if ISBN is numeric.
    if (!is_numeric($value)) {
      $value = '';
    }
    else {
      // Checks for valid ISBN.
      $isbn = new Isbn();
      if (strlen($value) == 0 || !$isbn->isValidIsbn($value)) {
        $value = '';
      }
    }

    $values['value'] = $value;
  }

}
