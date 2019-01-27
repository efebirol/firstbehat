@api @search

Feature: User is searching repository

# Given - When - Then
    Scenario: I want to get a list of the repository that references behat
        Given I am an anonymous user
        When I search for behat
        Then I get a result

    # Search with different status code (ToDo: check possible status code)

    #I get a empty search back
    Scenario: I am searching for a keyword that returns empty search result
        Given I am an anonymous user
        When I search for behat on "/search/repositories?q=xzyhadf"
        Then I get a response code with status "200"
        And I get a result

