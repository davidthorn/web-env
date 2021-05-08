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
use Shopware\Models\Category\Category;

/**
 * @group elasticSearch
 */
class SimilarProductsTest extends TestCase
{
    /**
     * setting up test config
     */
    public static function setUpBeforeClass(): void
    {
        Shopware()->Config()->offsetSet('similarlimit', 3);
    }

    /**
     * Cleaning up test config
     */
    public static function tearDownAfterClass(): void
    {
        Shopware()->Config()->offsetSet('similarlimit', 0);
    }

    public function testSimilarProduct()
    {
        $context = $this->getContext();

        $number = 'testSimilarProduct';
        $article = $this->getProduct($number, $context);

        $similarNumbers = [];
        $similarProducts = [];
        for ($i = 0; $i < 4; ++$i) {
            $similarNumber = 'SimilarProduct-' . $i;
            $similarNumbers[] = $similarNumber;
            $similarProduct = $this->getProduct($similarNumber, $context);
            $similarProducts[] = $similarProduct->getId();
        }
        $this->linkSimilarProduct($article->getId(), $similarProducts);

        $product = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::class)
            ->get($number, $context);

        $similarProducts = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface::class)
            ->get($product, $context);

        static::assertCount(4, $similarProducts);

        /** @var ListProduct $similarProduct */
        foreach ($similarProducts as $similarProduct) {
            static::assertInstanceOf('\Shopware\Bundle\StoreFrontBundle\Struct\ListProduct', $similarProduct);
            static::assertContains($similarProduct->getNumber(), $similarNumbers);
        }
    }

    public function testSimilarProductsList()
    {
        $context = $this->getContext();

        $number = 'testSimilarProductsList';
        $number2 = 'testSimilarProductsList2';

        $article = $this->getProduct($number, $context);
        $article2 = $this->getProduct($number2, $context);

        $similarNumbers = [];
        $similarProducts = [];
        for ($i = 0; $i < 4; ++$i) {
            $similarNumber = 'SimilarProduct-' . $i;
            $similarNumbers[] = $similarNumber;
            $similarProduct = $this->getProduct($similarNumber, $context);
            $similarProducts[] = $similarProduct->getId();
        }

        $this->linkSimilarProduct($article->getId(), $similarProducts);
        $this->linkSimilarProduct($article2->getId(), $similarProducts);

        $products = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::class)
            ->getList([$number, $number2], $context);

        $similarProductList = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface::class)
            ->getList($products, $context);

        static::assertCount(2, $similarProductList);

        /** @var ListProduct $product */
        foreach ($products as $product) {
            $similarProducts = $similarProductList[$product->getNumber()];

            static::assertCount(4, $similarProducts);

            /** @var ListProduct $similarProduct */
            foreach ($similarProducts as $similarProduct) {
                static::assertInstanceOf(ListProduct::class, $similarProduct);
                static::assertContains($similarProduct->getNumber(), $similarNumbers);
            }
        }
    }

    public function testSimilarProductsByCategory()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        $this->getProduct($number, $context, $category);

        for ($i = 0; $i < 4; ++$i) {
            $similarNumber = 'SimilarProduct-' . $i;
            $this->getProduct($similarNumber, $context, $category);
        }

        $helper = new Helper();
        $converter = new Converter();
        $convertedShop = $converter->convertShop($helper->getShop(1));
        if (!$convertedShop->getCurrency()) {
            $convertedShop->setCurrency($context->getCurrency());
        }

        $helper->refreshSearchIndexes(
            $convertedShop
        );

        $product = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::class)
            ->get($number, $context);

        $similar = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface::class)
            ->get($product, $context);

        static::assertCount(3, $similar);

        foreach ($similar as $similarProduct) {
            static::assertInstanceOf(
                ListProduct::class,
                $similarProduct
            );
        }
    }

    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additonally = null
    ) {
        $data = parent::getProduct($number, $context, $category);

        return $this->helper->createArticle($data);
    }

    /**
     * @param int   $productId
     * @param int[] $similarProductIds
     */
    private function linkSimilarProduct($productId, $similarProductIds)
    {
        foreach ($similarProductIds as $similarProductId) {
            Shopware()->Db()->insert('s_articles_similar', [
                'articleID' => $productId,
                'relatedarticle' => $similarProductId,
            ]);
        }
    }
}
