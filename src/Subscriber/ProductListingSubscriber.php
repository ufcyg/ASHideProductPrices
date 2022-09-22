<?php

declare(strict_types=1);

namespace ASHideProductPrices\Subscriber;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Defaults;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Events\ProductListingCollectFilterEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\Filter;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\SalesChannel\Listing\CachedProductListingRoute;

class ProductListingSubscriber implements EventSubscriberInterface
{
    /** @var SystemConfigService $systemConfigService */
    private SystemConfigService $systemConfigService;
    /** @var ContainerInterface $container */
    protected $container;

    private CacheInvalidator $cacheInvalidator;
    private Connection $connection;

    public function __construct(
        SystemConfigService $systemConfigService,
        CacheInvalidator $cacheInvalidator,
        Connection $connection
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->cacheInvalidator = $cacheInvalidator;
        $this->connection = $connection;
    }

    /**
     * @internal
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }

    // register event
    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCollectFilterEvent::class => 'addFilter'
        ];
    }

    public function addFilter(ProductListingCollectFilterEvent $event): void
    {
        $result = $this->container->get('product.repository')->search(new Criteria(), Context::createDefaultContext());

        foreach ($result as $id => $product) {
            $productIds[] = $id;
        }
        $this->invalidateProductIds($productIds);
        $this->invalidateListings($productIds);
        // fetch existing filters
        $filters = $event->getFilters();
        //fetch customer
        /** @var SalesChannelContext $context */
        $context = $event->getSalesChannelContext();
        /** @var CustomerEntity $customerEntity */
        $customerEntity = $context->getCustomer();
        if ($customerEntity == null) {
            //not logged in -> display NO item
            $filter = $this->noDisplayFilter();
        } else {
            //fetch customer group id
            $customerGroupId = $customerEntity->getGroupId();

            // get involved repositories / DB tables
            /** @var EntityRepositoryInterface productRepository */
            $productRepository = $this->container->get('product.repository');
            //fetch ALL products with their customergroups association
            $criteria = new Criteria();
            $criteria->addAssociation('customergroups');
            /** @var EntitySearchResult $result */
            $result = $productRepository->search($criteria, $context->getContext());

            // iterate through search result and check if the product is associated with the customer group
            /**
             * @var string $productId
             * @var ProductEntity $product
             * @var array $shownProductIds
             */
            $shownProductIds = null;
            /** @var ProductEntity $product */
            foreach ($result as $productId => $product) {
                /** @var CustomerGroupCollection $customergroupsExtension */
                $customergroupsExtension = $product->getExtension('customergroups');
                foreach ($customergroupsExtension as $thisCustomergroupId => $customerGroup) {
                    if ($thisCustomergroupId == $customerGroupId) {
                        if (!str_contains($product->getName(), 'Probenahmeset:')) {
                            // this product should be shown, add to array
                            $shownProductIds[] = $productId;
                        }
                    }
                }
            }

            if ($shownProductIds == null) {
                // customergroup is not assigned to any products
                $filter = $this->noDisplayFilter();
            } else { // this is what we want, filter for and display all associated products
                $filter = new Filter(
                    // unique name of the filter
                    'isAvailable',

                    // defines if this filter is active
                    true,

                    // Defines aggregations behind a filter. A filter can contain multiple aggregations like properties
                    [],

                    // defines the DAL filter which should be added to the criteria
                    new EqualsAnyFilter('id', $shownProductIds),

                    // defines the values which will be added as currentFilter to the result
                    true
                );
            }
        }

        // Add your custom filter
        $filters->add($filter);
    }

    private function invalidateListings($productIds): void
    {

        // invalidates product listings which are based on the product category assignment
        $this->cacheInvalidator->invalidate(
            array_map([CachedProductListingRoute::class, 'buildName'], $this->getProductCategoryIds($productIds))
        );
    }
    private function getProductCategoryIds(array $ids): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT DISTINCT LOWER(HEX(category_id)) as category_id
             FROM product_category_tree
             WHERE product_id IN (:ids)
             AND product_version_id = :version
             AND category_version_id = :version',
            ['ids' => Uuid::fromHexToBytesList($ids), 'version' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );
    }
    public function invalidateProductIds($productIds): void
    {
        // invalidates all routes which loads products in nested unknown objects, like cms listing elements or cross selling elements
        $this->cacheInvalidator->invalidate(
            array_map([EntityCacheKeyGenerator::class, 'buildProductTag'], $productIds)
        );
    }

    private function noDisplayFilter(): Filter
    {
        return $filter = new Filter(
            // unique name of the filter
            'isAvailable',

            // defines if this filter is active
            true,

            // Defines aggregations behind a filter. A filter can contain multiple aggregations like properties
            [],

            // new ContainsFilter('product.customergroups', $customerGroupId)
            // defines the DAL filter which should be added to the criteria
            new EqualsFilter('id', 'cfbd5018d38d41d8adca10d94fc8bdd6'), // this is the ID for the Standard-Kundengruppe which will be the same in every shopware installation and therefor cannot be a product ID

            // defines the values which will be added as currentFilter to the result
            false
        );
    }
}
