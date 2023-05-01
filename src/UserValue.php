<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * User Value
 * 
 * Class to represent a scalar value which comes from any untrusted source, such
 * as configuration files, CLI parameters, CLI input or of course $_GET/$_POST.
 * 
 * plibv4/import and plibv4/Argv currently have similar solutions, but UserValue
 * is to replace them.
 */
class UserValue {
	private $value = NULL;
	private $mandatory;
	private $validate;
	private $convert;
	private $trim = TRUE;
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
	 * @return \UserValue
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
	 * @return \UserValue
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
	function noTrim() {
		$this->trim = FALSE;
	}
	/**
	 * PHP has some sick sh... going on when casting types, so let's be extra
	 * safe here and make clear what is empty in the scope of this class.
	 * As it is intended to be used only on strings or null, it will throw a
	 * RuntimeException when used on any other type.
	 * @param type $value
	 * @return boolean
	 */
	static function isEmpty($value) {
		if(!is_string($value) and !is_null($value)) {
			throw new RuntimeException("This method is intended to evaluate string or null types only, not ". gettype($value));
		}
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
	private function testMandatory($value) {
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
	function setValidate(Validate $validate) {
		$this->validate = $validate;
	}
	
	/**
	 * Set Convert
	 * 
	 * Set an implementation of Convert which will be applied to a user value.
	 * @param Convert $convert
	 */
	function setConvert(Convert $convert) {
		$this->convert = $convert;
	}
	
	/**
	 * Set value
	 * 
	 * Set the user input, which will apply mandatory and validate checks, if
	 * available. Note that if you want to have a default value for optional
	 * values, you'll have to use setUserInput too - what applies to the user
	 * applies to the programmer as well.
	 * @param string $value
	 * @return type
	 */
	function setValue(string $value) {
		if($this->trim==true) {
			$value = trim($value);
		}
		$this->testMandatory($value);
		if($value==="") {
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
		$this->testMandatory($this->value);
		if($this->value===NULL) {
			return "";
		}
	return $this->value;
	}
	
	function isMandatory(): bool {
		return $this->mandatory;
	}
}