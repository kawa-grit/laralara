<?php

namespace App\Util;


class CheckerInfoException extends \Exception {}

class CheckerInfo {

	const PLAYER_MAX = 15;

	const POSITION_COUNT = 26;

	private static $_BarIndex = array(
		XGID::PLAYER_X => 25,
		XGID::PLAYER_O => 0,
	);

	private $checkerValue;
	private $pointArray;

	public function __construct($checkerValue) {
		$this->checkerValue = $checkerValue;
		$this->pointArray = array();
		$this->validate($checkerValue);
	}

	public function point($i) {
		if ($i < 0 || $i > 25) {
			throw new CheckerInfoException(__METHOD__ . '不正インデックス:' . $i);
		}
		return $this->pointArray[$i];
	}

	public function barPoint($playerType) {
		return $this->point(self::$_BarIndex[$playerType]);
	}

	public function bearoffCount() {
		$playerOffCount = array(
			XGID::PLAYER_X => self::PLAYER_MAX,
			XGID::PLAYER_O => self::PLAYER_MAX,
		);
		for ($i = 0; $i < self::POSITION_COUNT; $i++) {
			$point = $this->point($i);
			if ($point->count > 0) {
				$playerOffCount[$point->playerType] -= $point->count;
			}
		}
		return $playerOffCount;
	}

	public function reverse() {
		$this->pointArray = array_reverse($this->pointArray);
		for ($i = 0; $i < count($tmppointArray); $i++) {
			$this->pointArray[$i]->reverse($i);
		}
		return $this;
	}

	public function __toString() {
		return $this->checkerValue();
	}
	
	public function checkerValue() {
		$checkerValue = '';
		foreach ($this->pointArray as $point) {
			$checkerValue .= $point->point;
		}
		return $checkerValue;
	}

	public function pipCount() {
		$pipCount = array(XGID::PLAYER_X => 0, XGID::PLAYER_O => 0);
		foreach ($this->pointArray as $point) {
			if ($point->count > 0) {
				$pipCount[$point->playerType] += $point->pip();
			}
		}
		return $pipCount;
	}

	private function validate($checkerValue) {
		$checkerValueArray = str_split($checkerValue);
		// 文字数チェック
		if (count($checkerValueArray) !== self::POSITION_COUNT) {
			throw new CheckerInfoException(
				sprintf(
					'%s:%d:%d',
					$checkerValue,
					count($checkerValueArray),
					self::POSITION_COUNT));
		}
		// チェッカー枚数カウント
		$checkerCountSum = array(XGID::PLAYER_X => 0, XGID::PLAYER_O => 0);
		foreach ($checkerValueArray as $i => $checkerItem) {
			$point = new CheckerPoint($i, $checkerItem);
			if ($point->count > 0) {
				$checkerCountSum[$point->playerType] += $point->count;
			}
			$this->pointArray[] = $point;
		}
		// チェッカー枚数チェック
		if ($checkerCountSum[XGID::PLAYER_X] > self::PLAYER_MAX
			|| $checkerCountSum[XGID::PLAYER_O] > self::PLAYER_MAX) {
			throw new CheckerInfoException('3');
		}
		// バーインデックスチェック
		foreach (self::$_BarIndex as $playerType => $barIndex) {
			$barPoint = $this->point($barIndex);
			if ($barPoint->count > 0 && $barPoint->playerType !== $playerType) {
				throw new CheckerInfoException('4');
			}
		}
	}
}
