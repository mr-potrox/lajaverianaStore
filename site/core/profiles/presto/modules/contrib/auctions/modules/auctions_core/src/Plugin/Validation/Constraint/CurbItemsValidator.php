<?php

namespace Drupal\auctions_core\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\auctions_core\AuctionToolsTrait;

/**
 * Validates the Auction Items constraint.
 */
class CurbItemsValidator extends ConstraintValidator {

  use AuctionToolsTrait;

  /**
   * Validator 2.5 and upwards compatible execution context.
   *
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    $instantOnly = $entity->getInstantOnly();
    $buyNow = $entity->getPriceBuyNow();
    $itemPriceStarting = $entity->getPriceStarting();

    if ($entity->hasField('price_buy_now') && $entity->hasField('price_starting')  && !$instantOnly) {

      // startingPriceIsZero.
      if ($this->roundCents($itemPriceStarting) == 0) {
        $this->context
          ->buildViolation($constraint->startingPriceIsZero, ['%value' => $item->value])
          ->atPath('price_starting')
          ->addViolation();
      }

      // buyNowLowerThanStartingPrice.
      if ($buyNow > 0 && $buyNow < $itemPriceStarting) {
        $this->context
          ->buildViolation($constraint->buyNowLowerThanStartingPrice, ['%value' => $item->value])
          ->atPath('price_buy_now')
          ->addViolation();
      }

    }

    if ($entity->hasField('price_buy_now') && $entity->hasField('instant_only') && $instantOnly) {
      // instantOnlyWithoutBuyNowPrice.
      if ($buyNow == 0) {
        $this->context
          ->buildViolation($constraint->instantOnlyWithoutBuyNowPrice, ['%value' => $item->value])
          ->atPath('price_buy_now')
          ->addViolation();
      }
    }

  }

}
