<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    // FeatureContext hat fehlende Schritte . Definiere diese mit den folgenden Snippets :

    /**
     * @Given I am an anonymous user
     */
    public function iAmAnAnonymousUser()
    {
        return true;
    }

    /**
     * @When I search for behat
     */
    public function iSearchForBehat()
    {
        return true;
    }

    /**
     * @Then I get a result
     */
    public function iGetAResult()
    {
        return true;
    }    
}
