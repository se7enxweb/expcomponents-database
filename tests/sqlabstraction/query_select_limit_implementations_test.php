<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package Database
 * @subpackage Tests
 */

/**
 * Testing the limit() reimplementations of the MSSQL and Oracle select
 * queries. Only the generated SQL is looked at, so no such server is needed.
 *
 * @package Database
 * @subpackage Tests
 */
class ezcQuerySelectLimitImplementationsTest extends ezcTestCase
{
    private $db;

    protected function setUp() : void
    {
        try
        {
            $this->db = ezcDbInstance::get();
        }
        catch ( Exception $e )
        {
            $this->markTestSkipped();
        }
    }

    public function testOracleLimitWithOffsetDigitStrings()
    {
        $q = new ezcQuerySelectOracle( $this->db );
        $q->select( '*' )->from( 'table' )->limit( "3", "6" );
        $query = $q->getQuery();
        $this->assertStringContainsStringIgnoringCase( 'ROWNUM <= 9', $query );
        $this->assertStringContainsString( 'rn >= 7', $query );
    }

    public function testOracleLimitNoOffset()
    {
        $q = new ezcQuerySelectOracle( $this->db );
        $q->select( '*' )->from( 'table' )->limit( 2 );
        $this->assertStringContainsString( 'ROWNUM <= 2', $q->getQuery() );
    }

    public function testOracleLimitRefusesSqlText()
    {
        $q = new ezcQuerySelectOracle( $this->db );
        $q->select( '*' )->from( 'table' );
        try
        {
            $q->limit( 1, "0 OR 1=1" );
            $this->fail( "Expected exception" );
        }
        catch ( ezcQueryInvalidParameterException $e )
        {
        }
        $this->assertStringNotContainsStringIgnoringCase( 'ROWNUM', $q->getQuery() );
    }

    public function testMssqlLimitRefusesSqlText()
    {
        $q = new ezcQuerySelectMssql( $this->db );
        $q->select( '*' )->from( 'table' );
        try
        {
            $q->limit( "10 OR 1=1" );
            $this->fail( "Expected exception" );
        }
        catch ( ezcQueryInvalidParameterException $e )
        {
        }
        $this->assertStringNotContainsString( 'TOP', $q->getQuery() );
    }

    public function testMssqlLimitNoOffset()
    {
        $q = new ezcQuerySelectMssql( $this->db );
        $q->select( '*' )->from( 'table' )->limit( 5 );
        $this->assertStringContainsString( 'TOP 5', $q->getQuery() );
    }

    public static function suite()
    {
        return new PHPUnit\Framework\TestSuite( 'ezcQuerySelectLimitImplementationsTest' );
    }
}
?>
