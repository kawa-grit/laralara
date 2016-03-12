<?php

namespace App\Util;


class CheckerMoveException extends \Exception {}

// "4/6(2)"や"13/1*"等 1ターン内の1挙動を管理
class CheckerMove {

	const ILLEGAL = '[I]';

	private static $_ReverseIndex = [
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
	];

	public $moveValue;

	public $fromIndex;
	public $toIndex = [];

	public $hit = FALSE;
	public $illegal = FALSE;

	public $count = 1;

	public function __construct($moveValue) {
		$this->moveValue = $moveValue;

		// イリーガル判定
		if (Utils::startsWithIgnoreCase($moveValue, self::ILLEGAL)) {
			$moveValue = str_replace(self::ILLEGAL, '', $moveValue); // TODO チェック！
			$this->illegal = TRUE;
		}
		// ヒット判定
		if (strstr($moveValue, '*') !== FALSE) {
			$this->hit = TRUE;
			$moveValue = str_replace('*', '', $moveValue);
		}
		// 移動文字と枚数に分割
		$moveValueArray = Utils::trimArray(explode(' ', str_replace(['(', ')'], ' ', $moveValue)));
		// 枚数指定をチェック
		if (count($moveValueArray) == 2) {
			$moveValue = $moveValueArray[0];
			$this->count = $moveValueArray[1];
		} elseif (count($moveValueArray) > 2) {
			throw new CheckerMoveException(__METHOD__ . 'カウント取得時分解数不正' . print_r($moveValueArray, TRUE));
		}
		$moveValueArray = explode('/', $moveValue);
		if (count($moveValueArray) < 2) {
			throw new CheckerMoveException(__METHOD__ . 'インデックス取得時分解数不正' . print_r($moveValueArray, TRUE));
		}
		// 1番めはFromインデックス
		$this->_setFromIndex(array_shift($moveValueArray));
		foreach ($moveValueArray as $toIndex) {
			// 2番め以降はToインデックス
			$this->_addToIndex($toIndex);
		}
	}

	private function _reverse() {
		// 移動元
		$this->fromIndex = self::$_ReverseIndex[$this->fromIndex];
		// 移動先
		foreach ($this->toIndex as $i => $index) {
			$this->toIndex[$i] = self::$_ReverseIndex[$index];
		}
	}

	private function _setFromIndex($fromIndex) {
		if (strcasecmp($fromIndex, 'bar') === 0) {
			return $this->_setFromIndex(25);
		} elseif (is_numeric($fromIndex)) {
			if ($fromIndex < 0 || $fromIndex > 25) {
				throw new CheckerMoveException(__METHOD__ . '開始値範囲不正' . $fromIndex);
			}
			$this->fromIndex = intval($fromIndex);
			return $this;
		} else {
			throw new CheckerMoveException(__METHOD__ . '開始値不正' . $fromIndex);
		}
	}

	private function _addToIndex($toIndex) {
		if (strcasecmp($toIndex, 'off') === 0) {
			return $this->_addToIndex(0);
		} elseif (is_numeric($toIndex)) {
			if ($toIndex < 0 || $toIndex > 25) {
				throw new CheckerMoveException(__METHOD__ . '終了値範囲不正' . $toIndex);
			}
			// イリーガルであれば大小関係はノーチェックＯＫ
			if ($this->illegal || $this->fromIndex > $toIndex) {
				$this->toIndex[] = intval($toIndex);
				return $this;
			} else {
				throw new CheckerMoveException(__METHOD__ . '終了値開始値逆転不正' . $toIndex);
			}
		} else {
			throw new CheckerMoveException(__METHOD__ . '終了値不正' . $toIndex);
		}
	}

	public function execute($xgid) {
		// プレイヤータイプが相手だったらインデックスを入れ替える
		if ($xgid->isActionPlayerType(XGID::PLAYER_O)) {
			$this->_reverse();
		}
		// 移動元インデックスチェック
		$fromPoint = $xgid->checker->point($this->fromIndex);
		// 非自駒判定
		if (!$xgid->isActionPlayerType($fromPoint->playerType)) {
			throw new CheckerInfoMoveException(
				__METHOD__ . '移動元インデックス自駒でない' . $fromPoint . $xgid->actionPlayerType());
		}
		if ($fromPoint->count < $this->count) {
			throw new CheckerInfoMoveException(
				__METHOD__ . '移動元インデックスの駒数不足' . $fromPoint . $this->count);
		}
		// 移動先インデックスチェック
		$hitPoint = [];
		$finalToPoint = NULL;
		foreach ($this->toIndex as $index) {
			$toPoint = $xgid->checker->point($index);
			$finalToPoint = $toPoint;
			if ($toPoint->count === 0 or $xgid->isActionPlayerType($toPoint->playerType) or $toPoint->bearoff($xgid->actionOwn)) {
				continue;
			}
			// 非自駒判定
			if ($toPoint->count !== 1) {
				throw new CheckerInfoMoveException(
					__METHOD__ . '移動先インデックス相手駒でブロックされている' . $toPoint . $xgid->actionOwn);
			}
			// 相手に変更
			$hitPoint[] = $toPoint;
		}
		// 移動処理
		// ヒット相手駒移動
		foreach ($hitPoint as $point) {
			// ヒットのポイントを1ダウン
			// 相手のバーポイントを1アップ
			list($f, $t) = $point->moveTo($xgid->checker->barPoint($point->playerType));
		}
		// 自駒移動
		list($f, $t) = $fromPoint->moveTo($finalToPoint, $this->count);
		return $xgid;
	}
}
