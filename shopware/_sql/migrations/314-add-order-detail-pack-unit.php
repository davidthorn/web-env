<?php
class Migrations_Migration314 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('
            ALTER TABLE `s_order_details`
            ADD `pack_unit` VARCHAR(255) NULL DEFAULT NULL ;
        ');
    }
}
