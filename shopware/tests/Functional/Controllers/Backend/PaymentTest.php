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

class PaymentTest extends \Enlight_Components_Test_Controller_TestCase
{
    private $testDataCreate = [
        'name' => 'New payment',
        'description' => 'New payment',
        'source' => 1,
        'template' => '',
        'class' => '',
        'table' => '',
        'hide' => 0,
        'additionaldescription' => '',
        'debitPercent' => 0,
        'surcharge' => 0,
        'surchargeString' => '',
        'position' => 0,
        'active' => 0,
        'esdActive' => 0,
        'embedIFrame' => '',
        'hideProspect' => '',
    ];

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
     * Tests the getPaymentsAction()
     * to test if reading the payments is working
     */
    public function testGetPayments()
    {
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/getPayments');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Tests the getCountriesAction()
     * to test if reading the countries is working
     */
    public function testGetCountries()
    {
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/getCountries');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Function to test creating a new payment
     */
    public function testCreatePayments()
    {
        Shopware()->Db()->exec('DELETE FROM s_core_paymentmeans WHERE name = "New payment"');

        $this->Request()->setMethod('POST')->setPost($this->testDataCreate);
        $this->dispatch('backend/payment/createPayments');

        static::assertTrue($this->View()->success);
        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);

        return $jsonBody['data'];
    }

    /**
     * Function to test updating a payment
     *
     * @param array $data Contains the data of the created payment
     * @depends testCreatePayments
     */
    public function testUpdatePayments($data)
    {
        $this->Request()->setMethod('POST')->setPost(['id' => $data['id'], 'name' => 'Neue Zahlungsart']);

        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/updatePayments');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * Function to test deleting a payment
     *
     * @param array $data Contains the data of the created payment
     * @depends testCreatePayments
     */
    public function testDeletePayment($data)
    {
        $this->Request()->setMethod('POST')->setPost(['id' => $data['id']]);

        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/payment/deletePayment');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('success', $jsonBody);
    }
}
