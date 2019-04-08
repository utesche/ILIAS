<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\NewMethodTransformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class NewMethodTransformationTest extends TestCase
{
	private $instance;

	public function setUp()
	{
		$this->instance = new NewMethodTransformationTestClass();
	}

	/**
	 * @throws \ilException
	 * @throws \ReflectionException
	 */
	public function testNewObjectTransformation()
	{
		$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'myMethod');

		$result = $transformation->transform(array('hello', 42));

		$this->assertEquals(array('hello', 42), $result);
	}

	/**
	 * @expectedException \TypeError
	 */
	public function testNewMethodTransformationThrowsTypeErrorOnInvalidConstructorArguments()
	{
		$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'myMethod');

		$object = $transformation->transform(array('hello', 'world'));

		$this->fail();
	}

	public function testClassDoesNotExistWillThrowException()
	{
		try {
			$transformation = new NewMethodTransformation('BreakdanceMcFunkyPants', 'myMethod');
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testMethodDoesNotExistOnClassWillThrowException()
	{
		try {
			$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'someMethod');
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testPrivateMethodCanNotBeCalledInTransform()
	{
		$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'myPrivateMethod');

		try {
			$object = $transformation->transform(array('hello', 10));
		} catch  (\Error $error) {
			return;
		}

		$this->fail();
	}

	public function testPrivateMethodCanNotBeCalledInApplyto()
	{
		$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'myPrivateMethod');
		try {
			$object = $transformation->applyTo(new Ok(array('hello', 10)));
		} catch  (\Error $error) {
			return;
		}

		$this->fail();
	}

	/**
	 * @expectedException \Exception
	 */
	public function testMethodThrowsExceptionInTransform()
	{
		$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'methodThrowsException');

		$object = $transformation->transform(array('hello', 10));

		$this->fail();
	}

	public function testMethodThrowsExceptionInApplyTo()
	{
		$transformation = new NewMethodTransformation(NewMethodTransformationTestClass::class, 'methodThrowsException');

		$object = $transformation->applyTo(new Ok(array('hello', 10)));

		$this->assertTrue($object->isError());
	}
}

class NewMethodTransformationTestClass
{
	public function myMethod(string $string, int $integer)
	{
		return array($string, $integer);
	}

	private function myPrivateMethod(string $string, int $integer)
	{
		return array($string, $integer);
	}

	public function methodThrowsException(string $string, int $integer)
	{
		throw new \Exception('SomeException');
	}
}
