<?php
class Migrations_Migration419 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("DELETE FROM s_core_subscribes WHERE listener = 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl'");
    }
}
