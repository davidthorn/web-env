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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Controller;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Symfony\Component\DomCrawler\Crawler;

class AddressTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var ModelManager
     */
    private static $modelManager;

    /**
     * @var array
     */
    private static $_cleanup = [];

    /**
     * @var string
     */
    private static $loginEmail;

    /**
     * @var string
     */
    private static $loginPassword;

    /**
     * @var Customer
     */
    private static $customer;

    /**
     * Create one customer to be used for these tests
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$modelManager = Shopware()->Container()->get(\Shopware\Components\Model\ModelManager::class);
        self::$modelManager->clear();

        // Register customer
        $demoData = self::getCustomerDemoData(true);
        $billingDemoData = self::getBillingDemoData();
        $shippingDemoData = self::getShippingDemoData();

        $shop = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::class)->createShopContext(1)->getShop();

        $customer = new Customer();
        $customer->fromArray($demoData);

        $billing = new Address();
        $billing->fromArray($billingDemoData);

        $shipping = new Address();
        $shipping->fromArray($shippingDemoData);

        $registerService = Shopware()->Container()->get(\Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface::class);
        $registerService->register($shop, $customer, $billing, $shipping);

        self::$loginEmail = $demoData['email'];
        self::$loginPassword = $demoData['password'];
        self::$customer = $customer;

        self::$_cleanup[Customer::class][] = $customer->getId();
    }

    /**
     * Clean up created entities and database entries
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        foreach (self::$_cleanup as $entityName => $ids) {
            foreach ($ids as $id) {
                self::$modelManager->remove(self::$modelManager->find($entityName, $id));
            }
        }

        self::$modelManager->flush();
        self::$modelManager->clear();

        Shopware()->Container()->reset('router');
    }

    public function testList()
    {
        $this->ensureLogin();
        $crawler = $this->doRequest('GET', '/address/');

        static::assertEquals(3, $crawler->filter('.address--item-content')->count());
        static::assertGreaterThan(0, $crawler->filter('html:contains("Standard-Rechnungsadresse")')->count());
        static::assertGreaterThan(0, $crawler->filter('html:contains("Standard-Lieferadresse")')->count());
    }

    /**
     * @return int
     */
    public function testCreation()
    {
        $this->ensureLogin();
        $crawler = $this->doRequest(
            'POST',
            '/address/create/',
            [
                'address' => [
                    'salutation' => 'mr',
                    'firstname' => 'Luis',
                    'lastname' => 'King',
                    'street' => 'Fasanenstrasse 99',
                    'zipcode' => '79268',
                    'city' => 'Bötzingen',
                    'country' => 2,
                ],
            ]
        );

        static::assertEquals(1, $crawler->filter('html:contains("Die Adresse wurde erfolgreich erstellt")')->count());
        static::assertGreaterThan(0, $crawler->filter('.address--item-content:contains("Fasanenstrasse 99")')->count());
        static::assertEquals(4, $crawler->filter('.address--item-content')->count());

        return (int) $crawler->filter('.address--item-content:contains("Fasanenstrasse 99")')->filter('input[name=addressId]')->attr('value');
    }

    /**
     * @param int $addressId
     * @depends testCreation
     */
    public function testEditPage($addressId)
    {
        $this->ensureLogin();

        // Edit page
        $crawler = $this->doRequest('GET', '/address/edit/id/' . $addressId);
        static::assertEquals('Fasanenstrasse 99', $crawler->filter('input[name="address[street]"]')->attr('value'));
    }

    /**
     * @param int $addressId
     * @depends testCreation
     */
    public function testEdit($addressId)
    {
        $this->ensureLogin();

        // Edit operation
        $crawler = $this->doRequest(
            'POST',
            '/address/edit/id/' . $addressId,
            [
                'address' => [
                    'salutation' => 'mr',
                    'firstname' => 'Joe',
                    'lastname' => 'Doe',
                    'street' => 'Fasanenstrasse 99',
                    'zipcode' => '79268',
                    'city' => 'Bötzingen',
                    'country' => 2,
                ],
            ]
        );

        static::assertEquals(1, $crawler->filter('html:contains("Die Adresse wurde erfolgreich gespeichert")')->count());
        static::assertGreaterThan(0, $crawler->filter('.address--item-content:contains("Joe Doe")')->count());
        static::assertGreaterThan(0, $crawler->filter('.address--item-content:contains("Fasanenstrasse 99")')->count());
        static::assertEquals(4, $crawler->filter('.address--item-content')->count());
    }

    /**
     * @depends testCreation
     *
     * @param int $addressId
     */
    public function testDeletion($addressId)
    {
        $this->ensureLogin();

        // Delete confirm page
        $crawler = $this->doRequest('GET', '/address/delete/id/' . $addressId);
        static::assertEquals(1, $crawler->filter('html:contains("Fasanenstrasse 99")')->count());

        // Delete operation
        $crawler = $this->doRequest('POST', '/address/delete/id/' . $addressId, ['id' => $addressId]);
        static::assertEquals(1, $crawler->filter('html:contains("Die Adresse wurde erfolgreich gelöscht")')->count());
        static::assertEquals(3, $crawler->filter('.address--item-content')->count());
    }

    /**
     * @depends testCreation
     */
    public function testDeletionOfDefaultAddressesShouldFail()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The address is defined as default billing or shipping address and cannot be removed.');
        $this->ensureLogin();
        $addressId = self::$customer->getDefaultBillingAddress()->getId();

        $this->doRequest('POST', '/address/delete/id/' . $addressId . '/', ['id' => $addressId]);
    }

    /**
     * @depends testDeletionOfDefaultAddressesShouldFail
     */
    public function testVerifyAddressDeletionOfDefaultAddressesShouldFail()
    {
        $this->ensureLogin();

        $crawler = $this->doRequest('GET', '/address/');

        static::assertEquals(3, $crawler->filter('.address--item-content')->count());
    }

    /**
     * @depends testCreation
     */
    public function testChangeOfBillingAddressReflectsInAccount()
    {
        $this->ensureLogin();

        // Crawl original data
        $crawler = $this->doRequest('GET', '/account');
        $addressId = (int) $crawler->filter('.account--billing .panel--actions a:contains("oder andere Adresse wählen")')->attr('data-id');

        static::assertGreaterThan(0, $addressId);

        $this->doRequest(
            'POST',
            '/address/edit/id/' . $addressId,
            [
                'address' => [
                    'salutation' => 'mr',
                    'company' => 'Muster GmbH',
                    'firstname' => 'Shop',
                    'lastname' => 'Man',
                    'street' => 'Musterstr. 55',
                    'zipcode' => '55555',
                    'city' => 'Musterhausen',
                    'country' => 2,
                    'state' => 3,
                ],
            ]
        );

        // verify the changes
        $crawler = $this->doRequest('GET', '/account');
        $panelBody = $crawler->filter('.account--billing .panel--body');

        static::assertEquals('Muster GmbH', trim($panelBody->filter('.address--company')->text()));
        static::assertEquals('Herr', trim($panelBody->filter('.address--salutation')->text()));
        static::assertEquals('Shop', trim($panelBody->filter('.address--firstname')->text()));
        static::assertEquals('Man', trim($panelBody->filter('.address--lastname')->text()));
        static::assertEquals('Musterstr. 55', trim($panelBody->filter('.address--street')->text()));
        static::assertEquals('Nordrhein-Westfalen', trim($panelBody->filter('.address--statename')->text()));
        static::assertEquals('Deutschland', trim($panelBody->filter('.address--countryname')->text()));
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return Crawler
     */
    private function doRequest($method, $url, array $data = [])
    {
        $this->reset();

        $this->Request()->setMethod($method);

        if ($method === 'POST') {
            $this->Request()->setPost($data);
        }

        $this->dispatch($url);

        if ($this->Response()->isRedirect()) {
            $location = null;

            foreach ($this->Response()->getHeaders() as $header) {
                if ($header['name'] === 'location') {
                    $location = $header['value'];
                }
            }

            $parts = parse_url($location);
            $followUrl = $parts['path'];

            if (isset($parts['query'])) {
                $followUrl .= '?' . $parts['query'];
            }

            return $this->doRequest('GET', $followUrl);
        }

        return new Crawler($this->Response()->getBody());
    }

    /**
     * Log-in into account, needed for every test
     */
    private function ensureLogin()
    {
        $this->doRequest('POST', '/account/login', ['email' => self::$loginEmail, 'password' => self::$loginPassword]);
    }

    /**
     * Helper method for creating a valid customer
     *
     * @param bool $randomEmail
     *
     * @return array
     */
    private static function getCustomerDemoData($randomEmail = false)
    {
        $emailPrefix = $randomEmail ? uniqid(rand()) : '';

        $data = [
            'salutation' => 'mr',
            'firstname' => 'Albert',
            'lastname' => 'McTaggart',
            'email' => $emailPrefix . 'albert.mctaggart@shopware.test',
            'password' => uniqid(rand()),
        ];

        return $data;
    }

    private static function getBillingDemoData()
    {
        $country = self::createCountry();

        $data = [
            'salutation' => 'mr',
            'firstname' => 'Sherman',
            'lastname' => 'Horton',
            'street' => '1117 Washington Street',
            'zipcode' => '78372',
            'city' => 'Orange Grove',
            'country' => $country,
            'state' => self::createState($country),
        ];

        return $data;
    }

    private static function getShippingDemoData()
    {
        $data = [
            'salutation' => 'mr',
            'firstname' => 'Nathaniel',
            'lastname' => 'Fajardo',
            'street' => '3844 Euclid Avenue',
            'zipcode' => '93101',
            'city' => 'Santa Barbara',
            'country' => self::createCountry(),
        ];

        return $data;
    }

    /**
     * @return Country
     */
    private static function createCountry()
    {
        $country = new Country();

        $country->setName('ShopwareLand ' . uniqid(rand()));
        $country->setActive(true);
        $country->setDisplayStateInRegistration(1);
        $country->setForceStateInRegistration(0);

        self::$modelManager->persist($country);
        self::$modelManager->flush($country);

        self::$_cleanup[Country::class][] = $country->getId();

        return self::$modelManager->merge($country);
    }

    /**
     * @return State
     */
    private static function createState(Country $country)
    {
        $state = new State();

        $state->setName('Shopware State ' . uniqid(rand()));
        $state->setActive(1);
        $state->setCountry($country);
        $state->setShortCode(uniqid(rand()));

        self::$modelManager->persist($state);
        self::$modelManager->flush($state);

        self::$_cleanup[State::class][] = $state->getId();

        return self::$modelManager->merge($state);
    }
}
