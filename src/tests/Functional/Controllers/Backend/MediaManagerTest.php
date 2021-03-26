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

namespace Shopware\Tests\Functional\Controllers\Backend;

class MediaManagerTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Creates a new album,
     * checks if the new album has inherited the parents settings
     * and deletes it afterwards
     */
    public function testAlbumInheritance()
    {
        $params = [
            'albumID' => '',
            'createThumbnails' => '',
            'iconCls' => 'sprite-target',
            'id' => '',
            'leaf' => false,
            'mediaCount' => '',
            'parentId' => '-11',
            'position' => '',
            'text' => 'PHPUNIT_ALBUM',
            'thumbnailSize' => '',
        ];

        $this->Request()->setMethod('POST')->setPost($params);
        $this->dispatch('/backend/MediaManager/saveAlbum');

        $jsonBody = $this->View()->getAssign();
        static::assertTrue($jsonBody['success']);

        $this->resetRequest();
        $this->resetResponse();
        $this->Request()->setMethod('GET')->setParams(['albumId' => '-11']);
        $this->dispatch('/backend/MediaManager/getAlbums');

        $jsonBody = $this->View()->getAssign();
        static::assertTrue($jsonBody['success']);

        $parentNode = $jsonBody['data'][0];
        $newAlbum = $parentNode['data'][count($parentNode['data']) - 1];

        static::assertEquals($parentNode['thumbnailSize'], $newAlbum['thumbnailSize']);
        static::assertEquals($parentNode['thumbnailHighDpi'], $newAlbum['thumbnailHighDpi']);
        static::assertEquals($parentNode['thumbnailHighDpiQuality'], $newAlbum['thumbnailHighDpiQuality']);
        static::assertEquals($parentNode['thumbnailQuality'], $newAlbum['thumbnailQuality']);
        static::assertEquals($parentNode['createThumbnails'], $newAlbum['createThumbnails']);
        static::assertEquals($parentNode['id'], $newAlbum['parentId']);
        static::assertEquals(1, $newAlbum['leaf']);
        static::assertEquals('sprite-target', $newAlbum['iconCls']);

        $this->resetRequest();
        $this->resetResponse();
        $this->Request()->setMethod('POST')->setPost([
            'albumID' => $newAlbum['id'],
        ]);
        $this->dispatch('/backend/MediaManager/removeAlbum');

        $jsonBody = $this->View()->getAssign();
        static::assertTrue($jsonBody['success']);
    }
}
