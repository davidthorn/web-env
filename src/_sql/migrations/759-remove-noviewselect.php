<?php

class Migrations_Migration759 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE s_categories DROP noviewselect");
    }
}
