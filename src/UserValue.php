<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */
namespace plibv4\uservalue;
use plibv4\validate\Validate;
use plibv4\convert\Convert;
use RuntimeException;
/**
 * User Value
 * 
 * Class to represent a scalar value which comes from any untrusted source, such
 * as configuration files, CLI parameters, CLI input or of course $_GET/$_POST.
 * 
 * A note about how null is used: There is a difference between a value which is
 * used, but has an empty string as opposed to a value which is not used at all.
 * Eg compare --name="lucy", --name='' ("") or, well, no --name (null).
 * Libraries using UserValue should make use of "" and null accordingly.
 */
final class UserValue {
	private ?string $value = NULL;
	private bool $mandatory = false;
	private ?Validate $validate = null;
	private ?Convert $convert = null;
	private bool $trim = TRUE;
	private ?string $default = NULL;
	/**
	 * Determines if empty values are allowed or not.
	 * @param bool $mandatory
	 */
	private function __construct(bool $mandatory) {
		$this->mandatory = $mandatory;
	}
	
	/**
	 * As Mandatory
	 * 
	 * Creates a mandatory instance of UserValue, ie it won't accept empty
	 * values.
	 * 
	 * @return UserValue
	 */
	static function asMandatory(): UserValue {
		$value = new UserValue(TRUE);
	return $value;
	}
	
	/**
	 * As Optional
	 * 
	 * Creates an instance of UserValue as optional instance, ie it will accept
	 * no value or an empty value (only "" counts as empty value)
	 * @return UserValue
	 */
	static function asOptional(): UserValue {
		$value = new UserValue(FALSE);
	return $value;
	}
	
	/**
	 * No trim
	 * 
	 * Doesn't trim values. Usually, it is advisable to trim() user values.
	 */
	function noTrim(): void {
		$this->trim = FALSE;
	}
	/**
	 * PHP has some sick sh... going on when casting types, so let's be extra
	 * safe here and make clear what is empty in the scope of this class.
	 * As it is intended to be used only on strings or null, it will throw a
	 * RuntimeException when used on any other type.
	 * @param string $value
	 * @return boolean
	 */
	static function isEmpty(?string $value) {
		if($value!==NULL and $value!=="") {
			return FALSE;
		}
	return TRUE;
	}
	
	/**
	 * testMandatory
	 * 
	 * Test if a value is empty or not and mandatory or not and throws a
	 * RuntimeException if a mandatory value is empty. Note that only empty
	 * strings are considered to be empty.
	 * @param string $value
	 * @throws RuntimeException
	 */
	private function testMandatory(?string $value): void {
		if($this->isMandatory() && self::isEmpty($value)) {
			throw new MandatoryException("value is mandatory");
		}
	}
	
	/**
	 * Set Validate
	 * 
	 * Set an implementation of Validate against which a string will be checked.
	 * @param Validate $validate
	 */
	function setValidate(Validate $validate): void {
		$this->validate = $validate;
	}
	
	/**
	 * Set Convert
	 * 
	 * Set an implementation of Convert which will be applied to a user value.
	 * @param Convert $convert
	 */
	function setConvert(Convert $convert): void {
		$this->convert = $convert;
	}
	
	/**
	 * Set Default
	 * 
	 * Set a default value which will be returned if setValue is not called.
	 * $allowEmpty allows empty values on mandatory values. While this may seem
	 * counterintuitive, consider the case that you use setDefault to set the
	 * existing value of a database table to a form, which was not mandatory
	 * before and therefore can be empty.
	 * Note that default values won't be converted or validated; consider the
	 * case that you set a default from a database (2023-05-01) but allow the
	 * user to enter localized dates (01.05.2023 or 05/01/2023). Yours is the
	 * 'true' format, and his will be converted to yours.
	 * @param string $default
	 * @param bool $allowEmpty
	 */
	public function setDefault(string $default, bool $allowEmpty=FALSE): void {
		if($allowEmpty == FALSE) {
			$this->testMandatory($default);
		}
		$this->default = $default;
	}
	
	/**
	 * Set Value
	 * 
	 * Set the user input, which will apply mandatory and validate checks, if
	 * available.
	 * Empty user input will be accepted on optional values, and will take
	 * precedence over default values.
	 * @param string $value
	 * @return void
	 */
	function setValue(string $value): void {
		if($this->trim==true) {
			$value = trim($value);
		}
		$this->testMandatory($value);
		if($value==="") {
			$this->value = "";
			return;
		}
		if($this->validate) {
			$this->validate->validate($value);
		}
		if($this->convert) {
			$value = $this->convert->convert($value);
		}
		$this->value = $value;
	}
	
	/**
	 * Get value
	 * 
	 * Returns the validated and converted user input (if applicable). Another
	 * check is done here to test if the mandatory constraint is satisfied; if
	 * an instance is mandatory, and setUserInput was never called, an Exception
	 * will be thrown.
	 * @return string
	 */
	function getValue(): string {
		/*
		 * Easiest case: setValue has never been called and there is a non-empty
		 * default value. Return default value. 
		 */
		if($this->value===NULL && !self::isEmpty($this->default)) {
			/**
			 * Catch 'impossible' null value. It can't be default here, but
			 * Psalm can't analyze it.
			 */
			return $this->default ?? "" ;
		}
		$this->testMandatory($this->value);
		// Coalesce possible null into empty string.
		$coalesced = $this->value ?? "";
	return $coalesced;
	}
	
	function isMandatory(): bool {
		return $this->mandatory;
	}
}