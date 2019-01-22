@api @search

Feature: User is searching repository

# Given - When - Then
    Scenario: I want to get a list of the repository that references behat
        Given I am an anonymous user
        When I search for behat
        Then I get a result