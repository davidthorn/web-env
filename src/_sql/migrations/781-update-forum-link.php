<?php


class Migrations_Migration781 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = 'UPDATE s_core_menu SET onclick = "window.open(\'https://forum.shopware.com\')" WHERE name = "Zum Forum"';
        $this->addSql($sql);
    }
}
