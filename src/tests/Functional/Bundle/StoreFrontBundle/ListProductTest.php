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

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class ListProductTest extends TestCase
{
    public function testProductRequirements()
    {
        $number = 'List-Product-Test';

        $context = $this->getContext();

        $data = $this->getProduct($number, $context);
        $data = array_merge(
            $data,
            $this->helper->getConfigurator(
                $context->getCurrentCustomerGroup(),
                $number
            )
        );
        $this->helper->createArticle($data);

        $product = $this->getListProduct($number, $context);

        static::assertNotEmpty($product->getId());
        static::assertNotEmpty($product->getVariantId());
        static::assertNotEmpty($product->getName());
        static::assertNotEmpty($product->getNumber());
        static::assertNotEmpty($product->getManufacturer());
        static::assertNotEmpty($product->getTax());
        static::assertNotEmpty($product->getUnit());

        static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\ListProduct', $product);
        static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit', $product->getUnit());
        static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer', $product->getManufacturer());

        static::assertNotEmpty($product->getPrices());
        static::assertNotEmpty($product->getPriceRules());
        foreach ($product->getPrices() as $price) {
            static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Price', $price);
            static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit', $price->getUnit());
            static::assertGreaterThanOrEqual(1, $price->getUnit()->getMinPurchase());
        }

        foreach ($product->getPriceRules() as $price) {
            static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule', $price);
        }

        static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Price', $product->getCheapestPrice());
        static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule', $product->getCheapestPriceRule());
        static::assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit', $product->getCheapestPrice()->getUnit());
        static::assertGreaterThanOrEqual(1, $product->getCheapestPrice()->getUnit()->getMinPurchase());

        static::assertNotEmpty($product->getCheapestPriceRule()->getPrice());
        static::assertNotEmpty($product->getCheapestPrice()->getCalculatedPrice());
        static::assertNotEmpty($product->getCheapestPrice()->getCalculatedPseudoPrice());
        static::assertNotEmpty($product->getCheapestPrice()->getFrom());

        static::assertGreaterThanOrEqual(1, $product->getUnit()->getMinPurchase());
        static::assertNotEmpty($product->getManufacturer()->getName());
    }

    /**
     * @param string $number
     *
     * @return ListProduct
     */
    private function getListProduct($number, ShopContext $context)
    {
        return Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::class)
            ->get($number, $context);
    }
}
