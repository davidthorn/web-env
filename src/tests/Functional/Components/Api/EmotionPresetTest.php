<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Components\Api;

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Models\Emotion\Preset;

/**
 * @group EmotionPreset
 */
class EmotionPresetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EmotionPreset
     */
    private $resource;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get(\Doctrine\DBAL\Connection::class);
        $this->connection->beginTransaction();
        $this->connection->executeQuery('DELETE FROM s_emotion_presets');
        $this->connection->executeQuery('DELETE FROM s_core_plugins');
        $this->resource = Shopware()->Container()->get(\Shopware\Components\Api\Resource\EmotionPreset::class);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testCreate()
    {
        $this->resource->create(['name' => 'test', 'presetData' => '[]']);
        static::assertCount(1, $this->connection->fetchAll('SELECT * FROM s_emotion_presets'));
    }

    public function testCreateReturnsPersistedEntity()
    {
        $preset = $this->resource->create(['name' => 'test', 'presetData' => '[]']);
        static::assertInstanceOf(Preset::class, $preset);
        static::assertNotNull($preset->getId());
    }

    public function testPresetDataIsRequiredOnCreate()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->create(['name' => 'Test']);
    }

    public function testPresetNameIsRequiredOnCreate()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->create(['presetData' => '[]']);
    }

    public function testPresetNameCannotBeEmpty()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $this->resource->create(['name' => '', 'presetData' => '[]']);
    }

    public function testPresetDataCanNotBeEmpty()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $this->resource->create(['name' => 'test', 'presetData' => '']);
    }

    public function testListContainsRequiredPlugins()
    {
        $this->insertPreset(
            [
                'name' => 'First preset',
                'preset_data' => '[]',
                'required_plugins' => json_encode([
                    ['name' => 'SwagLiveShopping', 'label' => 'Live shopping', 'version' => '1.0.0'],
                ]),
            ]
        );

        $preset = array_shift($this->resource->getList());
        $preset = $this->removeIds($preset);

        static::assertEquals([
            'name' => 'First preset',
            'label' => 'First preset',
            'description' => 'First preset',
            'premium' => false,
            'custom' => true,
            'thumbnail' => null,
            'preview' => null,
            'presetData' => '[]',
            'requiredPlugins' => [
                [
                    'plugin_name' => 'SwagLiveShopping',
                    'plugin_label' => 'Live shopping',
                    'active' => false,
                    'plugin_exists' => false,
                    'installed' => false,
                    'valid' => false,
                    'current_version' => null,
                    'updateRequired' => true,
                    'name' => 'SwagLiveShopping',
                    'version' => '1.0.0',
                    'label' => 'Live shopping',
                ],
            ],
            'assetsImported' => true,
        ], $preset);
    }

    public function testListContainsRequiredPluginsWithLocalPlugins()
    {
        $this->insertPreset(
            [
                'name' => 'First preset',
                'preset_data' => '[]',
                'required_plugins' => json_encode([
                    ['name' => 'SwagLiveShopping', 'label' => 'Live shopping', 'version' => '1.0.0'],
                ]),
            ],
            [],
            [['name' => 'SwagLiveShopping', 'label' => 'Live shopping', 'version' => '2.0.0', 'installation_date' => '2017-01-01', 'active' => 1]]
        );

        $preset = array_shift($this->resource->getList());
        $preset = $this->removeIds($preset);

        static::assertEquals([
            'name' => 'First preset',
            'label' => 'First preset',
            'description' => 'First preset',
            'premium' => false,
            'custom' => true,
            'thumbnail' => null,
            'preview' => null,
            'presetData' => '[]',
            'requiredPlugins' => [
                [
                    'plugin_name' => 'SwagLiveShopping',
                    'plugin_label' => 'Live shopping',
                    'active' => 1,
                    'plugin_exists' => 1,
                    'installed' => 1,
                    'current_version' => '2.0.0',
                    'updateRequired' => false,
                    'valid' => true,
                    'name' => 'SwagLiveShopping',
                    'version' => '1.0.0',
                    'label' => 'Live shopping',
                ],
            ],
            'assetsImported' => true,
        ], $preset);
    }

    public function testPluginWithExactSameVersion()
    {
        $this->insertPreset(
            [
                'name' => 'First preset',
                'preset_data' => '[]',
                'required_plugins' => json_encode([
                    ['name' => 'SwagLiveShopping', 'label' => 'Live shopping', 'version' => '2.0.0'],
                ]),
            ],
            [],
            [['name' => 'SwagLiveShopping', 'label' => 'Live shopping', 'version' => '2.0.0', 'installation_date' => '2017-01-01', 'active' => 1]]
        );

        $preset = array_shift($this->resource->getList());
        $preset = $this->removeIds($preset);

        static::assertEquals([
            'name' => 'First preset',
            'label' => 'First preset',
            'description' => 'First preset',
            'premium' => false,
            'custom' => true,
            'thumbnail' => null,
            'preview' => null,
            'presetData' => '[]',
            'requiredPlugins' => [
                [
                    'plugin_name' => 'SwagLiveShopping',
                    'plugin_label' => 'Live shopping',
                    'active' => 1,
                    'plugin_exists' => 1,
                    'installed' => 1,
                    'valid' => true,
                    'current_version' => '2.0.0',
                    'updateRequired' => false,
                    'name' => 'SwagLiveShopping',
                    'version' => '2.0.0',
                    'label' => 'Live shopping',
                ],
            ],
            'assetsImported' => true,
        ], $preset);
    }

    public function testListItemWithTranslation()
    {
        $this->insertPreset(
            ['name' => 'First preset', 'preset_data' => '[]'],
            [['label' => 'English label', 'description' => 'English description', 'locale' => 'en_GB']]
        );

        $preset = array_shift($this->resource->getList('en_GB'));
        $preset = $this->removeIds($preset);

        static::assertSame([
            'name' => 'First preset',
            'label' => 'English label',
            'description' => 'English description',
            'premium' => false,
            'custom' => true,
            'thumbnail' => null,
            'preview' => null,
            'presetData' => '[]',
            'requiredPlugins' => [],
            'assetsImported' => true,
            'locale' => 'en_GB',
        ], $preset);
    }

    public function testListItemsWithAndWithoutTranslation()
    {
        $this->insertPreset(
            ['name' => 'First preset', 'preset_data' => '[]'],
            [['label' => 'English label', 'description' => 'English description', 'locale' => 'en_GB']]
        );

        $this->insertPreset(
            ['name' => 'Second preset', 'preset_data' => '[]'],
            [['label' => 'German label', 'description' => 'German description', 'locale' => 'de_DE']]
        );

        $presets = $this->resource->getList('en_GB');
        $presets = array_map(function ($preset) {
            return $this->removeIds($preset);
        }, $presets);

        static::assertSame(
            [
                [
                    'name' => 'First preset',
                    'label' => 'English label',
                    'description' => 'English description',
                    'premium' => false,
                    'custom' => true,
                    'thumbnail' => null,
                    'preview' => null,
                    'presetData' => '[]',
                    'requiredPlugins' => [],
                    'assetsImported' => true,
                    'locale' => 'en_GB',
                ],
                [
                    'name' => 'Second preset',
                    'label' => 'Second preset',
                    'description' => 'Second preset',
                    'premium' => false,
                    'custom' => true,
                    'thumbnail' => null,
                    'preview' => null,
                    'presetData' => '[]',
                    'requiredPlugins' => [],
                    'assetsImported' => true,
                ],
            ],
            $presets
        );
    }

    public function testCreateWithTranslation()
    {
        $preset = $this->resource->create([
            'name' => 'Test preset',
            'presetData' => '[]',
            'translations' => [
                ['locale' => 'de_DE', 'label' => 'German', 'description' => 'German'],
                ['locale' => 'en_GB', 'label' => 'English', 'description' => 'English'],
            ],
        ]);

        static::assertNotNull($preset->getId());
        $english = array_shift($this->resource->getList('en_GB'));

        $english = $this->removeIds($english);
        static::assertSame([
            'name' => 'Test-preset',
            'label' => 'English',
            'description' => 'English',
            'premium' => false,
            'custom' => true,
            'thumbnail' => null,
            'preview' => null,
            'presetData' => '[]',
            'requiredPlugins' => [],
            'assetsImported' => true,
            'locale' => 'en_GB',
        ], $english);

        $german = array_shift($this->resource->getList('de_DE'));
        $german = $this->removeIds($german);
        static::assertSame([
            'name' => 'Test-preset',
            'label' => 'German',
            'description' => 'German',
            'premium' => false,
            'custom' => true,
            'thumbnail' => null,
            'preview' => null,
            'presetData' => '[]',
            'requiredPlugins' => [],
            'assetsImported' => true,
            'locale' => 'de_DE',
        ], $german);
    }

    public function testUpdate()
    {
        $preset = $this->resource->create(['name' => 'test', 'presetData' => json_encode(['data'])]);
        $updated = $this->resource->update($preset->getId(), ['name' => 'updated']);
        static::assertSame('updated', $updated->getName());
        static::assertCount(1, $this->connection->fetchAll("SELECT * FROM s_emotion_presets WHERE name = 'updated'"));
    }

    public function testUpdateWithInvalidId()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->update(1000, ['name' => 'test']);
    }

    public function testDelete()
    {
        $preset = $this->resource->create(['name' => 'test', 'presetData' => 'data']);
        $this->resource->delete($preset->getId());
        static::assertEmpty($this->resource->getList());
    }

    public function testValidateExistingName()
    {
        $this->expectException('Shopware\Components\Api\Exception\CustomValidationException');
        $this->resource->create(['name' => 'test', 'presetData' => '[]']);
        $this->resource->create(['name' => 'test', 'presetData' => '[]']);
    }

    public function testDeleteWithInvalidId()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->delete(null);
    }

    public function testDeleteWithNoneExistingId()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->delete(1000);
        static::assertEmpty($this->resource->getList());
    }

    public function testDeleteNoneCustomPreset()
    {
        $this->expectException('Shopware\Components\Api\Exception\PrivilegeException');
        $preset = $this->resource->create(['name' => 'test', 'presetData' => '[]', 'custom' => false]);
        $this->resource->delete($preset->getId());
    }

    /**
     * @return array
     */
    private function removeIds(array $item)
    {
        unset($item['id']);

        return $item;
    }

    private function insertPreset(array $preset, array $translations = [], array $localPlugins = [])
    {
        $this->connection->insert('s_emotion_presets', $preset);
        $id = $this->connection->lastInsertId('s_emotion_presets');

        foreach ($translations as $translation) {
            $translation['presetID'] = $id;
            $this->connection->insert('s_emotion_preset_translations', $translation);
        }

        foreach ($localPlugins as $plugin) {
            $this->connection->insert('s_core_plugins', $plugin);
        }
    }
}
