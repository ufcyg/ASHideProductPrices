<?php

declare(strict_types=1);

namespace ASHideProductPrices\Core\Checkout\Hide\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Psr\Container\ContainerInterface;

class HideCartProcessor implements CartProcessorInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;
    public const TYPE = 'product';
    /** @var ContainerInterface */
    protected $container;

    public function __construct(
        LineItemFactoryRegistry $factory,
        QuantityPriceCalculator $quantityPriceCalculator
    ) {
        $this->factory = $factory;
        $this->quantityPriceCalculator = $quantityPriceCalculator;
    }
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        /** @var EntityRepositoryInterface $productCustomergroupMappingRepo */
        $productCustomergroupMappingRepo = $this->container->get('as_product_customergroup_mapping.repository');
        $customerGroup = $context->getCurrentCustomerGroup();
        $customerGroupId = $customerGroup->getId();
        /** @var LineItemCollection $lineItems */
        $lineItems = $original->getLineItems();
        /** @var LineItem $lineItem */
        foreach ($lineItems as $lineItem) {
            $productId = $lineItem->getReferencedId();
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('customerGroupId', $customerGroupId));
            $criteria->addFilter(new EqualsFilter('productId', $productId));
            /** @var IdSearchResult $result */
            $result = $productCustomergroupMappingRepo->searchIds($criteria, $context->getContext());
            if ($result->getTotal() == 0) {

                $toCalculate->add($lineItem);
            }
        }
    }
}
