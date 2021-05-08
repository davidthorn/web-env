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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AccountBundle\Service\CustomerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;

class CustomerServiceTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var CustomerServiceInterface
     */
    protected static $customerService;

    /**
     * @var ModelManager
     */
    protected static $modelManager;

    /**
     * @var Connection
     */
    protected static $connection;

    /**
     * @var ContextServiceInterface
     */
    protected static $contextService;

    /**
     * @var array
     */
    protected static $_cleanup = [];

    /**
     * Set up fixtures
     */
    public static function setUpBeforeClass(): void
    {
        self::$customerService = Shopware()->Container()->get('shopware_account.customer_service');
        self::$modelManager = Shopware()->Container()->get(\Shopware\Components\Model\ModelManager::class);
        self::$connection = Shopware()->Container()->get(\Doctrine\DBAL\Connection::class);
        self::$contextService = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::class);

        self::$modelManager->clear();
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

    public function testUpdateEmail()
    {
        $newMail = 'bryan.khan@shopware.test';

        $customer = self::$modelManager->find(Customer::class, 2);
        $customer->setEmail($newMail);

        self::$customerService->update($customer);

        static::assertEquals($newMail, $customer->getEmail());

        // Reset back to default demo mail
        $customer->setEmail('mustermann@b2b.de');
        self::$customerService->update($customer);
    }

    public function testUpdateExistingEmail()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $newMail = 'test@example.com';

        $customer = self::$modelManager->find(Customer::class, 2);
        $customer->setEmail($newMail);

        self::$customerService->update($customer);
    }

    public function testUpdateProfileWithEmptyData()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $updateData = [
            'firstname' => '',
            'lastname' => '',
            'salutation' => '',
        ];

        $customer = self::$modelManager->find(Customer::class, 2);
        $customer->fromArray($updateData);

        self::$customerService->update($customer);
    }

    public function testUpdateProfile()
    {
        $updateData = [
            'firstname' => 'Victoria',
            'lastname' => 'Palmer',
            'salutation' => 'ms',
            'birthday' => '1957-02-03',
        ];

        $customer = self::$modelManager->find(Customer::class, 2);
        $customer->fromArray($updateData);

        self::$customerService->update($customer);

        static::assertEquals($updateData['salutation'], $customer->getSalutation());
        static::assertEquals($updateData['firstname'], $customer->getFirstname());
        static::assertEquals($updateData['lastname'], $customer->getLastname());
        static::assertEquals($updateData['birthday'], $customer->getBirthday()->format('Y-m-d'));
    }
}
