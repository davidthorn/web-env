<?php

class Migrations_Migration702 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE  `s_emotion_element` ADD  `css_class` VARCHAR(255);
EOD;
        $this->addSql($sql);
    }
}
