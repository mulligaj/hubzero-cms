<?php
/**
 * Test class for the answers API component controller
 * 
 * @author Shawn Rice <zooley@purdue.edu>
 */

// Include time api component controller
require_once JPATH_BASE . DS . 'components' . DS . 'com_answers' . DS . 'controllers' . DS . 'api.php';

/**
 * Test class for time component api controller
 */
class AnswersControllerApiTest extends PHPUnit_Framework_TestCase
{
	var $instance = null;

	/**
	 * Setup
	 */
	function setUp()
	{
		$this->instance = new AnswersApiController();
	}

	/**
	 * Tear down
	 */
	function tearDown()
	{
		$this->instance = null;
	}

	/**
	 * Test if $this->instance is an object
	 *
	 * @group com_answers
	 */
	function testInstanceIsObject()
	{
		$this->assertType('object', $this->instance);
	}

	/**
	 * Test that instance extends \Hubzero\Component\ApiController
	 *
	 * @group com_answers
	 */
	function testExtendsHubzeroApiController()
	{
		$this->assertTrue($this->instance instanceof \Hubzero\Component\ApiController);
	}
}