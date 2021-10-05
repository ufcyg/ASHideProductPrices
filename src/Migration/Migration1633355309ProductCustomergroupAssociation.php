<?php declare(strict_types=1);

namespace ASHideProductPrices\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1633355309ProductCustomergroupAssociation extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1633355309;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
                CREATE TABLE IF NOT EXISTS `as_product_customergroup_mapping` (
                    `customer_group_id` BINARY(16) NOT NULL,
                    `product_id` BINARY(16) NOT NULL,
                    `product_version_id` BINARY(16) NOT NULL,
                    `created_at` DATETIME(3) NOT NULL,
                    PRIMARY KEY (`customer_group_id`, `product_id`, `product_version_id`),
                    CONSTRAINT `fk.product_customer_group.customer_group_id` FOREIGN KEY (`customer_group_id`)
                      REFERENCES `customer_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT `fk.product_customer_group.product_id__product_version_id` FOREIGN KEY (`product_id`, `product_version_id`)
                      REFERENCES `product` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
                )
                    ENGINE = InnoDB
                    DEFAULT CHARSET = utf8mb4
                    COLLATE = utf8mb4_unicode_ci;
                SQL;
        $connection->executeStatement($sql);

        $this->updateInheritance($connection, 'product', 'customergroups');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
