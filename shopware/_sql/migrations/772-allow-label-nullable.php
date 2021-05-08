<?php

class Migrations_Migration772 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_attribute_configuration` CHANGE `label` `label` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `s_attribute_configuration` DROP `plugin_id`;");
    }
}
