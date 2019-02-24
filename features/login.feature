@api @login

Feature: Give user a list of repositores when logged in
    Scenario: Scenario name
    Given I am an authenticated user
    When I request a list of my repositories 
    Then I get a response with repository names