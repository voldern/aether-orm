<?php
/**
 *
 *
 * @package Database
 * @author Espen Volden
 */
class AetherDatabaseExpression {

	protected $expression;

	public function __construct($expression) {
		$this->expression = $expression;
	}

	public function __toString() {
		return (string)$this->expression;
	}

}