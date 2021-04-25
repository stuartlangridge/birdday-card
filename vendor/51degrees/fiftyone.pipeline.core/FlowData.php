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

namespace fiftyone\pipeline\core;

/**
  * FlowData is created by a specific Pipeline
  * It collects evidence set by the user
  * It passes evidence to FlowElements in the Pipeline
  * These elements can return ElementData or populate an errors object
*/
class FlowData
{
    public $pipeline;
    public $stopped = false;
    public $evidence;
    public $data;
    public $processed;
    public $errors = array();

    /**
     * Constructor for FlowData
     * @param Pipeline // parent Pipeline
    */
    public function __construct($pipeline)
    {
        $this->pipeline = $pipeline;

        $this->evidence = new Evidence($this);
    }

    /**
      * process function runs the process function on every attached FlowElement
      * allowing data to be changed based on evidence
      * This can only be run once per FlowData instance
      * @return FlowData
    */
    public function process()
    {
        if (!$this->processed) {
            foreach ($this->pipeline->flowElements as $flowElement) {
                if (!$this->stopped) {
            // All errors are caught and stored in an errors array keyed by the
                    // FlowElement that set the error

                    try {
                        $flowElement->process($this);
                    } catch (\Throwable $e) {
                        $this->setError($flowElement->dataKey, $e);
                    } catch (\Exception $e) {
                        $this->setError($flowElement->dataKey, $e);
                    }
                }
            }

            // Set processed flag to true. FlowData can only be processed once

            $this->processed = true;
            return $this;
        } else {
            $this->setError("global", "FlowData already processed");
        }
    }

    /**
     * Retrieve data by FlowElement object
     * @param FlowElement
     * @return ElementData
    */
    public function getFromElement($flowElement)
    {
        if (isset($this->data[$flowElement->dataKey])) {
            return $this->data[$flowElement->dataKey];
        } else {
            return null;
        }
    }

    /**
     * Retrieve data by FlowElement key
     * @param string FlowElementDataKey
     * @return ElementData
     *
    */
    public function get($flowElementKey)
    {
        if (isset($this->data[$flowElementKey])) {
            return $this->data[$flowElementKey];
        } else {
            return null;
        }
    }

    /**
     * Magic getter to allow $FlowData->FlowElementKey getting
     * @param string FlowElementKey
     * @return $ElementData
    */
    public function __get($flowElementKey)
    {
        if (isset($this->data[$flowElementKey])) {
            return $this->data[$flowElementKey];
        } else {
            return null;
        }
    }

    /**
     * Set data (used by FlowElement)
     * @param ElementData
    */
    public function setElementData($data)
    {
        $this->data[$data->flowElement->dataKey] = $data;
    }

    /**
     * Set error (should be keyed by FlowElement dataKey)
     * @param string key
     * @param string error message
    */
    public function setError($key, $error)
    {
        if (!isset($this->errors[$key])) {
            $this->errors[$key] = array();
        }

        $this->pipeline->log("error", $error);

        $this->errors[$key][] = $error;
    }

    /**
     * Get an array evidence stored in the FlowData, filtered by
     * its FlowElements' EvidenceKeyFilters
     * @return array
    */
    public function getEvidenceDataKey()
    {
        $requestedEvidence = array();
        $evidence = $this->evidence->getAll();

        foreach ($this->Pipeline->flowElements as $flowElement) {
            $requestedEvidence = array_merge($requestedEvidence, $flowElement->filterEvidence($this));
        }

        return $requestedEvidence;
    }

    /**
     * Stop processing any subsequent FlowElements
     * @return void
    */
    public function stop()
    {
        $this->stopped = true;
    }

    /**
     * Get data from FlowElement based on property meta data
     * @param string metakey
     * @param mixed metavalue
     * @return array
    */
    public function getWhere($metaKey, $metaValue)
    {
        $metaKey = \strtolower($metaKey);
        $metaValue = \strtolower($metaValue);

        $keys = array();
      
        if (isset($this->pipeline->propertyDatabase[$metaKey])) {
            if (isset($this->pipeline->propertyDatabase[$metaKey][$metaValue])) {
                foreach ($this->pipeline->propertyDatabase[$metaKey][$metaValue] as $key => $value) {
                    $keys[$key] = $value["flowElement"];
                }
            }
        }

        $output = array();

        if (isset($keys)) {
            foreach ($keys as $key => $flowElement) {
            // First check if FlowElement has any data set

                $data = $this->get($flowElement);

                if ($data) {
                    try {
                        $output[$key] = $data->get($key);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $output;
    }
}
