<?php
class Migrations_Migration392 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('DELETE FROM s_core_subscribes WHERE listener LIKE "Shopware_Plugins_Frontend_Statistics_Bootstrap::onDispatchLoopShutdown"');
    }
}


