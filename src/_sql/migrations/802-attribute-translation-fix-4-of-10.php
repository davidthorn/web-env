<?php

class Migrations_Migration802 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        require_once __DIR__ . '/common/AttributeTranslationMigrationHelper.php';
        $helper = new AttributeTranslationMigrationHelper($this->connection);
        $helper->migrate(200000);
    }
}
