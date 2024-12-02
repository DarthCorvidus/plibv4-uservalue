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
	 * Test create as mandatory
	 */
	function testCreateAsMandatory() {
		$value = UserValue::asMandatory();
		$this->assertInstanceOf(UserValue::class, $value);
		$this->assertEquals(TRUE, $value->isMandatory());
	}

	/**
	 * Test create as optional
	 */
	function testCreateAsOptional() {
		$value = UserValue::asOptional();
		$this->assertInstanceOf(UserValue::class, $value);
		$this->assertEquals(FALSE, $value->isMandatory());
	}
	
	function testIsEmpty() {
		$this->assertEquals(FALSE, UserValue::isEmpty("Dog"));
		$this->assertEquals(FALSE, UserValue::isEmpty("0"));
		$this->assertEquals(FALSE, UserValue::isEmpty("0.0"));
		$this->assertEquals(TRUE, UserValue::isEmpty(NULL));
		$this->assertEquals(TRUE, UserValue::isEmpty(""));
	}
	
	/**
	 * This test is redundant now, as string can be defined as type.
	 */
	function testIsEmptyWrongType() {
		$this->expectException(TypeError::class);
		UserValue::isEmpty(3);
	}
	
	/**
	 * Test get value
	 * 
	 * Set a value and get it.
	 */
	function testGetValue() {
		$value = UserValue::asMandatory();
		$value->setValue("Jonas Wagner");
		$this->assertEquals("Jonas Wagner", $value->getValue());
	}
	
	/**
	 * Test get empty optional
	 * 
	 * Set no value and allow empty values, which result in an empty string.
	 */
	function testGetEmptyOptional() {
		$value = UserValue::asOptional();
		$this->assertEquals("", $value->getValue());
	}

	/**
	 * Test get Empty mandatory
	 * 
	 * Leave a mandatory value empty, which throws an exception.
	 */
	function testGetEmptyMandatory() {
		$value = UserValue::asMandatory();
		$this->expectException(MandatoryException::class);
		$value->getValue();
	}
	
	/**
	 * Test set empty optional
	 * 
	 * Set an empty string to a non-mandatory value.
	 */
	function testSetEmptyOptional() {
		$value = UserValue::asOptional();
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}

	/**
	 * Test set empty mandatory
	 * 
	 * Set an empty value to a mandatory instance, which throws an exception
	 */
	function testSetEmptyMandatory() {
		$value = UserValue::asMandatory();
		$this->expectException(RuntimeException::class);
		$value->setValue("");
	}

	/**
	 * Test Zero not Empty
	 * 
	 * Set 0 instead of empty string, which is NOT empty.
	 */
	function testSetZeroMandatory() {
		$value = UserValue::asMandatory();
		$value->setValue("0");
		$this->assertEquals("0", $value->getValue());
	}

	/**
	 * Test trim
	 * 
	 * Test that values will be trimmed.
	 */
	function testTrim() {
		$value = UserValue::asMandatory();
		$value->setValue("Jonas Wagner ");
		$this->assertEquals("Jonas Wagner", $value->getValue());
	}

	/**
	 * Test no Trim
	 * 
	 * Test that values will not be trimmed if notrim is used.
	 */
	function testNoTrim() {
		$value = UserValue::asMandatory();
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
		$value = UserValue::asMandatory();
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
		$value = UserValue::asMandatory();
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
		$value = UserValue::asOptional();
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
		$value = UserValue::asMandatory();
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
		$value = UserValue::asOptional();
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$value->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}
	
	function testSetDefaultNoSetValueCalled() {
		$value = UserValue::asOptional();
		$value->setDefault("02:00:00");
		$this->assertEquals("02:00:00", $value->getValue());
	}

	function testSetDefaultWithSetValue() {
		$value = UserValue::asOptional();
		$value->setDefault("02:00:00");
		$value->setValue("03:00:00");
		$this->assertEquals("03:00:00", $value->getValue());
	}

	/**
	 * Set Default Mandatory
	 * 
	 * If a default value is set, no MandatoryException will be thrown.
	 */
	function testSetDefaultMandatory() {
		$value = UserValue::asMandatory();
		$value->setDefault("02:00:00");
		$this->assertEquals("02:00:00", $value->getValue());
	}

	/**
	 * Set Default Empty Mandatory
	 * 
	 * If a default value is set, but an empty value is set, a MandatoryException
	 * will be thrown.
	 * This is to protect the user from unexpected behaviour, ie entering "" but
	 * getting 02:00:00 here.
	 */
	function testSetDefaultEmptyMandatory() {
		$value = UserValue::asMandatory();
		$value->setDefault("02:00:00");
		$this->expectException(MandatoryException::class);
		$value->setValue("");
	}
	
	/**
	 * Set Empty Default Optional
	 * 
	 * If a value is optional, but defaulted, user input has precedence, as the
	 * programmer assumes that empty values are allowed.
	 * Example: user uses setDescription="" to clear out a description, but the
	 * original description was used as default.
	 */
	function testSetDefaultEmptyOptional() {
		$value = UserValue::asOptional();
		$value->setDefault("02:00:00");
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}

	function testSetEmptyDefaultForbidden() {
		$value = UserValue::asMandatory();
		$this->expectException(MandatoryException::class);
		$value->setDefault("");
		$value->getValue();
	}
	
	function testSetEmptyDefaultAllowed() {
		$value = UserValue::asMandatory();
		$value->setDefault("", TRUE);
		$this->expectException(MandatoryException::class);
		$value->getValue();
	}

}