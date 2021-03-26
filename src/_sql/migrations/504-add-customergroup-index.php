<?php
class Migrations_Migration504 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
ALTER TABLE `s_user` ADD INDEX(`customergroup`);
SQL;

        $this->addSql($sql);
    }
}
