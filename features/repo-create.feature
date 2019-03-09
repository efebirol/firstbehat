@api @repository

Feature: I need a new repository

    Scenario: I create a new repository
        Given I am an authenticated user
        When I create the "monkey" repository
        When I request a list of my repositories
        Then The results should include a repostory name "monkey" 