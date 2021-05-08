<?php
class Migrations_Migration105 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_core_sessions_backend` (
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `expiry` (`expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOD;

        $this->addSql($sql);
    }
}
