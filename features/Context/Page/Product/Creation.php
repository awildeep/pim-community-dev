<?php

namespace Context\Page\Product;

use Context\Page\Base\Form;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Product creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /**
     * @var array
     */
    protected $elements = array(
        'Activated locales' => array('css' => '#select2-drop'),
        'Create popin' => array('css' => 'div.ui-dialog')
    );

    /**
     * {@inheritdoc}
     */
    public function pressButton($locator)
    {
        if ($locator === 'Create') {
            $button = $this
                ->getElement('Create popin')
                ->find('css', sprintf('button:contains("%s")', $locator));

            if ($button) {
                return $button->click();
            }
        }

        parent::pressButton($locator);
    }

    /**
     * {@inheritdoc}
     */
    public function findField($field)
    {
        $label = $this->find('css', sprintf('#pim_product_create label:contains("%s")', $field));

        if (!$label) {
            throw new ElementNotFoundException($this->getSession(), 'form label ', 'value', $field);
        }

        $field = $label->getParent()->find('css', 'input');

        if (!$field) {
            throw new ElementNotFoundException($this->getSession(), 'form field ', 'id|name|label|value', $field);
        }

        return $field;
    }
}
