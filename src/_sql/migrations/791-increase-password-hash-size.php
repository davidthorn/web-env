<?php

class Migrations_Migration791 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE s_user MODIFY password VARCHAR(1024) NOT NULL;');
    }
}
