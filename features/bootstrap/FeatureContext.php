<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Component\Config\Definition\Exception\Exception;



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

    /** Helper Funtionen  */
    /**
     * @Then I except a response code with status :status
     */
    public function iExpectAResponseCode($status)
    {
        $response_code = $this->response->getStatusCode();
        print("\nStatus Code: " . $response_code);
        if ($response_code != $status) {
            throw new Exception("Habe keine gültigen HTTP Status Code (200) von Webseite erhalten. Response Code ist: ", $response_code);
        }
    }

    public function getBodyAsJson()
    {
        return json_decode($this->response->getBody(), true);
    }

    protected function iExpectASuccessfulRequest()
    {
        $response_code = $this->response->getStatusCode();
        if ('2' != substr($response_code, 0, 1)) {
            throw new Exception("We expected a successful request but received a $response_code instead!");
        }
    }

    protected function iExpectAFailedRequest()
    {
        $response_code = $this->response->getStatusCode();
        if ('4' != substr($response_code, 0, 1)) {
            throw new Exception("We expected a failed request but received a $response_code instead!");
        }
    }

    /** Test Funktionen */
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

        $this->iExpectAResponseCode(200);

        $data = $this->getBodyAsJson();

        if ($data['total_count'] == 0) {
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
        $this->iExpectAResponseCode(200);

        $data = $this->getBodyAsJson();

        if ($data['total_count'] != 0) {
            throw new Exception("I should find no result with this search!");
        }
    }

    /**
     * @Then I except at least :numberResult result
     */
    public function iGetAtLeastResult($numberResult)
    {
        $data = $this->getBodyAsJson();

        if ($data['total_count'] <= $numberResult) {
            throw new Exception("Es sollte mindestens " . $numberResult . " gefunden werden.");
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
        echo "Login mit user: " . $this->username . " und password: " . $this->password . "\n";
        $this->response = $this->client->get('/');

        $response_code = $this->response->getStatusCode();
        $this->iExpectAResponseCode(200);
    }

    /**
     * @When I request a list of my repositories
     */
    public function iRequestAListOfMyRepositories()
    {
        $this->response = $this->client->get('/user/repos');
        $response_code = $this->response->getStatusCode();
        $this->iExpectAResponseCode(200);
    }

    /**
     * @Then The results should include a repostory name :arg1
     */
    public function theResultsShouldIncludeARepostoryName($arg1)
    {
        $repositories = $this->getBodyAsJson();

        foreach ($repositories as $repository) {
            echo "\nName der entfernten Repo: " . $repository['name'];
            if ($repository['name'] == $arg1) {
                return true;
            }
        }

        throw new Exception("Expected to find a repository named '$arg1' but didn't.");
    }

    /**
     * @When I create the :arg1 repository
     */
    public function iCreateTheRepository($arg1)
    {
        echo "Versuche die Repo zu erzeugen: " . $arg1;
        $parameters = json_encode(['name' => $arg1]);
        $this->client->post('/user/repos', ['body' => $parameters]);
        $this->iExpectAResponseCode(200);

        //prüfe ob die Repo schon existiert
        $this->iRequestAListOfMyRepositories();
        $this->theResultsShouldIncludeARepostoryName($arg1);
    }

    /**
     * @Given I have a repository called :arg1
     */
    public function iHaveARepositoryCalled($arg1)
    {
        //hole mir alle Repos
        $this->response = $this->client->get('/user/repos');
        $response_code = $this->response->getStatusCode();
        $this->iExpectAResponseCode(200);

        //suche nach dem Names der Repositories
        $repositories = $this->getBodyAsJson();

        foreach ($repositories as $repository) {
            echo "\nName der entfernten Repo: " . $repository['name'];
            if ($repository['name'] == $arg1) {
                return true;
            }
        }
    }

    /**
     * @When I watch the :arg1 repository
     * Quelle: sehe https://developer.github.com/v3/activity/watching/#set-a-repository-subscription
     *  für Infos wie man sich als Watcher für eine Repository hinzufügt
     */
    public function iWatchTheRepository($arg1)
    {
        $watch_url = '/repos/' . $this->username . '/' . $arg1 . '/subscription';
        $parameters = json_encode(['subscribed' => 'true']);

        $this->client->put($watch_url, ['body' => $parameters]);
    }


    /**
     * @Then The :arg1 repository will list me as a watcher
     */
    public function theRepositoryWillListMeAsAWatcher($arg1)
    {
        $watch_url = '/repos/' . $this->username . '/' . $arg1 . '/subscribers';
        $this->response = $this->client->get($watch_url);

        $subscribers = $this->getBodyAsJson();

        foreach ($subscribers as $subscriber) {
            if ($subscriber['login'] == $this->username) {
                return true;
            }
        }

        throw new Exception("Did not find '{$this->username}' as a watcher as expected.");
    }

    /**
     * @Then I delete the repository called :arg1
     */
    public function iDeleteTheRepositoryCalled($arg1)
    {
        $delete = '/repos/' . $this->username . '/' . $arg1;
        $this->response = $this->client->delete($delete);

        echo "x1:".$this->response->getStatusCode();

        //Response liefert ein 204 ("no content"), da Repository gelöscht wurde
        $this->iExpectAResponseCode(204);
    }
 
    /**
     * @Given I have the following repositories:
     */
    public function iHaveTheFollowingRepositories(TableNode $table)
    {
        $this->table = $table->getRows();

        // Drop the first element which is the header row
        array_shift($this->table);

        foreach($this->table as $id => $row) {
            $this->table[$id]['name'] = $row[0] . '/' . $row[1];

            $this->response = $this->client->get('/repos/' . $row[0] . '/' . $row[1]);

            $this->iExpectAResponseCode(200);
        }
    }

    /**
     * @When I watch each repository
     */
    public function iWatchEachRepository()
    {
        $parameters = json_encode(['subscribed' => 'true']);

        foreach($this->table as $row) {
            $watch_url = '/repos/' . $row['name'] . '/subscription';
            $this->client->put($watch_url, ['body' => $parameters]);
        }
    }

    /**
     * @Then My watch list will include those repositories
     */
    public function myWatchListWillIncludeThoseRepositories()
    {
        $watch_url = '/users/' . $this->username . '/subscriptions';
        $this->response = $this->client->get($watch_url);
        $watches = $this->getBodyAsJson();

        foreach($this->table as $row) {
            $fullname = $row['name'];

            foreach($watches as $watch) {
                if ($fullname == $watch['full_name']) {
                    break 2;
                }
            }

            throw new Exception("Error! " . $this->username . " is not watching " . $fullname);
        }
    }


}
