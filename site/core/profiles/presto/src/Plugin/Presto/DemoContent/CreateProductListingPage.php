<?php

namespace Drupal\presto\Plugin\Presto\DemoContent;

use Drupal\Core\Language\LanguageInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;

/**
 * Creates the product listing page.
 *
 * @PrestoDemoContent(
 *     id = "create_product_listing",
 *     type = \Drupal\presto\Installer\DemoContentTypes::ECOMMERCE,
 *     label = @Translation("Create product listing page"),
 *     weight = 10
 * )
 *
 * @package Drupal\presto\Plugin\Presto\DemoContent
 */
class CreateProductListingPage extends AbstractDemoContent {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createContent() {
    // Create node.
    $node = Node::create([
      'type' => 'page',
      'title' => 'Products',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'uid' => 1,
      'status' => 1,
      'promote' => 0,
      'field_fields' => [],
      'path' => [
        'alias' => '/products',
      ],
    ]);
    $node->save();

    // Create menu link.
    $menuLink = MenuLinkContent::create([
      'title' => 'Products',
      'link' => [
        'uri' => "internal:/node/{$node->id()}",
      ],
      'menu_name' => 'main',
      'weight' => 30,
      'expanded' => TRUE,
    ]);
    $menuLink->save();
  }

}
