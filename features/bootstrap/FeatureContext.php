<?php

use Behat\Behat\Context\SnippetAcceptingContext;



/**
 * Defines application features from the specific context.
 */
class FeatureContext implements SnippetAcceptingContext 
{

    protected $response = null;
    protected $username = null;
    protected $password = null;
    protected $client = null;



    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct($github_username, $github_password)
    {
        $this->username = $github_username;
        $this->password = $github_password;
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
        $this->client = new GuzzleHttp\Client(['base_uri' => 'https://api.github.com']);
        $this->response = $this->client->get('/search/repositories?q=behat'); //searching for "behat" in Github Repositories
    }

    /**
     * @When I search for behat on :urlpath
     */
    public function iSearchForBehatWithPath($urlpath)
    {
        //simuliere einen HTTP Request (hier: zum Github, siehe Browser https://api.github.com/search/repositories?q=behat)
        $this->client = new GuzzleHttp\Client(['base_uri' => 'https://api.github.com']);
        $this->response = $this->client->get($urlpath); //searching for "behat" in Github Repositories
        echo "URL ist: ", $urlpath;
    }


    /**
     * @Then I get a result
     */
    public function iGetAResult()
    {
        $response_code = $this->response->getStatusCode();
        print("Status Code: " . $response_code);
        if ($response_code != 200) {
            throw new Exception("Habe keine gültigen HTTP Status Code (200) von Webseite erhalten. Response Code ist: " , $response_code);
        }
        $data = json_decode($this->response->getBody(), true);
        
        if($data['total_count'] == 0){
            throw new Exception("Found zero results in search!");
        }
    }

    /**
     * @Then I get no result
     */
    public function iGetNoResult()
    {
        $response_code = $this->response->getStatusCode();
        print("Status Code: " . $response_code);
        if ($response_code != 200) {
            throw new Exception("Habe keine gültigen HTTP Status Code (200) von Webseite erhalten. Response Code ist: " , $response_code);
        }
        $data = json_decode($this->response->getBody(), true);
        
        if($data['total_count'] != 0){
            throw new Exception("I should find no result with this search!");
        }
    }

    /**
     * @Then I except a response code with status :status
     */
    public function iGetAResponseCode($status)
    {
        $response_code = $this->response->getStatusCode();
        print("Status Code: " . $response_code);
        if ($response_code != $status) {
            throw new Exception("Habe keine gültigen HTTP Status Code (200) von Webseite erhalten. Response Code ist: " , $response_code);
        }
    }

    /**
     * @Then I except at least :numberResult result
     */
    public function iGetAtLeastResult($numberResult)
    {
        $data = json_decode($this->response->getBody(), true);
        
        if($data['total_count'] <= $numberResult){
            throw new Exception("Es sollte mindestens ".$numberResult." gefunden werden.");
        }
    }


    /** Authentifizierung**/
    
   /**
     * @Given I am an authenticated user
     */
    public function iAmAnAuthenticatedUser()
    {
        $this->client = new GuzzleHttp\Client(
            [
                'base_uri' => 'https://api.github.com',
                'auth' => [$this->username, $this->password]
            ]
        );
        echo "Login mit user: ".$this->username." und password: ".$this->password;
        $response = $this->client->get('/');

        if (200 != $response->getStatusCode()) {
            throw new Exception("Authentication didn't work!");
        }
    }

    /**
     * @When I request a list of my repositories
     */
    public function iRequestAListOfMyRepositories()
    {
        $this->response = $this->client->get('/user/repos');

        if (200 != $this->response->getStatusCode()) {
            throw new Exception("Authentication didn't work!");
        }
    }

    /**
     * @Then The results should include a repostory name :arg1
     */
    public function theResultsShouldIncludeARepostoryName($arg1)
    {
        $repositories = json_decode($this->response->getBody(), true);

        foreach($repositories as $repository) {
            echo "\nName der entfernten Repo: ".$repository['name'];
            if ($repository['name'] == $arg1) {
                return true;
            }
        }

        throw new Exception("Expected to find a repository named '$arg1' but didn't.");
    }

}
