<?php
/**
 * Copyright (c) Enalean, 2015. All rights reserved
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/
 */

namespace Tracker;

use TestDataBuilder;
use TrackerDataBuilder;
use RestBase;

require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * @group TrackerTests
 */
class ArtifactTest extends RestBase {

    protected $project_id;
    protected $tracker_id;
    protected $slogan_field_id;
    protected $desc_field_id;
    protected $status_field_id;
    protected $status_value_id;

    protected function getResponse($request) {
        return $this->getResponseByToken(
            $this->getTokenForUserName(TestDataBuilder::TEST_USER_1_NAME),
            $request
        );
    }

    public function setUp() {
        parent::setUp();

        $this->project_id = $this->getProjectId();
        $tracker          = $this->getTracker();
        $this->tracker_id = $tracker['id'];

        foreach ($tracker['fields'] as $field) {
            if ($field['name'] === 'slogan') {
                $this->slogan_field_id = $field['field_id'];
            } elseif ($field['name'] === 'epic_desc') {
                $this->desc_field_id = $field['field_id'];
            } elseif ($field['name'] === 'status') {
                $this->status_field_id = $field['field_id'];
                $this->status_value_id = $field['values'][0]['id'];
            }
        }
    }

    private function getProjectId() {
        $response = $this->getResponse($this->client->get('projects/'))->json();
        foreach($response as $project_json) {
            if ($project_json['shortname'] === TrackerDataBuilder::XML_PROJECT_ID_SHORT_NAME) {
                return $project_json['id'];
            }
        }
    }

    private function getTracker() {
        $response = $this->getResponse($this->client->get('projects/'. $this->project_id . '/trackers'))->json();
        return $response[0];
    }

    public function testGetArtifact() {
        $response = $this->getResponse($this->xml_client->get('artifacts/'.TestDataBuilder::RELEASE_ARTIFACT_ID));
        $this->assertEquals($response->getStatusCode(), 200);

        $artifact_xml = $response->xml();

        $this->assertEquals((int) $artifact_xml->id, TestDataBuilder::RELEASE_ARTIFACT_ID);
        $this->assertEquals((int) $artifact_xml->project->id, TestDataBuilder::PROJECT_PRIVATE_MEMBER_ID);
    }

    public function testPOSTArtifact() {
        $xml = "<request><tracker><id>".TestDataBuilder::RELEASES_TRACKER_ID."</id></tracker><values><item><field_id>132</field_id><value>Test Release</value></item><item><field_id>134</field_id><bind_value_ids><item>126</item></bind_value_ids></item></values></request>";

        $response = $this->getResponse($this->xml_client->post('artifacts', null, $xml));

        $this->assertEquals($response->getStatusCode(), 200);
        $artifact_xml = $response->xml();

        $artifact_id = (int) $artifact_xml->id;
        $this->assertGreaterThan(0, $artifact_id);

        return $artifact_id;
    }

    /**
     * @depends testPOSTArtifact
     */
    public function testPUTArtifact($artifact_id) {
        $new_value = 'Test Release Updated';
        $xml       = "<request><tracker><id>".TestDataBuilder::RELEASES_TRACKER_ID."</id></tracker><values><item><field_id>132</field_id><value>".$new_value."</value></item></values></request>";

        $response = $this->getResponse($this->xml_client->put('artifacts/'. $artifact_id, null, $xml));

        $this->assertEquals($response->getStatusCode(), 200);
        $artifact_xml = $this->getResponse($this->xml_client->get('artifacts/'. $artifact_id))->xml();

        $this->assertEquals($new_value, (string)$artifact_xml->values->item[0]->value);
    }

    public function testPOSTArtifactInXMLTracker() {
        $xml = "<request><tracker><id>".$this->tracker_id."</id></tracker><values><item><field_id>".$this->slogan_field_id."</field_id><value>slogan</value></item><item><field_id>".$this->desc_field_id."</field_id><value>desc</value></item><item><field_id>".$this->status_field_id."</field_id><bind_value_ids><item>".$this->status_value_id."</item></bind_value_ids></item></values></request>";

        $response = $this->getResponse($this->xml_client->post('artifacts', null, $xml));

        $this->assertEquals($response->getStatusCode(), 200);
        $artifact_xml = $response->xml();

        $artifact_id = (int) $artifact_xml->id;
        $this->assertGreaterThan(0, $artifact_id);

        return $artifact_id;
    }

    /**
     * @depends testPOSTArtifactInXMLTracker
     */
    public function testGetArtifactInXMLTracker($artifact_id) {
        $response = $this->getResponse($this->xml_client->get('artifacts/'.$artifact_id));
        $this->assertEquals($response->getStatusCode(), 200);

        $artifact_xml = $response->xml();

        $this->assertEquals((int) $artifact_xml->id, $artifact_id);
        $this->assertEquals((int) $artifact_xml->project->id, $this->project_id);
    }

}
