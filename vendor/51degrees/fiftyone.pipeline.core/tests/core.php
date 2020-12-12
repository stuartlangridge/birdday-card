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

namespace fiftyone\pipeline\core\tests;

require(__DIR__ . "/../vendor/autoload.php");

require(__DIR__ . "/classes/TestPipeline.php");

use fiftyone\pipeline\core\PipelineBuilder;

use fiftyone\pipeline\core\tests\ErrorFlowData;
use fiftyone\pipeline\core\tests\StopFlowData;
use fiftyone\pipeline\core\tests\MemoryLogger;
use fiftyone\pipeline\core\tests\ExampleFlowElement1;
use fiftyone\pipeline\core\tests\ExampleFlowElement2;
use fiftyone\pipeline\core\tests\TestPipelineBuilder;

use PHPUnit\Framework\TestCase;

class CoreTests extends TestCase
{
    // Test logging works
    public function testLogger()
    {
        $testPipeline = new TestPipeline();
        $loggerMessage = $testPipeline->logger->log[0]["message"];
        $this->assertTrue($loggerMessage === "test");
    }
    
    // Test getting evidence
    public function testEvidence()
    {
        $testPipeline = new TestPipeline();
        $userAgent = $testPipeline->flowData->evidence->get("header.user-agent");
        $this->assertTrue($userAgent === "test");
    }

    // Test filtering evidence
    public function testEvidenceKeyFilter()
    {
        $testPipeline = new TestPipeline();
        $nullEvidence = $testPipeline->flowData->evidence->get("header.other-evidence");
        $this->assertTrue($nullEvidence === null);
    }

    // Test Getter methods
    public function testGet()
    {
        $testPipeline = new TestPipeline();
        $getValue = $testPipeline->flowData->get("example1")->get("integer");
        $this->assertTrue($getValue === 5);
    }

    public function testGetWhere()
    {
        $testPipeline = new TestPipeline();
        $getValue = count($testPipeline->flowData->getWhere("type", "int"));
        $this->assertTrue($getValue === 1);
    }

    public function testGetFromElement()
    {
        $testPipeline = new TestPipeline();
        $getValue = $testPipeline->flowData->getFromElement($testPipeline->flowElement1)->get("integer");
        $this->assertTrue($getValue === 5);
    }

    // Test check stop FlowData works
    public function testStopFlowData()
    {
        $testPipeline = new TestPipeline();
        $getValue = $testPipeline->flowData->get("example2");
        $this->assertTrue($getValue === null);
    }

    // Test errors are returned
    public function testErrors()
    {
        $testPipeline = new TestPipeline();
        $getValue = $testPipeline->flowData->errors["error"];
        $this->assertTrue(isset($getValue));
    }

    // Test if adding properties at a later stage works (for cloud FlowElements for example)
    public function testUpdateProperties()
    {
        $flowElement1 = new ExampleFlowElement1();
        $logger = new MemoryLogger("info");
        $pipeline = (new PipelineBuilder())->add($flowElement1)->add(new ErrorFlowData())
        ->add(new StopFlowData())
        ->add(new ExampleFlowElement2())
        ->addLogger($logger)
        ->build();
        $flowElement1->properties["integer"]["testing"] = "true";
        $flowData = $pipeline->createFlowData();
        $flowData->evidence->set("header.user-agent", "test");
        $flowData->evidence->set("some.other-evidence", "test");
        $flowData->process();

        $getValue = count($flowData->getWhere("testing", "true"));
        $this->assertTrue($getValue === 0);
        $flowElement1->updatePropertyList();
        $getValue = count($flowData->getWhere("testing", "true"));
        $this->assertTrue($getValue === 1);
    }
}
