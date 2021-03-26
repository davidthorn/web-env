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

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;

abstract class TestCase extends \Enlight_Components_Test_TestCase
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Converter
     */
    protected $converter;

    protected function setUp(): void
    {
        $this->helper = new Helper();
        $this->converter = new Converter();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->helper->cleanUp();
        parent::tearDown();
    }

    /**
     * @param array $products
     *
     * @return Article[]
     */
    public function createProducts($products, ShopContext $context, Category $category)
    {
        $articles = [];
        foreach ($products as $number => $additionally) {
            $articles[$number] = $this->createProduct(
                $number,
                $context,
                $category,
                $additionally
            );
        }

        return $articles;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group
     */
    public function getEkCustomerGroup()
    {
        return $this->converter->convertCustomerGroup(
            Shopware()->Container()->get(\Shopware\Components\Model\ModelManager::class)->find(\Shopware\Models\Customer\Group::class, 1)
        );
    }

    /**
     * @param array                $products
     * @param array                $expectedNumbers
     * @param Category             $category
     * @param ConditionInterface[] $conditions
     * @param FacetInterface[]     $facets
     * @param SortingInterface[]   $sortings
     * @param bool                 $variantSearch
     *
     * @return ProductNumberSearchResult
     */
    protected function search(
        $products,
        $expectedNumbers,
        $category = null,
        $conditions = [],
        $facets = [],
        $sortings = [],
        $context = null,
        array $configs = [],
        $variantSearch = false
    ) {
        if ($context === null) {
            $context = $this->getContext();
        }

        if ($category === null) {
            $category = $this->helper->createCategory();
        }

        $config = Shopware()->Container()->get(\Shopware_Components_Config::class);
        $originals = [];
        foreach ($configs as $key => $value) {
            $originals[$key] = $config->get($key);
            $config->offsetSet($key, $value);
        }

        $this->createProducts($products, $context, $category);

        $this->helper->refreshSearchIndexes($context->getShop());

        $criteria = new Criteria();

        $this->addCategoryBaseCondition($criteria, $category, $conditions, $context);

        $this->addConditions($criteria, $conditions);

        $this->addFacets($criteria, $facets);

        $this->addSortings($criteria, $sortings);

        $criteria->offset(0)->limit(4000);

        if ($variantSearch) {
            $search = Shopware()->Container()->get(\Shopware\Bundle\SearchBundle\VariantSearch::class);
        } else {
            $search = Shopware()->Container()->get(\Shopware\Bundle\SearchBundle\ProductNumberSearchInterface::class);
        }

        $result = $search->search($criteria, $context);

        foreach ($originals as $key => $value) {
            $config->offsetSet($key, $value);
        }

        $this->assertSearchResult($result, $expectedNumbers);

        return $result;
    }

    /**
     * @param array $conditions
     */
    protected function addCategoryBaseCondition(
        Criteria $criteria,
        Category $category,
        $conditions,
        ShopContext $context
    ) {
        if ($category) {
            $criteria->addBaseCondition(
                new CategoryCondition([$category->getId()])
            );
        }
    }

    /**
     * @param ConditionInterface[] $conditions
     */
    protected function addConditions(Criteria $criteria, $conditions)
    {
        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }
    }

    /**
     * @param FacetInterface[] $facets
     */
    protected function addFacets(Criteria $criteria, $facets)
    {
        foreach ($facets as $facet) {
            $criteria->addFacet($facet);
        }
    }

    /**
     * @param SortingInterface[] $sortings
     */
    protected function addSortings(Criteria $criteria, $sortings)
    {
        foreach ($sortings as $sorting) {
            $criteria->addSorting($sorting);
        }
    }

    /**
     * @param string $number
     * @param array  $additionally
     *
     * @return Article
     */
    protected function createProduct(
        $number,
        ShopContext $context,
        Category $category,
        $additionally
    ) {
        $data = $this->getProduct(
            $number,
            $context,
            $category,
            $additionally
        );

        return $this->helper->createArticle($data);
    }

    protected function assertSearchResult(
        ProductNumberSearchResult $result,
        array $expectedNumbers
    ) {
        $numbers = array_map(function (BaseProduct $product) {
            return $product->getNumber();
        }, $result->getProducts());

        foreach ($numbers as $number) {
            static::assertContains($number, $expectedNumbers, sprintf('Product with number: `%s` found but not expected', $number));
        }
        foreach ($expectedNumbers as $number) {
            static::assertContains($number, $numbers, sprintf('Expected product number: `%s` not found', $number));
        }

        static::assertCount(count($expectedNumbers), $result->getProducts());
        static::assertEquals(count($expectedNumbers), $result->getTotalCount());
    }

    protected function assertSearchResultSorting(
        ProductNumberSearchResult $result,
        $expectedNumbers
    ) {
        $productResult = array_values($result->getProducts());

        /** @var BaseProduct $product */
        foreach ($productResult as $index => $product) {
            $expectedProduct = $expectedNumbers[$index];

            static::assertEquals(
                $expectedProduct,
                $product->getNumber(),
                sprintf(
                    'Expected %s at search result position %s, but got product %s',
                    $expectedProduct,
                    $index,
                    $product->getNumber()
                )
            );
        }
    }

    /**
     * @param int $shopId
     *
     * @return TestContext
     */
    protected function getContext($shopId = 1)
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();
        $shop = $this->helper->getShop($shopId);

        $context = $this->helper->createContext(
            $customerGroup,
            $shop,
            [$tax]
        );

        if (!$context->getShop()->getCurrency()) {
            $context->getShop()->setCurrency($context->getCurrency());
        }

        return $context;
    }

    /**
     * @param string   $number
     * @param Category $category
     * @param array    $additionally
     *
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = []
    ) {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );
        $product['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];

        if ($category) {
            $product['categories'] = [
                ['id' => $category->getId()],
            ];
        }

        if (!is_array($additionally)) {
            $additionally = [];
        }

        $product = array_merge($product, $additionally);

        return $product;
    }

    /**
     * Allows to set a Shopware config
     *
     * @param string $name
     */
    protected function setConfig($name, $value)
    {
        Shopware()->Container()->get(\Shopware\Components\ConfigWriter::class)->save($name, $value);
        Shopware()->Container()->get(\Zend_Cache_Core::class)->clean();
        Shopware()->Container()->get(\Shopware_Components_Config::class)->setShop(Shopware()->Shop());
    }
}
