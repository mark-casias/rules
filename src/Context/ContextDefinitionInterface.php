<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextDefinitionInterface.
 */

namespace Drupal\rules\Context;

/**
 * Interface for context definitions.
 */
interface ContextDefinitionInterface {

  /**
   * Returns a human readable label.
   *
   * @return string
   *   The label.
   */
  public function getLabel();

  /**
   * Sets the human readable label.
   *
   * @param string $label
   *   The label to set.
   *
   * @return $this
   */
  public function setLabel($label);

  /**
   * Returns a human readable description.
   *
   * @return string|null
   *   The description, or NULL if no description is available.
   */
  public function getDescription();

  /**
   * Sets the human readable description.
   *
   * @param string|null $description
   *   The description to set.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Returns the data type needed by the context.
   *
   * If the context is multiple-valued, this represents the type of each value.
   *
   * @return string
   *   The data type.
   */
  public function getDataType();

  /**
   * Sets the data type needed by the context.
   *
   * @param string $data_type
   *   The data type to set.
   *
   * @return $this
   */
  public function setDataType($data_type);

  /**
   * Returns whether the data is multi-valued, i.e. a list of data items.
   *
   * This is equivalent to checking whether the data definition implements the
   * \Drupal\Core\TypedData\ListDefinitionInterface interface.
   *
   * @return bool
   *   Whether the data is multi-valued; i.e. a list of data items.
   */
  public function isMultiple();

  /**
   * Sets whether the data is multi-valued.
   *
   * @param bool $multiple
   *   (optional) Whether the data is multi-valued. Defaults to TRUE.
   *
   * @return $this
   */
  public function setMultiple($multiple = TRUE);

  /**
   * Determines whether the context is required.
   *
   * For required data a non-NULL value is mandatory.
   *
   * @return bool
   *   Whether a data value is required.
   */
  public function isRequired();

  /**
   * Sets whether the data is required.
   *
   * @param bool $required
   *   (optional) Whether the data is multi-valued. Defaults to TRUE.
   *
   * @return $this
   */
  public function setRequired($required = TRUE);

  /**
   * Returns an array of validation constraints.
   *
   * See \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @return array
   *   An array of validation constraint definitions, keyed by constraint name.
   *   Each constraint definition can be used for instantiating
   *   \Symfony\Component\Validator\Constraint objects.
   */
  public function getConstraints();

  /**
   * Sets the array of validation constraints.
   *
   * NOTE: This will override any previously set constraints. In most cases
   * ContextDefinitionInterface::addConstraint() should be used instead.
   *
   * @param array $constraints
   *   The array of constraints. See
   *   \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @return $this
   *
   * @see \Drupal\rules\Context\ContextDefinitionInterface::addConstraint()
   */
  public function setConstraints(array $constraints);

  /**
   * Adds a validation constraint.
   *
   * See \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @param string $constraint_name
   *   The name of the constraint to add, i.e. its plugin id.
   * @param array|null $options
   *   The constraint options as required by the constraint plugin, or NULL.
   *
   * @return $this
   */
  public function addConstraint($constraint_name, $options = NULL);

  /**
   * Returns a validation constraint.
   *
   * See \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @param string $constraint_name
   *   The name of the the constraint, i.e. its plugin id.
   *
   * @return array
   *   A validation constraint definition which can be used for instantiating a
   *   \Symfony\Component\Validator\Constraint object.
   */
  public function getConstraint($constraint_name);

  /**
   * Returns the data definition of the defined context.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   */
  public function getDataDefinition();

}