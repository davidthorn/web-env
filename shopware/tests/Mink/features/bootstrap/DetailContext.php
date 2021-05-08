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

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Element\ArticleEvaluation;
use Shopware\Tests\Mink\Page\Detail;

class DetailContext extends SubContext
{
    /**
     * @Given /^I am on the detail page for article (?P<articleId>\d+)$/
     * @When /^I go to the detail page for article (?P<articleId>\d+)$/
     */
    public function iAmOnTheDetailPageForArticle($articleId)
    {
        $this->getPage('Detail')->open(['articleId' => $articleId, 'number' => null]);
    }

    /**
     * @Given /^I am on the detail page for variant "(?P<number>[^"]*)" of article (?P<articleId>\d+)$/
     * @When /^I go to the detail page for variant "(?P<number>[^"]*)" of article (?P<articleId>\d+)$/
     */
    public function iAmOnTheDetailPageForVariantOfArticle($number, $articleId)
    {
        $this->getPage('Detail')->open(['articleId' => $articleId, 'number' => $number]);
    }

    /**
     * @When /^I put the article into the basket$/
     * @When /^I put the article "(?P<quantity>[^"]*)" times into the basket$/
     */
    public function iPutTheArticleTimesIntoTheBasket($quantity = 1)
    {
        /** @var Detail $page */
        $page = $this->getPage('Detail');
        $page->addToBasket($quantity);
    }

    /**
     * @Given /^I should see an average customer evaluation of (?P<average>\d+) from following evaluations:$/
     */
    public function iShouldSeeAnAverageCustomerEvaluationOfFromFollowingEvaluations($average, TableNode $evaluations)
    {
        /** @var \Shopware\Tests\Mink\Page\Detail $page */
        $page = $this->getPage('Detail');

        /** @var ArticleEvaluation $articleEvaluations */
        $articleEvaluations = $this->getMultipleElement($page, 'ArticleEvaluation');
        $evaluations = $evaluations->getHash();

        $page->checkEvaluations($articleEvaluations, $average, $evaluations);
    }

    /**
     * @When /^I choose the following article configuration:$/
     */
    public function iChooseTheFollowingArticleConfiguration(TableNode $configuration)
    {
        $configuration = $configuration->getHash();

        $this->getPage('Detail')->configure($configuration);
    }

    /**
     * @Then /^I can not select "([^"]*)" from "([^"]*)"$/
     */
    public function iCanNotSelectFrom($configuratorOption, $configuratorGroup)
    {
        $this->getPage('Detail')->canNotSelectConfiguratorOption($configuratorOption, $configuratorGroup);
    }

    /**
     * @When /^I write an evaluation:$/
     */
    public function iWriteAnEvaluation(TableNode $data)
    {
        $this->getPage('Detail')->writeEvaluation($data->getHash());
    }

    /**
     * @When /^the shop owner activates my latest evaluation$/
     * @When /^the shop owner activates my latest (\d+) evaluations$/
     */
    public function theShopOwnerActivateMyLatestEvaluation($limit = 1)
    {
        $sql = 'UPDATE `s_articles_vote` SET `active`= 1 ORDER BY id DESC LIMIT ' . $limit;
        $this->getService('db')->exec($sql);
    }

    /**
     * @Given /^I can select every (\d+)\. option of "([^"]*)" from "([^"]*)" to "([^"]*)"$/
     */
    public function iCanSelectEveryOptionOfFromTo($graduation, $select, $min, $max)
    {
        $this->getPage('Detail')->checkSelect($select, $min, $max, $graduation);
    }

    /**
     * @When /^I submit the notification form with "([^"]*)"$/
     */
    public function iSubmitTheNotificationFormWith($email)
    {
        $this->getPage('Detail')->submitNotification($email);
    }

    /**
     * @When /^I open the evaluation form$/
     */
    public function iOpenTheEvaluationForm()
    {
        /** @var Detail $page */
        $page = $this->getPage('Detail');
        $page->openEvaluationSection();
    }

    /**
     * @Given /^The notification plugin is activated$/
     */
    public function theNotificationPluginIsActivated()
    {
        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = $this->getService(\Shopware\Bundle\PluginInstallerBundle\Service\InstallerService::class);
        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->activatePlugin($plugin);
    }

    /**
     * @Given /^The notification plugin is deactivated$/
     */
    public function theNotificationPluginIsDeactivated()
    {
        /** @var \Shopware\Bundle\PluginInstallerBundle\Service\InstallerService $pluginManager */
        $pluginManager = $this->getService(\Shopware\Bundle\PluginInstallerBundle\Service\InstallerService::class);
        $plugin = $pluginManager->getPluginByName('Notification');
        $pluginManager->deactivatePlugin($plugin);
    }
}
