<?php

namespace Drupal\block_node_condition\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a condition for nodes.
 *
 * @Condition(
 *   id = "node_condition",
 *   label = @Translation("Nodes"),
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class Node extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['nodes' => []] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'block_node_condition/block_node_condition';
    $form['nodes'] = [
      '#title' => $this->t('Display block only on selected nodes'),
      '#description' => $this->t('Use comma (,) to define multiple nodes.'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#tags' => TRUE,
      '#selection_handler' => 'default',
      '#default_value' => $this->getConfiguredNodeEntities(),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) : void {
    $this->configuration['nodes'] = array_column($form_state->getValue('nodes') ?? [], 'target_id');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (!$this->configuration['nodes'] && !$this->isNegated()) {
      return TRUE;
    }

    $node = $this->getContextValue('node');

    if ($node instanceof NodeInterface) {
      return in_array($node->id(), $this->configuration['nodes']);
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if ($this->configuration['nodes']) {
      if ($this->isNegated()) {
        return $this->t('This block will not be shown on the selected nodes.');
      }
      else {
        return $this->t('This block will be only be shown on the selected nodes.');
      }
    }
    return $this->t('This block will be shown on all nodes.');
  }

  /**
   * Load node entities configured by plugin configuration form.
   *
   * @return \Drupal\node\NodeInterfaceNodeInterface[]
   *   Array of node entities.
   */
  protected function getConfiguredNodeEntities() {
    $nodeIds = $this->configuration['nodes'];
    if (!$nodeIds) {
      return [];
    }

    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nodeIds);

    return $nodes;
  }

}
