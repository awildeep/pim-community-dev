Feature: Import partial product informations
  In order to avoid overwriting existing data
  As Julia
  I need to be able to import products without specifying all its properties

  Scenario: Successfully keep the product family if it is not present in the import file
    Given the following family:
      | code       |
      | sports-car |
    And the following product:
      | sku         | family     |
      | honda-civic | sports-car |
    And the following product attributes:
      | code | label | type | product     |
      | name | Name  | text | honda-civic |
    And the following job:
      | connector            | alias          | code                | label                       | type   |
      | Akeneo CSV Connector | product_import | acme_product_import | Product import for Acme.com | import |
    And the following file to import:
    """
    sku;name
    honda-civic;"Honda Civic"
    """
    And the following job "acme_product_import" configuration:
      | element   | property          | value                |
      | reader    | filePath          | {{ file to import }} |
      | reader    | uploadAllowed     | no                   |
      | reader    | delimiter         | ;                    |
      | reader    | enclosure         | "                    |
      | reader    | escape            | \                    |
      | processor | enabled           | yes                  |
      | processor | categories column | categories           |
      | processor | family column     | families             |
      | processor | groups column     | groups               |
    And I am logged in as "Julia"
    When I am on the "acme_product_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be 1 product
    And family of "honda-civic" should be "sports-car"
