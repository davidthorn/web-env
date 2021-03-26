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

namespace Shopware\Tests\Models\Order;

class ShippingTest extends \Enlight_Components_Test_TestCase
{
    public function testAddressFieldsLength()
    {
        $shipping = $this->getRandomShipping();

        $shippingId = $shipping->getId();
        $originalStreet = $shipping->getStreet();
        $originalZipCode = $shipping->getZipCode();

        $shipping->setStreet('This is a really really really long city name');
        $shipping->setZipCode('This is a really really really long zip code');

        Shopware()->Models()->persist($shipping);
        Shopware()->Models()->flush($shipping);
        Shopware()->Models()->clear();

        $shipping = Shopware()->Models()->getRepository('Shopware\Models\Order\Shipping')->find($shippingId);
        static::assertEquals('This is a really really really long city name', $shipping->getStreet());
        static::assertEquals('This is a really really really long zip code', $shipping->getZipCode());

        $shipping->setStreet($originalStreet);
        $shipping->setZipCode($originalZipCode);

        Shopware()->Models()->persist($shipping);
        Shopware()->Models()->flush($shipping);
    }

    private function getRandomShipping()
    {
        $ids = Shopware()->Models()->getRepository('Shopware\Models\Order\Shipping')
            ->createQueryBuilder('b')
            ->select('b.id')
            ->getQuery()
            ->getArrayResult();

        shuffle($ids);

        return Shopware()->Models()->getRepository('Shopware\Models\Order\Shipping')->find(array_shift($ids));
    }
}
