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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;

class ManufacturerTest extends TestCase
{
    public function testManufacturerList()
    {
        $ids = [];
        $context = $this->getContext();

        $manufacturer = $this->helper->createManufacturer([
            'name' => 'testManufacturerList-1',
            'image' => 'Manufacturer-Cover-1',
            'link' => 'www.google.de?manufacturer=1',
            'metaTitle' => 'Meta title',
            'description' => 'Lorem ipsum manufacturer',
            'attribute' => ['id' => 100],
        ]);
        $ids[] = $manufacturer->getId();

        $manufacturer = $this->helper->createManufacturer([
            'name' => 'testManufacturerList-2',
            'image' => 'Manufacturer-Cover-2.jpg',
            'link' => 'www.google.de?manufacturer=2',
            'metaTitle' => 'Meta title',
            'description' => 'Lorem ipsum manufacturer',
            'attribute' => ['id' => 100],
        ]);
        $ids[] = $manufacturer->getId();

        $manufacturer = $this->helper->createManufacturer([
            'name' => 'testManufacturerList-2',
            'image' => 'Manufacturer-Cover-2.jpg',
            'link' => 'www.google.de?manufacturer=2',
            'metaTitle' => 'Meta title',
            'description' => 'Lorem ipsum manufacturer',
            'attribute' => ['id' => 100],
        ]);
        $ids[] = $manufacturer->getId();

        $manufacturers = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface::class)
            ->getList($ids, $context);

        /** @var Manufacturer $manufacturer */
        foreach ($manufacturers as $key => $manufacturer) {
            static::assertEquals($key, $manufacturer->getId());

            static::assertNotEmpty($manufacturer->getName());
            static::assertNotEmpty($manufacturer->getLink());
            static::assertNotEmpty($manufacturer->getDescription());
            static::assertNotEmpty($manufacturer->getMetaTitle());
            static::assertNotEmpty($manufacturer->getCoverFile());

            static::assertGreaterThanOrEqual(1, $manufacturer->getAttributes());
            static::assertTrue($manufacturer->hasAttribute('core'));
        }
    }
}
