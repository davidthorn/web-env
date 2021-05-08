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

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Element\CompareColumn;
use Shopware\Tests\Mink\Page\Homepage;

class ShopwareContext extends SubContext
{
    /** @var FeatureContext */
    protected $featureContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->featureContext = $environment->getContext('Shopware\Tests\Mink\FeatureContext');
    }

    /**
     * @When /^I search for "(?P<searchTerm>[^"]*)"$/
     */
    public function iSearchFor($searchTerm)
    {
        $this->getPage('Homepage')->searchFor($searchTerm);
    }

    /**
     * @When /^I received the search-results for "(?P<searchTerm>[^"]*)"$/
     */
    public function iReceivedTheSearchResultsFor($searchTerm)
    {
        $this->getPage('Homepage')->receiveSearchResultsFor($searchTerm);
    }

    /**
     * @Then /^I should see the no results message for keyword "([^"]*)"$/
     */
    public function iShouldSeeTheNoResultsMessageForKeyword($keyword)
    {
        $this->getPage('Homepage')->receiveNoResultsMessageForKeyword($keyword);
    }

    /**
     * @When /^I change the currency to "(?P<currency>[^"]*)"$/
     */
    public function iChangeTheCurrencyTo($currency)
    {
        $this->getPage('Homepage')->changeCurrency($currency);
    }

    /**
     * @Then /^the comparison should contain the following products:$/
     */
    public function theComparisonShouldContainTheFollowingProducts(TableNode $items)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var CompareColumn $compareColumns */
        $compareColumns = $this->getMultipleElement($page, 'CompareColumn');

        $page->checkComparisonProducts($compareColumns, $items->getHash());
    }

    /**
     * @Then /^the cart should contain (?P<quantity>\d+) articles with a value of "(?P<amount>[^"]*)"$/
     */
    public function theCartShouldContainArticlesWithAValueOf($quantity, $amount)
    {
        $this->getElement('HeaderCart')->checkCart($quantity, $amount);
    }

    /**
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)"$/
     * @When /^I subscribe to the newsletter with "(?P<email>[^"]*)" :$/
     */
    public function iSubscribeToTheNewsletterWith($email, TableNode $additionalData = null)
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        $pageName = ucfirst($pageInfo['controller']);
        $data = [
            [
                'field' => 'newsletter',
                'value' => $email,
            ],
        ];

        if ($pageName === 'Index') {
            $pageName = 'Homepage';
        } elseif (($pageName === 'Newsletter') && ($additionalData)) {
            $data = array_merge($data, $additionalData->getHash());
        }

        /** @var Homepage|\Shopware\Tests\Mink\Page\Newsletter $page */
        $page = $this->getPage($pageName);
        $page->subscribeNewsletter($data);
    }

    /**
     * @When /^I unsubscribe the newsletter$/
     * @When /^I unsubscribe the newsletter with "(?P<email>[^"]*)"$/
     */
    public function iUnsubscribeTheNewsletter($email = null)
    {
        $data = [];

        if ($email) {
            $data = [
                [
                    'field' => 'newsletter',
                    'value' => $email,
                ],
            ];
        }

        $this->getPage('Newsletter')->unsubscribeNewsletter($data);
    }

    /**
     * @When /^I click the link in my latest email$/
     * @When /^I click the links in my latest (\d+) emails$/
     */
    public function iConfirmTheLinkInTheEmail($limit = 1)
    {
        $sql = 'SELECT `type`, `hash` FROM `s_core_optin` ORDER BY `id` DESC LIMIT ' . $limit;
        $hashes = $this->getService('db')->fetchAll($sql);

        $session = $this->getSession();
        $link = $session->getCurrentUrl();

        foreach ($hashes as $optin) {
            if ($optin['type'] === 'swPassword') {
                $mask = '%saccount/resetPassword/hash/%s';
                $link = $this->getPage('Homepage')->getShopUrl();

                $confirmationLink = sprintf($mask, $link, $optin['hash']);
                $session->visit($confirmationLink);

                return;
            }
        }

        $query = parse_url($link, PHP_URL_QUERY);
        $anchor = strpos($link, '#');

        if ($anchor) {
            $link = substr($link, 0, $anchor);
        }

        //Blogartikel-Bewertung
        if (empty($query)) {
            $mask = '%s/sConfirmation/%s';
        } else {
            parse_str($query, $args);

            switch ($args['action']) {
                //Artikel-Benachrichtigungen
                case 'notify':
                    $mask = '%s&sNotificationConfirmation=%s&sNotify=1&action=notifyConfirm';
                    break;

                //Artikel-Bewertungen
                default:
                    $mask = '%s&sConfirmation=%s';
                    break;
            }
        }

        foreach ($hashes as $optin) {
            $confirmationLink = sprintf($mask, $link, $optin['hash']);
            $session->visit($confirmationLink);
        }
    }

    /**
     * @When /^I enable the config "([^"]*)"$/
     */
    public function iEnableTheConfig($configName)
    {
        $this->theConfigValueOfIs($configName, true);
    }

    /**
     * @When /^I disable the config "([^"]*)"$/
     */
    public function iDisableTheConfig($configName)
    {
        $this->theConfigValueOfIs($configName, false);
    }

    /**
     * @When /^the config value of "([^"]*)" is (\d+)$/
     * @When /^the config value of "([^"]*)" is "([^"]*)"$/
     */
    public function theConfigValueOfIs($configName, $value)
    {
        $this->featureContext->changeConfigValue($configName, $value);
    }

    /**
     * @When the emotion world has loaded
     */
    public function theEmotionWorldHasLoaded()
    {
        $this->getSession()->wait(15000, "$($(':plugin-swEmotionLoader').get(0)).data('plugin_swEmotionLoader').isLoading == false");
    }

    /**
     * @Given /^I should see a banner with image "(?P<image>[^"]*)"$/
     * @Given /^I should see a banner with image "(?P<image>[^"]*)" to "(?P<link>[^"]*)"$/
     */
    public function iShouldSeeABanner($image, $link = null)
    {
        $this->iShouldSeeABannerOnPositionWithImage(1, $image, $link);
    }

    /**
     * @Given /^I should see a banner on position (\d+) with image "([^"]*)"$/
     * @Given /^I should see a banner on position (\d+) with image "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeABannerOnPositionWithImage($position, $image, $link = null)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\Banner $banner */
        $banner = $this->getMultipleElement($page, 'Banner', $position);
        $page->checkLinkedBanner($banner, $image, $link);
    }

    /**
     * @Given /^I should see a banner with image "(?P<image>[^"]*)" and mapping:$/
     */
    public function iShouldSeeABannerWithMapping($image, TableNode $mapping)
    {
        $this->iShouldSeeABannerOnPositionWithImageAndMapping(1, $image, $mapping);
    }

    /**
     * @Given /^I should see a banner on position (\d+) with image "([^"]*)" and mapping:$/
     */
    public function iShouldSeeABannerOnPositionWithImageAndMapping($position, $image, TableNode $mapping)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\Banner $banner */
        $banner = $this->getMultipleElement($page, 'Banner', $position);
        $mapping = $mapping->getHash();

        $page->checkMappedBanner($banner, $image, $mapping);
    }

    /**
     * @Given /^the product box on position (\d+) should have the following properties:$/
     */
    public function iShouldSeeAnArticle($position, TableNode $data)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\Article $article */
        $article = $this->getMultipleElement($page, 'Article', $position);

        $page->checkArticle($article, $data->getHash());
    }

    /**
     * @Given /^the category teaser on position (\d+) for "(?P<name>[^"]*)" should have the image "(?P<image>[^"]*)" and link to "(?P<link>[^"]*)"$/
     */
    public function iShouldSeeACategoryTeaserWithImageTo($position, $name, $image, $link)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\CategoryTeaser $teaser */
        $teaser = $this->getMultipleElement($page, 'CategoryTeaser', $position);

        $page->checkCategoryTeaser($teaser, $name, $image, $link);
    }

    /**
     * @Given /^I should see some blog articles:$/
     */
    public function iShouldSeeSomeBlogArticles(TableNode $articles)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\BannerSlider $slider */
        $blogArticle = $this->getMultipleElement($page, 'BlogArticle', 1);

        $articles = $articles->getHash();

        $page->checkBlogArticles($blogArticle, $articles);
    }

    /**
     * @Then /^I should see a banner slider:$/
     */
    public function iShouldSeeABannerSlider(TableNode $slides)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\BannerSlider $slider */
        $slider = $this->getMultipleElement($page, 'BannerSlider', 1);

        $page->checkSlider($slider, $slides->getHash());
    }

    /**
     * @Given /^I should see a YouTube-Video "(?P<code>[^"]*)"$/
     */
    public function iShouldSeeAYoutubeVideo($code)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\Youtube $slider */
        $youtube = $this->getMultipleElement($page, 'YouTube', 1);

        $page->checkYoutubeVideo($youtube, $code);
    }

    /**
     * @Then /^I should see a manufacturer slider:$/
     */
    public function iShouldSeeAManufacturerSlider(TableNode $manufacturers)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\ManufacturerSlider $slider */
        $slider = $this->getMultipleElement($page, 'ManufacturerSlider', 1);

        $page->checkManufacturerSlider($slider, $manufacturers->getHash());
    }

    /**
     * @Then /^I should see an article slider:$/
     */
    public function iShouldSeeAnArticleSlider(TableNode $articles)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var \Shopware\Tests\Mink\Element\ManufacturerSlider $slider */
        $slider = $this->getMultipleElement($page, 'ArticleSlider', 1);

        $products = Helper::floatArray($articles->getHash(), ['price']);

        $page->checkSlider($slider, $products);
    }

    /**
     * @Then /^I the language should be "([^"]*)"$/
     */
    public function iTheLanguageShouldBe($language)
    {
        Helper::setCurrentLanguage($language);
    }

    /**
     * @When I scroll to the bottom of the page
     */
    public function iScrollToTheBottomOfThePage()
    {
        $this->getDriver()->executeScript('window.scrollTo(0, document.body.scrollHeight);');
    }

    /**
     * @When /^I click "([^"]*)"$/
     */
    public function iClick($selector)
    {
        $element = $this->getSession()->getPage()->findField($selector);
        $element->click();
    }

    /**
     * @When /^I follow "(?P<link>(?:[^"]|\\")*)" on Account menu$/
     */
    public function clickLinkInAccount($link)
    {
        $element = $this->getSession()->getPage()->findAll('xpath', sprintf('//*[contains(concat(" ",normalize-space(@class)," ")," account--menu ")]//li//a[contains(text(),\'%s\')]', $link));

        if (!isset($element[1])) {
            throw new \RuntimeException(sprintf('Cannot find element with name "%s"', $link));
        }

        $element[1]->click();
    }

    /**
     * @When /^Wait until ajax requests are done$/
     */
    public function waitForAjaxRequestsDone()
    {
        $session = $this->getSession();
        $this->getSession()->getPage()->waitFor(2000, static function () use ($session) {
            return $session->evaluateScript('return jQuery.active == 0') === true;
        });
    }
}
