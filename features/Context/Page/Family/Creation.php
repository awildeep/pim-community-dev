<?php

namespace Context\Page\Family;

use Context\Page\Base\Form;

/**
 * Family creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    protected $path = '/configuration/family/create';

    /**
     * @param string $name
     * @param string $locale
     *
     * @return string
     */
    public function getFieldLocator($name, $locale)
    {
        return sprintf('pim_family_%s_%s', strtolower($name), $locale);
    }
}
