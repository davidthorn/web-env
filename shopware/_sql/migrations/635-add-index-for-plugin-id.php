<?php

class Migrations_Migration635 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
ALTER TABLE `s_core_subscribes`
ADD INDEX `pluginID` (`pluginID`);
SQL;

        $this->addSql($sql);
    }
}
