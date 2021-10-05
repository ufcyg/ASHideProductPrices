<?php

declare(strict_types=1);

namespace ASHideProductPrices;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class ASHideProductPrices extends Plugin
{
    /** @inheritDoc */
    public function install(InstallContext $installContext): void
    {
    }

    /** @inheritDoc */
    public function postInstall(InstallContext $installContext): void
    {
    }

    /** @inheritDoc */
    public function update(UpdateContext $updateContext): void
    {
    }

    /** @inheritDoc */
    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    /** @inheritDoc */
    public function activate(ActivateContext $activateContext): void
    {
    }

    /** @inheritDoc */
    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    /** @inheritDoc */
    public function uninstall(UninstallContext $uninstallcontext): void
    {
        if ($uninstallcontext->keepUserData()) {
            parent::uninstall($uninstallcontext);

            return;
        }

        $connection = $this->container->get(Connection::class);

        $connection->executeUpdate('DROP TABLE IF EXISTS `as_product_customergroup_mapping`');
        $connection->executeUpdate('ALTER TABLE `product` DROP COLUMN `customergroups`');
        //$connection->executeUpdate('ALTER TABLE `customer_group` DROP COLUMN `products`');

        parent::uninstall($uninstallcontext);
    }
}
