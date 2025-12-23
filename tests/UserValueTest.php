<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

declare(strict_types=1);

namespace plibv4\uservalue;
use PHPUnit\Framework\TestCase;
use plibv4\convert\ConvertDate;
use plibv4\validate\ValidateDate;
use plibv4\validate\ValidateException;
use RuntimeException;

/** @psalm-suppress UnusedClass */
final class UserValueTest extends TestCase {
	/**
	 * Test create as mandatory
	 */
	function testCreateAsMandatory(): void {
		$value = UserValue::asMandatory();
		$this->assertInstanceOf(UserValue::class, $value);
		$this->assertEquals(TRUE, $value->isMandatory());
	}

	/**
	 * Test create as optional
	 */
	function testCreateAsOptional(): void {
		$value = UserValue::asOptional();
		$this->assertInstanceOf(UserValue::class, $value);
		$this->assertEquals(FALSE, $value->isMandatory());
	}
	
	function testIsEmpty(): void {
		$this->assertEquals(FALSE, UserValue::isEmpty("Dog"));
		$this->assertEquals(FALSE, UserValue::isEmpty("0"));
		$this->assertEquals(FALSE, UserValue::isEmpty("0.0"));
		$this->assertEquals(TRUE, UserValue::isEmpty(NULL));
		$this->assertEquals(TRUE, UserValue::isEmpty(""));
	}
	
	/**
	 * Test get value
	 * 
	 * Set a value and get it.
	 */
	function testGetValue(): void {
		$value = UserValue::asMandatory();
		$value->setValue("Jonas Wagner");
		$this->assertEquals("Jonas Wagner", $value->getValue());
	}
	
	/**
	 * Test get empty optional
	 * 
	 * Set no value and allow empty values, which result in an empty string.
	 */
	function testGetEmptyOptional(): void {
		$value = UserValue::asOptional();
		$this->assertEquals("", $value->getValue());
	}

	/**
	 * Test get Empty mandatory
	 * 
	 * Leave a mandatory value empty, which throws an exception.
	 */
	function testGetEmptyMandatory(): void {
		$value = UserValue::asMandatory();
		$this->expectException(MandatoryException::class);
		$value->getValue();
	}
	
	/**
	 * Test set empty optional
	 * 
	 * Set an empty string to a non-mandatory value.
	 */
	function testSetEmptyOptional(): void {
		$value = UserValue::asOptional();
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}

	/**
	 * Test set empty mandatory
	 * 
	 * Set an empty value to a mandatory instance, which throws an exception
	 */
	function testSetEmptyMandatory(): void {
		$value = UserValue::asMandatory();
		$this->expectException(RuntimeException::class);
		$value->setValue("");
	}

	/**
	 * Test Zero not Empty
	 * 
	 * Set 0 instead of empty string, which is NOT empty.
	 */
	function testSetZeroMandatory(): void {
		$value = UserValue::asMandatory();
		$value->setValue("0");
		$this->assertEquals("0", $value->getValue());
	}

	/**
	 * Test trim
	 * 
	 * Test that values will be trimmed.
	 */
	function testTrim(): void {
		$value = UserValue::asMandatory();
		$value->setValue("Jonas Wagner ");
		$this->assertEquals("Jonas Wagner", $value->getValue());
	}

	/**
	 * Test no Trim
	 * 
	 * Test that values will not be trimmed if notrim is used.
	 */
	function testNoTrim(): void {
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
	function testValidateValidDate(): void {
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
	function testValidateInvalidDate(): void {
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
	function testValidateDateOptional(): void {
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
	function testConvert(): void {
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
	function testConvertOptional(): void {
		$value = UserValue::asOptional();
		$value->setValidate(new ValidateDate(ValidateDate::ISO));
		$value->setConvert(new ConvertDate(ConvertDate::ISO, ConvertDate::GERMAN));
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}
	
	function testSetDefaultNoSetValueCalled(): void {
		$value = UserValue::asOptional();
		$value->setDefault("02:00:00");
		$this->assertEquals("02:00:00", $value->getValue());
	}

	function testSetDefaultWithSetValue(): void {
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
	function testSetDefaultMandatory(): void {
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
	function testSetDefaultEmptyMandatory(): void {
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
	function testSetDefaultEmptyOptional(): void {
		$value = UserValue::asOptional();
		$value->setDefault("02:00:00");
		$value->setValue("");
		$this->assertEquals("", $value->getValue());
	}

	function testSetEmptyDefaultForbidden(): void {
		$value = UserValue::asMandatory();
		$this->expectException(MandatoryException::class);
		$value->setDefault("");
		$value->getValue();
	}
	
	function testSetEmptyDefaultAllowed(): void {
		$value = UserValue::asMandatory();
		$value->setDefault("", TRUE);
		$this->expectException(MandatoryException::class);
		$value->getValue();
	}

}