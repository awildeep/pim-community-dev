@javascript
Feature: Sort import profiles
  In order to sort import profiles in the catalog
  As a user
  I need to be able to sort import profiles by several columns in the catalog

  Background:
    Given the following jobs:
      | connector            | alias            | code           | label             | type   |
      | Akeneo CSV Connector | product_import   | acme_product   | Product Acme      | import |
      | Akeneo CSV Connector | attribute_import | acme_attribute | Attribute         | import |
      | Akeneo CSV Connector | product_import   | foo_product    | Product           | import |
      | Akeneo CSV Connector | category_import  | acme_category  | Category for Acme | import |
    Given I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the imports page
    Then the rows should be sortable by Code, Label, Job, Connector and Status
    And the rows should be sorted ascending by code
    And I should see sorted locales acme_attribute, acme_category, acme_product and foo_product

  Scenario: Successfully sort import profiles by code
    Given I am on the imports page
    When I sort by "code" value ascending
    Then I should see sorted import profiles acme_attribute, acme_category, acme_product and foo_product
    When I sort by "code" value descending
    Then I should see sorted import profiles foo_product, acme_product, acme_category and acme_attribute

  Scenario: Successfully sort import profiles by label
    Given I am on the imports page
    When I sort by "label" value ascending
    Then I should see sorted import profiles acme_attribute, acme_category, foo_product and acme_product
    When I sort by "label" value descending
    Then I should see sorted import profiles acme_product, foo_product, acme_category and acme_attribute

  Scenario: Successfully sort import profiles by job
    Given I am on the imports page
    When I sort by "job" value ascending
    Then I should see sorted import profiles acme_attribute, acme_category, acme_product, foo_product
    When I sort by "job" value descending
    Then I should see sorted import profiles acme_product, foo_product, acme_category and acme_attribute

  Scenario: Successfully sort import profiles by connector
    Given I am on the imports page
    When I sort by "connector" value ascending
    Then I should see sorted import profiles acme_product, acme_attribute, foo_product and acme_category
    When I sort by "connector" value descending
    Then I should see sorted import profiles acme_product, acme_attribute, foo_product and acme_category

  Scenario: Successfully sort import profiles by status
    Given I am on the imports page
    When I sort by "status" value ascending
    Then I should see sorted import profiles acme_product, acme_attribute, foo_product and acme_category
    When I sort by "status" value descending
    Then I should see sorted import profiles acme_product, acme_attribute, foo_product and acme_category
