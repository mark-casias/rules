<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesExpression\RulesAction.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\ContextHandlerTrait;
use Drupal\rules\Context\DataProcessorManager;
use Drupal\rules\Core\RulesActionManagerInterface;
use Drupal\rules\Engine\ActionExpressionInterface;
use Drupal\rules\Engine\ExecutionMetadataStateInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Engine\ExpressionBase;
use Drupal\rules\Engine\IntegrityCheckTrait;
use Drupal\rules\Engine\IntegrityViolationList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an executable action expression.
 *
 * This plugin is used to wrap action plugins and is responsible to setup all
 * the context necessary, instantiate the action plugin and to execute it.
 *
 * @RulesExpression(
 *   id = "rules_action",
 *   label = @Translation("Action"),
 *   form_class = "\Drupal\rules\Form\Expression\ActionForm"
 * )
 */
class RulesAction extends ExpressionBase implements ContainerFactoryPluginInterface, ActionExpressionInterface {

  use ContextHandlerTrait;
  use IntegrityCheckTrait;

  /**
   * The action manager used to instantiate the action plugin.
   *
   * @var \Drupal\rules\Core\RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   *   Contains the following entries:
   *   - action_id: The action plugin ID.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Core\RulesActionManagerInterface $action_manager
   *   The Rules action manager.
   * @param \Drupal\rules\Context\DataProcessorManager $processor_manager
   *   The data processor plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RulesActionManagerInterface $action_manager, DataProcessorManager $processor_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->actionManager = $action_manager;
    $this->processorManager = $processor_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('plugin.manager.rules_action'),
      $container->get('plugin.manager.rules_data_processor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    // If the plugin id has been set already, keep it if not specified.
    if (isset($this->configuration['action_id'])) {
      $configuration += [
        'action_id' => $this->configuration['action_id'],
      ];
    }
    return parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(ExecutionStateInterface $state) {
    $action = $this->actionManager->createInstance($this->configuration['action_id']);

    // We have to forward the context values from our configuration to the
    // action plugin.
    $this->mapContext($action, $state);

    $action->refineContextdefinitions();

    // Send the context value through configured data processor before executing
    // the action.
    $this->processData($action, $state);

    $action->execute();

    $auto_saves = $action->autoSaveContext();
    foreach ($auto_saves as $context_name) {
      // Mark parameter contexts for auto saving in the Rules state.
      $state->saveChangesLater($this->configuration['context_mapping'][$context_name]);
    }

    // Now that the action has been executed it can provide additional
    // context which we will have to pass back in the evaluation state.
    $this->mapProvidedContext($action, $state);
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinitions() {
    // Pass up the context definitions from the action plugin.
    $definition = $this->actionManager->getDefinition($this->configuration['action_id']);
    return !empty($definition['context']) ? $definition['context'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinition($name) {
    // Pass up the context definitions from the action plugin.
    $definition = $this->actionManager->getDefinition($this->configuration['action_id']);
    if (empty($definition['context'][$name])) {
      throw new ContextException(sprintf("The %s context is not a valid context.", $name));
    }
    return $definition['context'][$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    if (!empty($this->configuration['action_id'])) {
      $definition = $this->actionManager->getDefinition($this->configuration['action_id']);
      return $this->t('Action: @label', ['@label' => $definition['label']]);
    }
    return parent::getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormHandler() {
    if (isset($this->pluginDefinition['form_class'])) {
      $class_name = $this->pluginDefinition['form_class'];
      return new $class_name($this, $this->actionManager);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state) {
    $violation_list = new IntegrityViolationList();
    if (empty($this->configuration['action_id'])) {
      $violation_list->addViolationWithMessage($this->t('Action plugin ID is missing'));
      return $violation_list;
    }
    if (!$this->actionManager->hasDefinition($this->configuration['action_id'])) {
      $violation_list->addViolationWithMessage($this->t('Action plugin %plugin_id does not exist', [
        '%plugin_id' => $this->configuration['action_id'],
      ]));
      return $violation_list;
    }

    $action = $this->actionManager->createInstance($this->configuration['action_id']);

    return $this->doCheckIntegrity($action, $metadata_state);
  }

}
