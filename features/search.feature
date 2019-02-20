@api @search

Feature: User is searching repository

# Given - When - Then
    Scenario: I want to get a list of the repository that references behat
        Given I am an anonymous user
        When I search for behat
        Then I get a result

    # Search with different status code (ToDo: check possible status code)

    #I get a result in search back
    Scenario: I am searching for a keyword that returns a search result
        Given I am an anonymous user
        When I search for behat on "/search/repositories?q=behat"
        Then I except a response code with status "200"
        And I get a result

    #I get a empty search back
    Scenario: I am searching for a keyword that returns empty search result
        Given I am an anonymous user
        When I search for behat on "/search/repositories?q=xzyhadf"
        Then I except a response code with status "200"
        And I get no result

    #I get at least a certain number of search results back
    Scenario: I am searching for a keyword that returns empty search result
        Given I am an anonymous user
        When I search for behat on "/search/repositories?q=behat"
        Then I except a response code with status "200"
        And I except at least "1" result

