<?php

namespace App\Util;

class ActionCubeException extends \Exception {}

class ActionCube extends ActionInfo {

	const DOUBLES = 'Doubles';
	const TAKES = 'Takes';
	const DROPS = 'Drops';

	public static function match($actionValue) {
		return strcasecmp($actionValue, self::DOUBLES) === 0 or
			strcasecmp($actionValue, self::TAKES) === 0 or
			strcasecmp($actionValue, self::DROPS) === 0;
	}

	public function execute($xgid) {
		// ダブルを仕掛けたか判定
		if ($this->isCubeActionDouble()) {
			// キューブを1UP
			$xgid->cube += 1;
			// キューブオーナーをアクションオーナーに
			$xgid->cubeOwn = $xgid->actionOwn;
			// TODO 独自XGID仕様
			$xgid->action = XGIDAction::cubeAction();
		} elseif ($this->isCubeActionTake()) {
			// キューブオーナーを自分に
			$xgid->cubeOwn = $xgid->actionOwn;
			// TODO 独自XGID仕様
			$xgid->action = XGIDAction::noAction();
		} elseif ($this->isCubeActionDrop()) {
			// キューブ値をリセット TODO 独自仕様
			$xgid->cube = 0;
			// キューブオーナーをフリーに
			$xgid->cubeOwn = XGID::NO_OWNER;
			// TODO 独自XGID仕様
			$xgid->action = XGIDAction::noAction();
		} else {
			throw new ActionCubeException();
		}
		return $xgid;
	}

	public function isCubeActionDouble() {
		return strcasecmp($this->actionValue, self::DOUBLES) === 0;
	}

	public function isCubeActionTake() {
		return strcasecmp($this->actionValue, self::TAKES) === 0;
	}

	public function isCubeActionDrop() {
		return strcasecmp($this->actionValue, self::DROPS) === 0;
	}
}
