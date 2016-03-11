<?php

namespace App\Util;


abstract class ActionInfo {

	const ILLEGAL = 'Illegal';

	public $actionValue;

	public $originalActionValue;

	public static function create($actionValue) {
		if (ActionCube::match($actionValue)) {
			return new ActionCube($actionValue);
		} else {
			return new ActionDice($actionValue);
		}
	}

	public function __construct($actionValue) {
		$this->actionValue = $actionValue;
		// TODO ここから特殊＋DB使ってる…。
		/*
		if (Utils::startsWith($actionValue, self::ILLEGAL)) {
			$this->originalActionValue = $actionValue;
			$illegalModel = IllegalModel::get($actionValue);
			if ($illegalModel !== false) {
				$this->actionValue = $illegalModel['move_value'];
			}
		}
		*/
	}

	abstract public function execute($xgid);

	// TODO 以下なんだろ？
	const MOVE = 'moveAction';
	const CUBE_BEFORE = 'cubeBeforeAction';
	const CUBE_AFTER = 'cubeAfterAction';

	public static $_ActionType = array(
		self::MOVE,
		self::CUBE_BEFORE,
		self::CUBE_AFTER,
	);
}
