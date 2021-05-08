@detail
Feature: Detail page

    @captchaInactive @evaluations
    Scenario: I can see evaluations
        Given I am on the detail page for article 198

        Then  I follow "Bewertungen"
        Then  I should see "Kundenbewertungen für \"Artikel mit Bewertung\""
        And   I should see an average customer evaluation of 10 from following evaluations:
            | author        | stars | headline      | comment                                                                                                                                     | answer                                                                                                                                                      |
            | Bert Bewerter | 10    | Super Artikel | Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut. | Vielen Dank für die positive Bewertung. Wir legen bei der Auswahl unserer Artikel besonders Wert auf die Qualität, sowie das Preis - / Leistungsverhältnis. |
            | Pep Eroni     | 10    | Hervorragend  | bin sehr zufrieden...                                                                                                                       | Danke                                                                                                                                                       |

        When  I write an evaluation:
            | field        | value           |
            | sVoteName    | Max Mustermann  |
            | sVoteStars   | 1 sehr schlecht |
            | sVoteSummary | Neue Bewertung  |
            | sVoteComment | Hallo Welt      |
        And  I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet."
        And  the shop owner activates my latest evaluation
        And  I am on the detail page for article 198

        Then  I should see an average customer evaluation of 7 from following evaluations:
            | stars |
            | 1     |
            | 10    |
            | 10    |


    @plugin @notification
    Scenario: I can let me notify, when an article is not available
        Given The notification plugin is activated
        And   I am on the detail page for article 243
        Then  I should see "Benachrichtigen Sie mich, sobald der Artikel lieferbar ist"

        When  I submit the notification form with "test-notification@example.de"
        Then  I should see "Bestätigen Sie den Link der E-Mail, die wir Ihnen soeben geschickt haben. Wir benachrichtigen Sie dann, sobald der Artikel wieder verfügbar ist."

        When  I click the link in my latest email
        Then  I should see "Vielen Dank! Wir haben Ihre Anfrage gespeichert. Sie werden benachrichtigt sobald der Artikel wieder verfügbar ist."

    @plugin @notification
    Scenario: I should not see the notification box, when the notification plugin is disabled
        Given The notification plugin is deactivated
        And   I am on the detail page for article 243
        Then  I should not see "Bestätigen Sie den Link der E-Mail, die wir Ihnen soeben geschickt haben. Wir benachrichtigen Sie dann, sobald der Artikel wieder verfügbar ist."

    @language @javascript
    Scenario: I can change the language
        Given I am on the detail page for article 229
        Then  I should see "Magnete London"

        When  I select "English" from "__shop"
        Then  I should see "Magnets London"

        When  I select "Deutsch" from "__shop"
        Then  I should see "Magnete London"

    @captchaInactive @evaluations
    Scenario: I can write an evaluation
        Given I am on the detail page for article 100
        Then  I follow "Bewertungen"
        And   I should see "Bewertung schreiben"
        When  I write an evaluation:
            | field        | value           |
            | sVoteName    | Max Mustermann  |
            | sVoteStars   | 3               |
            | sVoteSummary | Neue Bewertung  |
            | sVoteComment | Hallo Welt      |
        Then  I should not see "Bitte füllen Sie alle rot markierten Felder aus"
        But   I should see "Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet."
        But   I should not see "Hallo Welt"

        When  the shop owner activates my latest evaluation
        And   I am on the detail page for article 100
        Then  I follow "Bewertungen"
        Then  I should see an average customer evaluation of 3 from following evaluations:
            | author         | stars | headline       | comment    |
            | Max Mustermann | 3     | Neue Bewertung | Hallo Welt |

    @graduatedPrices
    Scenario Outline: An article can have graduated prices
        Given I am on the detail page for article 209
        Then  I should see "<grade> <itemPrice>"

        When  I put the article "<quantity>" times into the basket
        Then  the cart should contain the following products:
            | number  | name          | quantity   | itemPrice   | sum   |
            | SW10208 | Staffelpreise | <quantity> | <itemPrice> | <sum> |

        Examples:
            | grade  | itemPrice | quantity | sum   |
            | bis 10 | 1,00      | 10       | 10,00 |
            | ab 11  | 0,90      | 20       | 18,00 |
            | ab 21  | 0,80      | 30       | 24,00 |
            | ab 31  | 0,75      | 40       | 30,00 |
            | ab 41  | 0,70      | 50       | 35,00 |

    @minimumQuantity @maximumQuantity @graduation
    Scenario: An article can have a minimum/maximum quantity with graduation
        Given I am on the detail page for article 207
        Then  I can select every 3. option of "sQuantity" from "3 Stück" to "30 Stück"

        When  I put the article "3" times into the basket
        Then  I can select every 3. option of "sQuantity" from "3" to "30"

    @variants
    Scenario: I can toggle between product variants
        Given I am on the detail page for article 2
        Then  I should see "19,99"

        When  I select "33" from "group[5]"
        And   Wait until ajax requests are done
        Then  I should not see "19,99"
        But   I should see "59,99"

        When  I select "34" from "group[5]"
        And   Wait until ajax requests are done
        Then  I should not see "59,99"
        But   I should see "199,00"

    @pseudoprice
    Scenario Outline: An article can have a pseudo price
        Given I am on the detail page for article <id>
        Then  I should see "<price>"
        And   I should see "<pseudoprice>"
        And   I should see "<discount>%"

        Examples:
            | id  | price    | pseudoprice | discount |
            | 36  | 24,99    | 29,99       | 16,67    |
            | 81  | 7,99     | 9,98        | 19,94    |
            | 113 | 599,00   | 698,99      | 14,3     |
            | 208 | 500,00   | 1.000,00    | 50       |
            | 239 | 2.499,00 | 2.799,00    | 10,72    |

    @javascript
    Scenario: The customer evaluation form has a captcha
        Given I am on the detail page for article 167
        Then  I should see "Sonnenbrille Speed Eyes"
        When  I open the evaluation form
        And   I should see a captcha

    @basePrice
    Scenario Outline: A product can have a base price
        Given I am on the detail page for article <id>
        Then  I should see "Inhalt: <content> (<basePrice> * / <baseUnit>)"

        Examples:
            | id | content   | basePrice | baseUnit  |
            | 3  | 0.7 Liter | 21,36 €   | 1 Liter   |
            | 18 | 10 Gramm  | 22,00 €   | 100 Gramm |

    @basePrice @variants
    Scenario Outline: Each variant can have a different base price
        Given I am on the detail page for variant "<number>" of article 2
        Then  I should see "Inhalt: <content> (<basePrice> * / 1 Liter)"

        Examples:
            | number    | content   | basePrice |
            |           | 0.5 Liter | 39,98 €   |
            | SW10002.1 | 1.5 Liter | 39,99 €   |
            | SW10002.2 | 5 Liter   | 39,80 €   |

    @notAvailable
    Scenario: The customer evaluation form has a captcha
        Given I am on the detail page for article 199
        Then  I should see "Dieser Artikel steht derzeit nicht zur Verfügung!"
        But   I should not see "In den Warenkorb"
