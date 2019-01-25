<?php

use Behat\Behat\Context\Context;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{

    protected $response = null;

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
        //simuliere einen HTTP Request (hier: zum Github, siehe Browser https://api.github.com/search/repositories?q=behat)
        $client = new GuzzleHttp\Client(['base_uri' => 'https://api.github.com']);
        $this->response = $client->get('/search/repositories?q=behat'); //searching for "behat" in Github Repositories
    }

    /**
     * @Then I get a result
     */
    public function iGetAResult()
    {
        $response_code = $this->response->getStatusCode();
        print("Status Code: " . $response_code);
        if ($response_code != 200) {
            throw new Exception("Habe keine gültigen HTTP Status Code (200) von Webseite erhalten");
        }
    }
}
