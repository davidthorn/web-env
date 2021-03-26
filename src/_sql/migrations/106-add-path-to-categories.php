<?php
class Migrations_Migration106 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE  `s_categories` ADD  `path` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `parent`;
EOD;

        $this->addSql($sql);
    }
}
