<?php

namespace App\Util;


class CheckerPointException extends \Exception {}

class CheckerPoint {

	const NONE = '-';

	private static $_CheckerCount = [
		self::NONE => 0,
		'A' => 1,  'a' => 1,
		'B' => 2,  'b' => 2,
		'C' => 3,  'c' => 3,
		'D' => 4,  'd' => 4,
		'E' => 5,  'e' => 5,
		'F' => 6,  'f' => 6,
		'G' => 7,  'g' => 7,
		'H' => 8,  'h' => 8,
		'I' => 9,  'i' => 9,
		'J' => 10, 'j' => 10,
		'K' => 11, 'k' => 11,
		'L' => 12, 'l' => 12,
		'M' => 13, 'm' => 13,
		'N' => 14, 'n' => 14,
		'O' => 15, 'o' => 15,
	];

	private static $_CheckerType = [
		'A' => XGID::PLAYER_X, 'a' => XGID::PLAYER_O,
		'B' => XGID::PLAYER_X, 'b' => XGID::PLAYER_O,
		'C' => XGID::PLAYER_X, 'c' => XGID::PLAYER_O,
		'D' => XGID::PLAYER_X, 'd' => XGID::PLAYER_O,
		'E' => XGID::PLAYER_X, 'e' => XGID::PLAYER_O,
		'F' => XGID::PLAYER_X, 'f' => XGID::PLAYER_O,
		'G' => XGID::PLAYER_X, 'g' => XGID::PLAYER_O,
		'H' => XGID::PLAYER_X, 'h' => XGID::PLAYER_O,
		'I' => XGID::PLAYER_X, 'i' => XGID::PLAYER_O,
		'J' => XGID::PLAYER_X, 'j' => XGID::PLAYER_O,
		'K' => XGID::PLAYER_X, 'k' => XGID::PLAYER_O,
		'L' => XGID::PLAYER_X, 'l' => XGID::PLAYER_O,
		'M' => XGID::PLAYER_X, 'm' => XGID::PLAYER_O,
		'N' => XGID::PLAYER_X, 'n' => XGID::PLAYER_O,
		'O' => XGID::PLAYER_X, 'o' => XGID::PLAYER_O,
	];

	private static $_OffIndex = [
		XGID::OWNER_X => 0,
		XGID::OWNER_O => 25,
	];

	private static $_CheckerCountAsPlayer = [
		XGID::PLAYER_X => [
			0 => self::NONE, 8  => 'H',
			1 => 'A',        9  => 'I',
			2 => 'B',        10 => 'J',
			3 => 'C',        11 => 'K',
			4 => 'D',        12 => 'L',
			5 => 'E',        13 => 'M',
			6 => 'F',        14 => 'N',
			7 => 'G',        15 => 'O',
		],
		XGID::PLAYER_O => [
			0 => self::NONE, 8  => 'h',
			1 => 'a',        9  => 'i',
			2 => 'b',        10 => 'j',
			3 => 'c',        11 => 'k',
			4 => 'd',        12 => 'l',
			5 => 'e',        13 => 'm',
			6 => 'f',        14 => 'n',
			7 => 'g',        15 => 'o',
		],
	];

	private static $_PipCountMaster = [
		XGID::PLAYER_X => [
			 0 =>  0, 13 => 13,
			 1 =>  1, 14 => 14,
			 2 =>  2, 15 => 15,
			 3 =>  3, 16 => 16,
			 4 =>  4, 17 => 17,
			 5 =>  5, 18 => 18,
			 6 =>  6, 19 => 19,
			 7 =>  7, 20 => 20,
			 8 =>  8, 21 => 21,
			 9 =>  9, 22 => 22,
			10 => 10, 23 => 23,
			11 => 11, 24 => 24,
			12 => 12, 25 => 25,
		],
		XGID::PLAYER_O => [
			 0 => 25, 13 => 12,
			 1 => 24, 14 => 11,
			 2 => 23, 15 => 10,
			 3 => 22, 16 =>  9,
			 4 => 21, 17 =>  8,
			 5 => 20, 18 =>  7,
			 6 => 19, 19 =>  6,
			 7 => 18, 20 =>  5,
			 8 => 17, 21 =>  4,
			 9 => 16, 22 =>  3,
			10 => 15, 23 =>  2,
			11 => 14, 24 =>  1,
			12 => 13, 25 =>  0,
		],
	];

	private static $_ReverseValue = [
		'-' => '-',
		'a' => 'A', 'A' => 'a',
		'b' => 'B', 'B' => 'b',
		'c' => 'C', 'C' => 'c',
		'd' => 'D', 'D' => 'd',
		'e' => 'E', 'E' => 'e',
		'f' => 'F', 'F' => 'f',
		'g' => 'G', 'G' => 'g',
		'h' => 'H', 'H' => 'h',
		'i' => 'I', 'I' => 'i',
		'j' => 'J', 'J' => 'j',
		'k' => 'K', 'K' => 'k',
		'l' => 'L', 'L' => 'l',
		'm' => 'M', 'M' => 'm',
		'n' => 'N', 'N' => 'n',
		'o' => 'O', 'O' => 'o',
	];

	public $index;
	public $point;

	public $count = 0;

	public $playerType = FALSE;
	public $ownDiv = FALSE;

	public function __construct($index, $pointValue) {
		$this->index = $index;
		$this->point = $pointValue;
		if (array_key_exists($pointValue, self::$_CheckerCount)) {
			$this->count = self::$_CheckerCount[$pointValue];
		} else {
			throw new CheckerPointException(__METHOD__ . '[CC]不正チェッカー値' . $this);
		}
		if (array_key_exists($pointValue, self::$_CheckerType)) {
			$this->playerType = self::$_CheckerType[$pointValue];
			$this->ownDiv = XGID::$_Owner[$this->playerType];
		} else {
			$this->playerType = FALSE;
			$this->ownDiv = FALSE;
		}
	}

	public function moveTo(CheckerPoint $toPoint, $count=1) {
		$this->count -= $count;
		$this->point = self::$_CheckerCountAsPlayer[$this->playerType][$this->count];
		$toPoint->count += $count;
		$toPoint->point = self::$_CheckerCountAsPlayer[$this->playerType][$toPoint->count];
		$toPoint->playerType = $this->playerType;
		$toPoint->ownDiv = $this->ownDiv;
		if ($this->count === 0) {
			$this->playerType = FALSE;
			$this->ownDiv = FALSE;
		}
		return [$this, $toPoint];
	}

	public function reverse($i) {
		$this->index = $i;
		$this->point = self::$_ReverseValue[$this->point];
		if ($this->playerType !== FALSE) {
			$this->playerType = XGID::$_OpponentPlayerType[$this->playerType];
			$this->ownDiv = XGID::$_Owner[$this->playerType];
		}
		return $this;
	}

	public function pip() {
		return $this->count * self::$_PipCountMaster[$this->playerType][$this->index];
	}

	public function bearoff($ownDiv) {
		return self::$_OffIndex[$ownDiv] === $this->index;
	}

	public function __toString() {
		return sprintf('[%s=>%s][%s=>%s](%s)', $this->index, $this->point, $this->playerType, $this->ownDiv, $this->count);
	}
}
