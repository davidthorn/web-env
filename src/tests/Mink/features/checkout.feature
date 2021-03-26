@checkout @checkout1
Feature: Checkout articles

    Background:
        Given I am on the page "Account"
        And   I log in successful as "Max Mustermann" with email "test@example.com" and password "shopware"
        And   the cart contains the following products:
            | articleId   | number  | name                    | quantity |
            | 167         | SW10167 | Sonnenbrille Speed Eyes | 3        |

    @onlypostallowed
    Scenario: I can't checkout using the HTTP GET method
        Given I proceed to order confirmation
        And   I checkout using GET
        Then  I should be on "/checkout/finish"
        And   I should see "AGB und Widerrufsbelehrung"
        And   I should not see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    Scenario: I can put articles to the basket, check all prices and pay via C.O.D. service
        Given the cart should contain 1 articles with a value of "38,47 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 38,47 € |
            | shipping      | 3,90 €  |
            | total         | 42,37 € |
            | sumWithoutVat | 35,61 € |
        When  I add the article "SW10170" to my basket
        Then  the cart should contain 2 articles with a value of "78,42 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 78,42 € |
            | shipping      | 3,90 €  |
            | total         | 82,32 € |
            | sumWithoutVat | 69,18 € |

        When  I remove the article on position 1
        Then  the cart should contain 1 articles with a value of "37,95 €"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 37,95 € |
            | shipping      | 3,90 €  |
            | total         | 41,85 € |
            | sumWithoutVat | 35,17 € |

        When  I proceed to order confirmation
        And   I change the payment method to 3
        And   Wait until ajax requests are done
        Then  the current payment method should be "Nachnahme"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 37,95 € |
            | shipping      | 3,90 €  |
            | total         | 41,85 € |
            | sumWithoutVat | 35,17 € |

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @paymentsurcharge
    Scenario:   I can switch to payment method with percentual surcharge and everything is calculated correctly
        Given   I proceed to order confirmation
        When    I change the payment method to 5
        And     Wait until ajax requests are done
        Then    the current payment method should be "Vorkasse"
        And     I should see "Zuschlag für Zahlungsart"
        And     the aggregations should look like this:
            | label         | value   |
            | sum           | 42,32 € |
            | shipping      | 3,90 €  |
            | total         | 46,22 € |
            | sumWithoutVat | 38,84 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"

    @shipping @payment
    Scenario: I can change the shipping-country to a non-EU-country and back and pay via bill
        Given I proceed to order confirmation
        And   I change my shipping address:
            | field   | address |
            | country | Schweiz            |
        Then  the aggregations should look like this:
            | label    | value   |
            | sum      | 32,02 € |
            | shipping | 21,00 € |
            | total    | 53,02 € |
        And   I should not see "MwSt."

        When  I change my shipping address:
            | field   | address |
            | country | Deutschland        |
        Then  the aggregations should look like this:
            | label    | value   |
            | shipping | 3,90 €  |
            | total    | 42,37 € |

        When  I change the payment method to 4
        And   Wait until ajax requests are done
        Then  the current payment method should be "Rechnung"
        And   I should see "Zuschlag für Zahlungsart"
        And   the aggregations should look like this:
            | label         | value   |
            | sum           | 43,47 € |
            | shipping      | 3,90 €  |
            | total         | 47,37 € |
            | sumWithoutVat | 39,81 € |
        And   I should see "AGB und Widerrufsbelehrung"

        When  I proceed to checkout
        Then  I should see "Vielen Dank für Ihre Bestellung bei Shopware Demo!"
