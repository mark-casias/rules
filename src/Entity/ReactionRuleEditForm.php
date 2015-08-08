<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\ReactionRuleEditForm.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to edit a reaction rule.
 */
class ReactionRuleEditForm extends RulesComponentFormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['rule_events'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Events'),
    );
    $form['rule_conditions'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
    );
    $form['rule_actions'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Actions'),
    );
    $form['#attached']['library'] = array(
      'rules_ui/rules_ui',
    );
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('Reaction rule %label has been updated.', ['%label' => $this->entity->label()]));
  }
}
