<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Mandatory exception
 * 
 * Exception to distinguish from ValidateException or any exception a complex
 * use of UserValue may throw.
 */
final class MandatoryException extends RuntimeException {

}
