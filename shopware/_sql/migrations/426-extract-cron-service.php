<?php
class Migrations_Migration426 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("DELETE FROM s_core_subscribes WHERE listener = 'Shopware_Plugins_Core_Cron_Bootstrap::onInitResourceCron'");
    }
}
