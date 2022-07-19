<?php

declare(strict_types=1);

namespace ASHideProductPrices\Core\Checkout\Hide\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Psr\Container\ContainerInterface;
use SGSUtilities\Core\Utilities\SGSUtils;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

class HideCartCollector implements CartDataCollectorInterface
{

    public const TYPE = 'product';
    public const DATA_KEY = 'product';
    /** @var SGSUtils $utils */
    private $utils;
    /** @var ContainerInterface */
    protected $container;

    public function __construct(SGSUtils $utils)
    {
        $this->utils = $utils;
    }


    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }
    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
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
                unset($data->getElements()['product-' . $productId]);
                unset($original->getLineItems()->getElements()[$productId]);
            }
        }
    }
}
