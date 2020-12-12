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

use fiftyone\pipeline\core\FlowElement;

/**
 * An engine is an extension of the Pipeline Core flowElement class
 * It allows for a cache, restricted properties and meaningful errors when
 * a property isn't available via the aspect data missingPropertyService
 *
*/
class Engine extends FlowElement
{

    /**
    * Add a cache to an engine
    * @param Cache (cache with get and set methods)
    */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
    * Add a subset of properties
    * @param string[] an array of properties to include
    */
    public function setRestrictedProperties($propertiesList)
    {
        $this->restrictedProperties = $propertiesList;
    }

    /**
    * A method to check if a flowData's evidence is in the cache
    * @param FlowData
    */
    public function inCache($flowData)
    {
        $keys = $this->filterEvidence($flowData);

        $cached = $this->cache->get($keys);

        if ($cached) {
            $flowData->setElementData($cached);

            return true;
        } else {
            return false;
        }
    }

    /**
    * Engine's core process function.
    * Calls specific overriden processInternal methods but wraps it in a cache check
    * and a cache put
    * @param FlowData
    */
    public function process($flowData)
    {
        if (isset($this->cache)) {
            if ($this->inCache($flowData)) {
                return true;
            }
        }

        $this->processInternal($flowData);

        if (isset($this->cache)) {
            $keys = $this->filterEvidence($flowData);

            $this->cache->set($keys, $flowData->get($this->dataKey));
        }
    }
}
