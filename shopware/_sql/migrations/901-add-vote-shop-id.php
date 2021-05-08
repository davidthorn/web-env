<?php

class Migrations_Migration901 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_articles_vote` ADD `shop_id` INT NULL DEFAULT NULL;');
        $this->addSql("SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Rating' LIMIT 1)");
        $this->addSql("ALTER TABLE `s_articles_vote` CHANGE `answer_date` `answer_date` DATETIME NULL DEFAULT NULL;");

        $sql = <<<'EOD'

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES
(@formId, 'displayOnlySubShopVotes', 'b:0;', 'Nur Subshopspezifische Bewertungen anzeigen', 'description', 'checkbox', 0, 0, 1);
EOD;

        $this->addSql($sql);

        $this->addSql("UPDATE s_core_config_elements SET scope = 1 WHERE name = 'votedisable'");
    }
}
