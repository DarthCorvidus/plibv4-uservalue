<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
class UserValueTest extends TestCase {
	/**
	 * Test construct
	 */
	function testConstruct() {
		$value = new UserValue();
		$this->assertInstanceOf(UserValue::class, $value);
	}
	
	/**
	 * Test get value
	 * 
	 * Set a value and get it.
	 */
	function testGetValue() {
		$value = new UserValue();
		$value->setValue("Jonas Wagner");
		$this->assertEquals("Jonas Wagner", $value->getValue());
	}
	
	/**
	 * Test get empty optional
	 * 
	 * Set no value and allow empty values, which result in an empty string.
	 */
	function testGetEmptyOptional() {
		$value = new UserValue(FALSE);
		$this->assertEquals("", $value->getValue());
	}

	/**
	 * Test get Empty mandatory
	 * 
	 * Leave a mandatory value empty, which throws an exception.
	 */
	function testGetEmptyMandatory() {
		$value = new UserValue(TRUE);
		$this->expectException(MandatoryException::class);
		$value->getValue();
	}
	
	/**
	 * Test set empty optional
	 * 
	 * Set an empty string to a non-mandatory value.
	 */
	function testSetEmptyOptional() {
		$value = new UserValue(FALSE);
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}

	/**
	 * Test set empty mandatory
	 * 
	 * Set an empty value to a mandatory instance, which throws an exception
	 */
	function testSetEmptyMandatory() {
		$value = new UserValue(TRUE);
		$this->expectException(RuntimeException::class);
		$value->setValue("");
	}

	/**
	 * Test Zero not Empty
	 * 
	 * Set 0 instead of empty string, which is NOT empty.
	 */
	function testSetZeroMandatory() {
		$value = new UserValue(TRUE);
		$value->setValue("0");
		$this->assertEquals("0", $value->getValue());
	}

	/**
	 * Test trim
	 * 
	 * Test that values will be trimmed.
	 */
	function testTrim() {
		$value = new UserValue();
		$value->setValue("Jonas Wagner ");
		$this->assertEquals("Jonas Wagner", $value->getValue());
	}

	/**
	 * Test no Trim
	 * 
	 * Test that values will not be trimmed if notrim is used.
	 */
	function testNoTrim() {
		$value = new UserValue();
		$value->noTrim();
		$value->setValue("Jonas Wagner ");
		$this->assertEquals("Jonas Wagner ", $value->getValue());
	}
	
	/**
	 * Test Validate Date
	 * 
	 * Test to validate a valid date.
	 */
	function testValidateValidDate() {
		$value = new UserValue();
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$value->setValue("2010-01-01");
		$this->assertEquals("2010-01-01", $value->getValue());
		
	}

	/**
	 * Test validate invalid date
	 * 
	 * Test to validate an invalidate date, which throws a ValidateException.
	 */
	function testValidateInvalidDate() {
		$value = new UserValue();
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$this->expectException(ValidateException::class);
		$value->setValue("Bogus");
	}
	
	/**
	 * Test validate date optional
	 * 
	 * Test that an empty string for an optional value won't be validated.
	 */
	function testValidateDateOptional() {
		$value = new UserValue(FALSE);
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}
	
	/**
	 * Test convert
	 * 
	 * Set implementation of Convert and test if a string is converted
	 */
	function testConvert() {
		$value = new UserValue();
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$value->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$value->setValue("2010-01-01");
		$this->assertEquals("01.01.2010", $value->getValue());
	}
	
	/**
	 * Test convert optional
	 * 
	 * Test that an empty string for an optional value won't be converted.
	 */
	function testConvertOptional() {
		$value = new UserValue(FALSE);
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$value->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}
}