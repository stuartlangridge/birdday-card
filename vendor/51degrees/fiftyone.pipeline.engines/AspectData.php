<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 * Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 * This Original Work is licensed under the European Union Public Licence (EUPL)
 * v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 *
 * If using the Work as, or as part of, a network application, by
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading,
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */

namespace fiftyone\pipeline\engines;

use fiftyone\pipeline\engines\MissingPropertyService;

use fiftyone\pipeline\core\ElementData;

/**
* aspectData extends elementData by adding the option of a missing property service
* It also allows properties to be explicitly excluded by a flowElement / engine
*/
class AspectData extends ElementData
{
    public $missingPropertyService;
    
    /**
    * Constructor for element data
    * Adds default missing property service if not available
    * @param FlowElement
    */
    public function __construct($flowElement)
    {
        if (!isset($this->missingPropertyService)) {
            $this->missingPropertyService = new MissingPropertyService();
        }

        parent::__construct($flowElement);
    }

    /**
    * Get a value (unless in a flowElement's restrictedProperties list)
    * If property not found, call the attached missing property service
    * @param string key
    * @return mixed
    */
    public function get($key)
    {
        if (isset($this->flowElement->restrictedProperties)) {
            if (!in_array($key, $this->flowElement->restrictedProperties)) {
                throw new \Exception("Property " . $key . " was excluded from " . $this->flowElement->dataKey);
            }
        }

        try {
            $result = $this->getInternal($key);

            if (!isset($result)) {
                return $this->missingPropertyService->check($key, $this->flowElement);
            } else {
                return $result;
            }
        } catch (\Exception $e) {
            return $this->missingPropertyService->check($key, $this->flowElement);
        }
    }
}
