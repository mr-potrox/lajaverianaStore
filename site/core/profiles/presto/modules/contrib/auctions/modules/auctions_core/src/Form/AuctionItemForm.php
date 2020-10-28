<?php

namespace Drupal\auctions_core\Form;

use Drupal\auctions_core\Service\AuctionTools;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Session\AccountProxy;
use Drupal\auctions_core\AuctionToolsTrait;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Form controller for Auction Items edit forms.
 *
 * @ingroup auctions_core
 */
class AuctionItemForm extends ContentEntityForm {

  use AuctionToolsTrait;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * AuctionTools Helper service.
   *
   * @var \Drupal\auctions_core\Service\AuctionTools
   */
  protected $auctionTools;

  /**
   * +  * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a AuctionItemForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepository $entity_repository, EntityTypeBundleInfo $entity_type_bundle_info, TimeInterface $time, AccountProxy $account, AuctionTools $auctionTools, ConfigFactoryInterface $configFactory, MessengerInterface $messenger) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->account = $account;
    $this->auctionTools = $auctionTools;
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->get('auctions_core.tools'),
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\auctions_core\Entity\AuctionItem $entity */
    $itemSettings = $this->configFactory->get('auctions.item_settings');
    $entity = $this->entity;
    $workflow = $entity->getWorkflow();
    $workflows = $this->itemWorkflows();
    if ($workflow == 1 || $workflow == 3 || $workflow == 4) {
       $form['#access'] = FALSE;
       $this->messenger()->addError($this->t("You can not edit this auction Item.  Auction is @workflow", ['@workflow'=> $workflows[$workflow]]) );
    }

    $form_state->setRebuild();

    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    if (!$entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 50,
      ];
    }

    // Can not start with adding bids, reverse entity ref.
    $form['bids']['#access'] = FALSE;
    $form['workflow']['#access'] = FALSE;
    $form['revision_log_message']['#access'] = FALSE;
    $form['status']['#access'] = FALSE;
    $form['#after_build'][] = '::afterBuild';

    $form['price_starting']['widget'][0]['value']['#min'] = 0;
    $form['price_starting']['widget'][0]['value']['#field_prefix'] = '$';
    $form['price_starting']['widget'][0]['value']['#states'] = [
      'invisible' => [
        ':input[name="instant_only[value]"]' => ['checked' => TRUE],
      ],
      'required' => [
        ':input[name="instant_only[value]"]' => ['checked' => FALSE],
      ],
    ];

    $form['bid_step']['widget'][0]['value']['#field_prefix'] = '$';
    $form['bid_step']['widget'][0]['value']['#min'] = $itemSettings->get('bid-step');
// $form['bid_step']['widget'][0]['value']['#default_value'] = $itemSettings->get('bid-step');
    $form['bid_step']['widget'][0]['value']['#states'] = [
      'invisible' => [
        ':input[name="instant_only[value]"]' => ['checked' => TRUE],
      ],
      'required' => [
        ':input[name="instant_only[value]"]' => ['checked' => FALSE],
      ],
    ];

    $form['price_threshold']['widget'][0]['value']['#min'] = 0;
    $form['price_threshold']['widget'][0]['value']['#step'] = '1';
    $form['price_threshold']['widget'][0]['value']['#field_suffix'] = '%';
    $form['price_threshold']['widget'][0]['value']['#size'] = '4';
    $form['price_threshold']['widget'][0]['value']['#states'] = [
      'invisible' => [
        ':input[name="instant_only[value]"]' => ['checked' => TRUE],
      ],
      'required' => [
        ':input[name="price_buy_now[0][value]"]' => ['!value' => 0],
      ],
    ];

    $form['price_buy_now']['widget'][0]['value']['#field_prefix'] = '$';
    $form['price_buy_now']['widget'][0]['value']['#min'] = 0;
    $form['price_buy_now']['widget'][0]['value']['#states'] = [
      'required' => [
        ':input[name="instant_only[value]"]' => ['checked' => TRUE],
      ],
    ];

    return $form;
  }

  /**
   * Form element #after_build callback: Updates the entity with submitted data.
   */
  public function afterBuild(array $element, FormStateInterface $form_state) {
    // Only let use select one, change to radios.
    // Daterange has pass years, remove them..
    $now = new DrupalDateTime('now');
    $years = $this->configFactory->get('auctions.item_settings')->get('years-ahead');
    $current = $now->format('Y');
    $then = $now->modify("$years year")->format('Y');
    $rawRange = \range($current, $then);
    $yearRange = \array_combine($rawRange, $rawRange);
    $element['date']['widget'][0]['value']['year']['#options'] = $yearRange;
    $element['date']['widget'][0]['end_value']['year']['#options'] = $yearRange;
    // Rebuild the entity if #after_build is being called as part of a form
    // rebuild, i.e. if we are processing input.
    if ($form_state->isProcessingInput()) {
      $this->entity = $this->buildEntity($element, $form_state);
    }

    return $element;

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    // todo
    // change 'workflow' if start date is now (else this is only cron).
    //
    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId($this->account->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    // Round off any decimal before save.
    $priceMonitor = $this->itemPriceMonitor();
    foreach ($priceMonitor as $field => $method) {
      $price = $form_state->getValue($field);
      $roundPrice = $this->roundCents($price[0]['value']);
      $entity->$method($roundPrice);
    }

    $status = parent::save($form, $form_state);

    \Drupal::service('cache_tags.invalidator')->invalidateTags(['auction_item:'.$entity->id()]);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the <q>%label</q> Auction Item.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the <q>%label</q> Auction Item.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.auction_item.canonical', ['auction_item' => $entity->id()]);
  }

}
