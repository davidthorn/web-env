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

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;

class GraduatedPricesTest extends TestCase
{
    public function testSimpleGraduation()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);
        $graduation = $listProduct->getPrices();

        static::assertCount(3, $graduation);
        foreach ($graduation as $price) {
            static::assertEquals('PHP', $price->getCustomerGroup()->getKey());
            static::assertGreaterThan(0, $price->getCalculatedPrice());
        }
    }

    public function testGraduationWithMinimumPurchase()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $data['mainDetail']['minPurchase'] = 15;

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);
        $graduation = $listProduct->getPrices();

        static::assertCount(2, $graduation);

        /** @var Price $price */
        $price = array_shift($graduation);
        static::assertEquals(15, $price->getFrom());
        static::assertEquals(75, $price->getCalculatedPrice());

        /** @var Price $price */
        $price = array_shift($graduation);
        static::assertEquals(21, $price->getFrom());
        static::assertEquals(50, $price->getCalculatedPrice());
    }

    public function testFallbackGraduation()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $context->getCurrentCustomerGroup()->setKey('NOT');

        $listProduct = $this->helper->getListProduct($number, $context);
        $graduation = $listProduct->getPrices();

        static::assertCount(3, $graduation);
        foreach ($graduation as $price) {
            static::assertEquals('BACK', $price->getCustomerGroup()->getKey());
            static::assertGreaterThan(0, $price->getCalculatedPrice());
        }
    }

    public function testVariantGraduation()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number
        );
        $data = array_merge($data, $configurator);

        foreach ($data['variants'] as &$variant) {
            $variant['prices'] = $this->helper->getGraduatedPrices(
                $context->getCurrentCustomerGroup()->getKey(),
                100
            );
        }

        $variantNumber = $data['variants'][1]['number'];

        $this->helper->createArticle($data);

        /** @var Price $first */
        $listProduct = $this->helper->getListProduct($number, $context);
        static::assertCount(3, $listProduct->getPrices());
        $first = array_shift($listProduct->getPrices());
        static::assertEquals(100, $first->getCalculatedPrice());

        /** @var Price $first */
        $listProduct = $this->helper->getListProduct($variantNumber, $context);

        static::assertCount(3, $listProduct->getPrices());
        $first = array_shift($listProduct->getPrices());
        static::assertEquals(200, $first->getCalculatedPrice());
    }

    public function testGraduationByPriceGroup()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();

        $data = $this->getProduct($number, $context);
        $data['mainDetail']['prices'] = [[
            'from' => 1,
            'to' => null,
            'price' => 40,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey(),
            'pseudoPrice' => 110,
        ]];

        $priceGroup = $this->helper->createPriceGroup();
        $priceGroupStruct = $this->converter->convertPriceGroup($priceGroup);
        $context->setPriceGroups([
            $priceGroupStruct->getId() => $priceGroupStruct,
        ]);

        $data['priceGroupId'] = $priceGroup->getId();
        $data['priceGroupActive'] = true;

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);

        $graduations = $listProduct->getPrices();
        static::assertCount(3, $graduations);

        static::assertEquals(36, $graduations[0]->getCalculatedPrice());
        static::assertEquals(1, $graduations[0]->getFrom());
        static::assertEquals(4, $graduations[0]->getTo());

        static::assertEquals(32, $graduations[1]->getCalculatedPrice());
        static::assertEquals(5, $graduations[1]->getFrom());
        static::assertEquals(9, $graduations[1]->getTo());

        static::assertEquals(28, $graduations[2]->getCalculatedPrice());
        static::assertEquals(10, $graduations[2]->getFrom());
        static::assertEquals(null, $graduations[2]->getTo());
    }

    protected function getContext($shopId = 1)
    {
        $context = parent::getContext();

        $context->setFallbackCustomerGroup(
            $this->converter->convertCustomerGroup($this->helper->createCustomerGroup(['key' => 'BACK']))
        );

        return $context;
    }

    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ) {
        $data = parent::getProduct($number, $context, $category);

        $data['mainDetail']['prices'] = array_merge(
            $data['mainDetail']['prices'],
            $this->helper->getGraduatedPrices(
                $context->getFallbackCustomerGroup()->getKey(),
                -20
            )
        );

        return $data;
    }
}
