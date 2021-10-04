<?php

declare(strict_types=1);

namespace ASHideProductPrices\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingCollectFilterEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\Filter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\MaxAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductListingSubscriber implements EventSubscriberInterface
{
    /** @var SystemConfigService $systemConfigService */
    private SystemConfigService $systemConfigService;

    public function __construct(
        SystemConfigService $systemConfigService
    ) {
        $this->systemConfigService = $systemConfigService;
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
        // fetch existing filters
        $filters = $event->getFilters();
        $request = $event->getRequest();
        //new ContainsFilter();
        $filtered = (bool) $request->get('isCloseout');

        $filter = new Filter(
        // unique name of the filter
            'isCloseout',

            // defines if this filter is active
            true,

            // Defines aggregations behind a filter. A filter can contain multiple aggregations like properties
            [
                new FilterAggregation(
                    'active-filter',
                    new MaxAggregation('active', 'product.isCloseout'),
                    [new EqualsFilter('product.isCloseout', true)]
                ),
            ],


            // defines the DAL filter which should be added to the criteria
            new EqualsFilter('product.isCloseout', false),

            // defines the values which will be added as currentFilter to the result
            $filtered
        );

        // Add your custom filter
        $filters->add($filter);
    }
}