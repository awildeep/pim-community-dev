<?php

namespace Pim\Bundle\CatalogBundle\Calculator;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator;

/**
 * Batch launching the calculator
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchCompletenessCalculator
{
    /**
     * @var CompletenessCalculator $calculator
     */
    protected $calculator;

    /**
     * @var ProductManager $productManager
     */
    protected $productManager;

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\PendingCompleteness[]
     */
    protected $pendings;

    /**
     * @staticvar integer
     */
    const MAX_BATCH_SIZE = 200;

    /**
     * Constructor
     * @param CompletenessCalculator $calculator
     * @param ProductManager         $productManager
     * @param EntityManager          $em
     */
    public function __construct(CompletenessCalculator $calculator, ProductManager $productManager, EntityManager $em)
    {
        $this->calculator     = $calculator;
        $this->productManager = $productManager;
        $this->entityManager  = $em;

        $this->pendings = array();
    }

    /**
     * Execute calculator on each needed part defined in pending completeness
     * - channels
     * - locales
     * - families
     */
    public function execute()
    {
        $products = $this->getProductsToCalculate();
        $channels = $this->getPendingChannels();
        $this->calculate($products, $channels);

        $pendingLocales = $this->getPendingLocales();
        $this->calculate($products, $pendingLocales['channels'], $pendingLocales['locales']);
        $this->saveCompletenesses($products);

        $families = $this->getPendingFamilies();
        $products = $this->getProductsToCalculate($families);
        $this->calculate($products);
        $this->saveCompletenesses($products);
    }

    /**
     * Launch calculator for specific channels, locales and products
     * Then automatically remove concerned pendings
     * @param ProductInterface[] $products
     * @param Channel[]          $channels
     * @param Locale[]           $locales
     */
    protected function calculate(array $products, array $channels = array(), array $locales = array())
    {
        $this->calculator->setChannels($channels);
        $this->calculator->setLocales($locales);
        $this->calculator->calculate($products);
        $this->removePending();
    }

    /**
     * Save products with cascading persist on completeness entities linked
     *
     * @param ProductInterface[] $products
     */
    protected function saveCompletenesses(array $products)
    {
        $batchSize = 0;
        foreach ($products as $product) {
            $this->entityManager->persist($product);
            if ($batchSize++ === self::MAX_BATCH_SIZE) {
                $this->entityManager->flush();
                $batchSize = 0;
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Get products to be calculated
     *
     * @param \Pim\Bundle\CatalogBundle\Entity\Family[] $families
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getProductsToCalculate(array $families = array())
    {
        $productRepo = $this->productManager->getFlexibleRepository();
        if (!empty($families)) {
            return $productRepo->findBy(array('family' => $families));
        } else {
            return $productRepo->findByExistingFamily();
        }
    }

    /**
     * Find pending completeness and channels which need completeness recalculation
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel[]
     */
    protected function getPendingChannels()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('channel');

        $channels = array();
        foreach ($this->pendings as $pendingChannel) {
            if (!in_array($pendingChannel->getChannel(), $channels)) {
                $channels[] = $pendingChannel->getChannel();
            }
        }

        return $channels;
    }

    /**
     * Find pending completeness and locales which need completeness recalculation
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale[]
     */
    protected function getPendingLocales()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('locale');

        $locales = array('locales' => array(), 'channels' => array());
        foreach ($this->pendings as $pendingLocale) {
            if (!in_array($pendingLocale->getLocale(), $locales)) {
                $locales['locales']  = $pendingLocale->getLocale();
                $locales['channels'] = $pendingLocale->getChannel();
            }
        }

        return $locales;
    }

    /**
     * Find pending completeness and families which need completeness recalculation
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Family[]
     */
    protected function getPendingFamilies()
    {
        $this->pendings = $this->getPendingCompletenessRepository()->findByNotNull('family');

        $families = array();
        foreach ($this->pendings as $pendingFamily) {
            if (!in_array($pendingFamily->getFamily(), $families)) {
                $families[] = $pendingFamily->getFamily();
            }
        }

        return $families;
    }

    /**
     * Get repository for pending completeness entity
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPendingCompletenessRepository()
    {
        return $this->entityManager->getRepository('PimCatalogBundle:PendingCompleteness');
    }

    /**
     * Remove pending entities from database
     */
    protected function removePending()
    {
        foreach ($this->pendings as $pending) {
            $this->entityManager->remove($pending);
        }

        $this->entityManager->flush();
    }
}
