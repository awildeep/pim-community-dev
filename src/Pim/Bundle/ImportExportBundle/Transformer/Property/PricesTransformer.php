<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Prices attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformer implements PropertyTransformerInterface, ProductValueUpdaterInterface
{
    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var array
     */
    private $currencies;

    /**
     * Constructor
     *
     * @param CurrencyManager $currencyManager
     */
    public function __construct(CurrencyManager $currencyManager)
    {
        $this->currencyManager = $currencyManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $currencies = $this->getCurrencies();

        $result = array();
        foreach (preg_split('/\s*,\s*/', trim($value)) as $price) {
            if (empty($price)) {
                continue;
            }

            if (0 === preg_match('/^([0-9]*\.?[0-9]*) (\w+)$/', $price, $matches)) {
                throw new InvalidValueException('Malformed price: %value%', array('%value%'=>$price));
            }

            if (in_array($matches[2], $currencies)) {
                $result[$matches[2]] = $matches[1];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateProductValue(ProductValueInterface $productValue, $data, array $options = array())
    {
        $currencies = $this->getCurrencies();
        $removeCurrency = function ($code) use (&$currencies) {
            $pos = array_search($code, $currencies);
            if (false !== $pos) {
                unset($currencies[$pos]);
            }
        };

        foreach ($productValue->getPrices() as $price) {
            $currency = $price->getCurrency();
            if (isset($data[$currency])) {
                $price->setData($data[$currency]);
                $removeCurrency($currency);
                unset($data[$currency]);
            }
        }

        foreach ($data as $currency => $price) {
            $this->addPrice($productValue, $price, $currency);
            $removeCurrency($currency);
        }

        foreach ($currencies as $currency) {
            $this->addPrice($productValue, null, $currency);
        }
    }

    /**
     * Returns the active currencies
     *
     * @return array
     */
    protected function getCurrencies()
    {
        if (!isset($this->currencies)) {
            $this->currencies = $this->currencyManager->getActiveCodes();
        }

        return $this->currencies;
    }

    /**
     * Creates a ProductPrice object
     *
     * @param  float        $data
     * @param  string       $currency
     * @return ProductPrice
     */
    protected function addPrice(ProductValueInterface $productValue, $data, $currency)
    {
        $price = new ProductPrice();
        $productValue->addPrice(
            $price->setValue($productValue)->setData($data)->setCurrency($currency)
        );
    }
}
