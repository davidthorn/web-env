<?php

class Migrations_Migration734 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_emotion_shops` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `emotion_id` INT(11) NOT NULL ,
  `shop_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
EOD;

        $this->addSql($sql);
    }
}
