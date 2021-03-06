<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

/**
 * Convert a basic representation of a product enabled into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEnabledConverter
{
    const ENABLED_KEY = '[enabled]';

    /**
     * @param array $data
     *
     * @return array
     */
    public function convert($data)
    {
        if (array_key_exists(self::ENABLED_KEY, $data)) {
            return array('enabled' => $data[self::ENABLED_KEY]);
        }

        return array();
    }
}
