<?php

class Migrations_Migration794 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        UPDATE s_cms_static SET shop_ids = NULL WHERE shop_ids = '';
EOD;
        $this->addSql($sql);
    }
}
