<?php

namespace Walee\Tracking\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $tableConfig = $connection
        ->newTable($installer->getTable('wt_config'))
        ->addColumn(
                'domain',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'domain'
        )
        ->addColumn(
                'username',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'username'
        )
        ->addColumn(
                'password',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'password'
        )
        ->addColumn(
                'is_view_tracking',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' =>  true],
                'is_view_tracking'
        )
        ->addColumn(
                'is_sales_tracking',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' =>  true],
                'is_sales_tracking'
        )
        ->addColumn(
                'is_add_cart_tracking',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' =>  true],
                'is_add_cart_tracking'
        )
        ->addColumn(
                'createdOn',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true, 'default' => Table::TIMESTAMP_INIT],
                'createdOn'
        )
        ->addColumn(
                'installed_version',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'installed_version'
        )
        ->addColumn(
                'last_synced',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true],
                'last_synced'
        )
        ->setComment('wt_config');

        if (!$installer->tableExists('wt_config')) {
            $connection->createTable($tableConfig);
        }

        // --------------------------------------------------------------------------------------------------------

        $matchDataTable = $connection
        ->newTable($installer->getTable('wt_match_data'))
        ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                12345,
                ['primary' => true, 'auto_increment' => true, 'nullable' =>  false],
                'id'
        )
        ->addColumn(
                'referrer',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'referrer'
        )
        ->addColumn(
                'ip',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'ip'
        )
        ->addColumn(
                'createdOn',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true, 'default' => Table::TIMESTAMP_INIT],
                'createdOn'
        );

        if (!$installer->tableExists('wt_match_data')) {
            $connection->createTable($matchDataTable);
        }

        // --------------------------------------------------------------------------------------------------------

        $salesTable = $connection
        ->newTable($installer->getTable('wt_sales'))
        ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                12345,
                ['primary' => true, 'auto_increment' => true, 'nullable' =>  false],
                'id'
        )
        ->addColumn(
                'referrer',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'referrer'
        )
        ->addColumn(
                'is_synced',
                Table::TYPE_SMALLINT,
                0,
                ['nullable' =>  true],
                'is_synced'
        )
        ->addColumn(
                'createdOn',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true, 'default' => Table::TIMESTAMP_INIT],
                'createdOn'
        )
        ->addColumn(
                'synced_on',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true],
                'synced_on'
        )
        ->addColumn(
                'orderId',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'orderId'
        )
        ->addColumn(
                'userPhone',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'userPhone'
        )
        ->addColumn(
                'currency',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'currency'
        )
        ->addColumn(
                'paymentMethod',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'paymentMethod'
        )
        ->addColumn(
                'userMail',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'userMail'
        )
        ->addColumn(
                'totalItems',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'totalItems'
        )
        ->addColumn(
                'totalPrice',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'totalPrice'
        )
        ->addColumn(
                'order_status',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'order_status'
        )
        ->addColumn(
                'lastUpdated',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true],
                'lastUpdated'
        );

        if (!$installer->tableExists('wt_sales')) {
            $connection->createTable($salesTable);
        }

        // --------------------------------------------------------------------------------------------------------

        $salesLineTable = $connection
        ->newTable($installer->getTable('wt_sale_line'))
        ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                12345,
                ['primary' => true, 'auto_increment' => true, 'nullable' =>  false],
                'id'
        )
        ->addColumn(
                'sale_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'sale_id'
        )
        ->addColumn(
                'proName',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'proName'
        )
        ->addColumn(
                'proId',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'proId'
        )
        ->addColumn(
                'proSku',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'proSku'
        )
        ->addColumn(
                'proPrice',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'proPrice'
        )
        ->addColumn(
                'proPriceSale',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'proPriceSale'
        )
        ->addColumn(
                'qty',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'qty'
        );

        if (!$installer->tableExists('wt_sale_line')) {
            $connection->createTable($salesLineTable);
        }

        // --------------------------------------------------------------------------------------------------------

        $viewsTable = $connection
        ->newTable($installer->getTable('wt_views'))
        ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                12345,
                ['primary' => true, 'auto_increment' => true, 'nullable' =>  false],
                'id'
        )
        ->addColumn(
                'referrer',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'referrer'
        )
        ->addColumn(
                'page',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'page'
        )
        ->addColumn(
                'createdOn',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true, 'default' => Table::TIMESTAMP_INIT],
                'createdOn'
        );

        if (!$installer->tableExists('wt_views')) {
            $connection->createTable($viewsTable);
        }

        // --------------------------------------------------------------------------------------------------------

        $cartsTable = $connection
        ->newTable($installer->getTable('wt_carts'))
        ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                12345,
                ['primary' => true, 'auto_increment' => true, 'nullable' =>  false],
                'id'
        )
        ->addColumn(
                'referrer',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'referrer'
        )
        ->addColumn(
                'proId',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'proId'
        )
        ->addColumn(
                'proSku',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'proSku'
        )
        ->addColumn(
                'proName',
                Table::TYPE_TEXT,
                null,
                ['nullable' =>  true],
                'proName'
        )
        ->addColumn(
                'proPrice',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'proPrice'
        )
        ->addColumn(
                'proPriceSale',
                Table::TYPE_INTEGER,
                null,
                ['nullable' =>  true],
                'proPriceSale'
        )
        ->addColumn(
                'createdOn',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' =>  true, 'default' => Table::TIMESTAMP_INIT],
                'createdOn'
        );

        if (!$installer->tableExists('wt_carts')) {
            $connection->createTable($cartsTable);
        }


        $installer->endSetup();
    }
}