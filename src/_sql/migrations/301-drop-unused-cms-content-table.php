<?php
class Migrations_Migration301 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            DROP TABLE IF EXISTS `s_cms_content`;
EOD;

        $this->addSql($sql);
    }
}
