<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Context for navigating the website
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigationContext extends RawMinkContext implements PageObjectAwareInterface
{
    /**
     * @var string|null $currentPage
     */
    public $currentPage = null;

    /**
     * @var string $username
     */
    private $username = null;

    /**
     * @var string $password
     */
    private $password = null;

    /**
     * @var PageFactory $pageFactory
     */
    private $pageFactory = null;

    /**
     * @var array $pageMapping
     */
    private $pageMapping = array(
        'associations'             => 'Association index',
        'attributes'               => 'Attribute index',
        'categories'               => 'Category tree creation',
        'channels'                 => 'Channel index',
        'currencies'               => 'Currency index',
        'exports'                  => 'Export index',
        'families'                 => 'Family index',
        'home'                     => 'Base index',
        'imports'                  => 'Import index',
        'locales'                  => 'Locale index',
        'products'                 => 'Product index',
        'product groups'           => 'ProductGroup index',
        'users'                    => 'User index',
        'user roles'               => 'UserRole index',
        'user groups'              => 'UserGroup index',
        'variants'                 => 'Variant index',
        'attribute groups'         => 'AttributeGroup index',
        'attribute group creation' => 'AttributeGroup creation',
    );

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @BeforeScenario
     */
    public function resetCurrentPage()
    {
        $this->currentPage = null;
    }

    /**
     * @param string $username
     *
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $password = $username;
        $this->getFixturesContext()->getOrCreateUser($username, $password);

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param string $page
     *
     * @Given /^I am on the ([^"]*) page$/
     */
    public function iAmOnThePage($page)
    {
        $page = isset($this->pageMapping[$page]) ? $this->pageMapping[$page] : $page;
        $this->openPage($page);
        $this->wait();
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I edit the "([^"]*)" (\w+)$/
     * @Given /^I am on the "([^"]*)" (\w+) page$/
     */
    public function iAmOnTheEntityEditPage($identifier, $page)
    {
        $page = ucfirst($page);
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" attribute group page$/
     * @Given /^I edit the "([^"]*)" attribute group$/
     */
    public function iAmOnTheAttributeGroupEditPage($identifier)
    {
        $page = 'AttributeGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param Category $category
     *
     * @Given /^I am on the (category "([^"]*)") node creation page$/
     */
    public function iAmOnTheCategoryNodeCreationPage(Category $category)
    {
        $this->openPage('Category node creation', array('id' => $category->getId()));
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) page$/
     */
    public function iAmOnTheImportJobPage(JobInstance $job)
    {
        $this->openPage('Import show', array('id' => $job->getId()));
        $this->wait();
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) page$/
     */
    public function iAmOnTheExportJobPage(JobInstance $job)
    {
        $this->openPage('Export show', array('id' => $job->getId()));
        $this->wait();
    }

     /**
      * @param string $identifier
      *
      * @Given /^I am on the "([^"]*)" product group page$/
      * @Given /^I edit the "([^"]*)" product group$/
      */
     public function iAmOnTheProductGroupEditPage($identifier)
     {
         $page = 'ProductGroup';
         $getter = sprintf('get%s', $page);
         $entity = $this->getFixturesContext()->$getter($identifier);
         $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
     }

    /**
     * @param JobInstance $job
     *
     * @When /^I launch the ("([^"]*)" export job)$/
     */
    public function iLaunchTheExportJob(JobInstance $job)
    {
        $this->openPage('Export launch', array('id' => $job->getId()));
    }

    /**
     * @param string $name
     *
     * @return Page
     */
    public function getPage($name)
    {
        if (null === $this->pageFactory) {
            throw new \RuntimeException('To create pages you need to pass a factory with setPageFactory()');
        }

        $name = implode('\\', array_map('ucfirst', explode(' ', $name)));

        return $this->pageFactory->createPage($name);
    }

    /**
     * @param string $page
     *
     * @Then /^I should be redirected on the (.*) page$/
     */
    public function iShouldBeRedirectedOnThePage($page)
    {
        $this->assertAddress($this->getPage($page)->getUrl());
    }

    /**
     * @param AttributeGroup $group
     *
     * @Given /^I should be on the ("([^"]*)" attribute group) page$/
     */
    public function iShouldBeOnTheAttributeGroupPage(AttributeGroup $group)
    {
        $expectedAddress = $this->getPage('AttributeGroup edit')->getUrl(array('id' => $group->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Family $family
     *
     * @Given /^I should be on the ("([^"]*)" family) page$/
     */
    public function iShouldBeOnTheFamilyPage(Family $family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(array('id' => $family->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Association $association
     *
     * @Given /^I should be on the ("([^"]*)" association) page$/
     */
    public function iShouldBeOnTheAssociationPage(Association $association)
    {
        $expectedAddress = $this->getPage('Association edit')->getUrl(array('id' => $association->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the locales page$/
     */
    public function iShouldBeOnTheLocalesPage()
    {
        $this->assertAddress($this->getPage('Locale index')->getUrl());
    }

    /**
     * @param Category $category
     *
     * @Then /^I should be on the (category "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheCategoryEditPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category edit')->getUrl(array('id' => $category->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Category $category
     *
     * @Given /^I should be on the (category "([^"]*)") node creation page$/
     */
    public function iShouldBeOnTheCategoryNodeCreationPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category node creation')->getUrl(array('id' => $category->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param string $page
     * @param array  $options
     *
     * @return Page
     */
    public function openPage($page, array $options = array())
    {
        $this->currentPage = $page;

        $page = $this->getCurrentPage()->open($options);
        $this->loginIfRequired();
        $this->wait();

        return $page;
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        return $this->getPage($this->currentPage);
    }

    /**
     * @param string $expected
     */
    private function assertAddress($expected)
    {
        $actual = $this->getSession()->getCurrentUrl();
        $result = strpos($actual, $expected) !== false;
        assertTrue($result, sprintf('Expecting to be on page "%s", not "%s"', $expected, $actual));
    }

    /**
     * A method that logs the user in with the previously provided credentials if required by the page
     */
    private function loginIfRequired()
    {
        $loginForm = $this->getCurrentPage()->find('css', '.form-signin');
        if ($loginForm) {
            $loginForm->fillField('_username', $this->username);
            $loginForm->fillField('_password', $this->password);
            $loginForm->pressButton('Log in');
        }
    }

    /**
     * @param integer $time
     * @param string  $condition
     *
     * @return void
     */
    private function wait($time = 5000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
