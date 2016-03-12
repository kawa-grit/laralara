<?php

namespace App\Util;


class XGIDActionException extends \Exception {}

class XGIDAction {

	const CUBE_ACTION = 'D';
	const CUBE_ACTION_VALUE = 'DD';

	const NO_ACTION = '0';
	const NO_ACTION_VALUE = '00';

	private static $_DiceValue = [
		1, 2, 3, 4, 5, 6,
	];

	public static function noAction() {
		return new XGIDAction(self::NO_ACTION_VALUE);
	}

	public static function cubeAction() {
		return new XGIDAction(self::CUBE_ACTION_VALUE);
	}

	public $value;

	public $firstDice;
	public $secondDice;

	public function __construct($xgidAction) {
		$this->value = $xgidAction;

		// validate
		if (strlen($this->value) !== 2) {
			throw new XGIDActionException('1');
		}
		if ($xgidAction === self::CUBE_ACTION_VALUE) {
			$this->firstDice = self::CUBE_ACTION;
			$this->secondDice = self::CUBE_ACTION;
		} elseif ($xgidAction === self::NO_ACTION_VALUE) {
			$this->firstDice = self::NO_ACTION;
			$this->secondDice = self::NO_ACTION;
		} elseif (preg_match('/^([1-6])([1-6])$/', $xgidAction, $matches)) {
			$this->firstDice = $matches[1];
			$this->secondDice = $matches[2];
		} else {
			throw new XGIDActionException('2:'.$xgidAction);
		}
	}

	public function __toString() {
		return $this->value;
	}

	public function isCube() {
		return $this->value === self::CUBE_ACTION_VALUE;
	}

	public function isNoAction() {
		return $this->value === self::NO_ACTION_VALUE;
	}

	public function isDice() {
		return !$this->isCube() && !$this->isNoAction();
	}
}
