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

namespace Shopware\Tests\Functional\Bundle\MediaBundle;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Models\Shop\Shop;

class FilesystemTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var array
     */
    private $testData;

    /**
     * @var array
     */
    private $testPaths = [
        'media/unknown/_phpunit_tmp.json',
        'media/unknown/5a/ef/21/_phpunit_tmp.json',
    ];

    /**
     * @var int
     */
    private $testFileSize;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaService = Shopware()->Container()->get(\Shopware\Bundle\MediaBundle\MediaServiceInterface::class);
        $this->testData = [
            'key' => 'myKey',
            'name' => 'name',
            'people' => [
                'great guy',
                'greater guy',
                'grumpy guy',
            ],
        ];
        $this->testFileSize = strlen(json_encode($this->testData));
    }

    protected function tearDown(): void
    {
        foreach ($this->testPaths as $file) {
            if ($this->mediaService->has($file)) {
                $this->mediaService->delete($file);
            }
        }
    }

    public function testFiles()
    {
        foreach ($this->testPaths as $file) {
            $this->_testWrite($file);
            $this->_testSize($file);
            $this->_testRead($file);
            $this->_testRename($file);
            $this->_testDelete($file);
        }
    }

    public function testUrlGeneration()
    {
        $file = current($this->testPaths);

        /** @var Shop $shop */
        $shop = Shopware()->Container()->get(\Shopware\Components\Model\ModelManager::class)->getRepository(Shop::class)->getActiveDefault();
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        if ($shop->getSecure()) {
            $baseUrl = 'https://' . $shop->getHost() . $shop->getBasePath() . '/';
        } else {
            $baseUrl = 'http://' . $shop->getHost() . $shop->getBasePath() . '/';
        }
        $mediaUrl = $baseUrl . $this->mediaService->encode($file);

        static::assertEquals($mediaUrl, $this->mediaService->getUrl($file));
        static::assertNull($this->mediaService->getUrl(''));
    }

    /**
     * @param string $path
     */
    private function _testWrite($path)
    {
        $content = json_encode($this->testData);
        $this->mediaService->write($path, $content);

        static::assertTrue($this->mediaService->has($path));
    }

    /**
     * @param string $path
     */
    private function _testRead($path)
    {
        $content = $this->mediaService->read($path);
        static::assertJsonStringEqualsJsonString($content, json_encode($this->testData));
    }

    /**
     * @param string $path
     */
    private function _testDelete($path)
    {
        static::assertTrue($this->mediaService->has($path));
        $this->mediaService->delete($path);
        static::assertFalse($this->mediaService->has($path));
    }

    /**
     * @param string $file
     */
    private function _testRename($file)
    {
        $tmpFile = 'media/unknown/_phpunit_tmp_rename.json';
        $this->mediaService->rename($file, $tmpFile);

        static::assertTrue($this->mediaService->has($tmpFile));
        static::assertFalse($this->mediaService->has($file));

        $this->_testRead($tmpFile);

        $this->mediaService->rename($tmpFile, $file);

        static::assertTrue($this->mediaService->has($file));
        static::assertFalse($this->mediaService->has($tmpFile));
    }

    /**
     * @param string $file
     */
    private function _testSize($file)
    {
        static::assertEquals($this->testFileSize, $this->mediaService->getSize($file));
    }
}
