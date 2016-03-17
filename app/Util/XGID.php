<?php

namespace App\Util;


class XGIDException extends \Exception {}

class XGID {
	const INIT = '-b----E-C---eE---c-e----B-:0:0:1:00:0:0:0:0:10';

	const DELIM = ':';

	const PLAYER_X = 'X';
	const PLAYER_O = 'O';

	private static $_ValidCubeValue = [0, 1, 2, 3, 4];

	const UNLIMITED = 0;

	const NO_RULE = 0;
	const CRAWFORD = 1;

	private static $_PointMatchRule = [
		self::NO_RULE,
		self::CRAWFORD,
	];

	const JACOBY = 1;
	const BEAVER = 2;
	const JACOBY_BEAVER = 3;

	private static $_UnlimitedMatchRule = [
		self::NO_RULE,
		self::JACOBY,
		self::BEAVER,
		self::JACOBY_BEAVER,
	];

	const NO_OWNER = 0;
	const OWNER_X = 1;
	const OWNER_O = -1;

	private static $_PlayerType = [
		self::OWNER_X => self::PLAYER_X,
		self::OWNER_O => self::PLAYER_O,
	];

	public static $_OpponentPlayerType = [
		self::PLAYER_X => self::PLAYER_O,
		self::PLAYER_O => self::PLAYER_X,
	];

	public static $_Owner = [
		self::PLAYER_X => self::OWNER_X,
		self::PLAYER_O => self::OWNER_O,
	];

	public static $_ReverseOwner = [
		self::NO_OWNER => self::NO_OWNER,
		self::OWNER_X => self::OWNER_O,
		self::OWNER_O => self::OWNER_X,
	];

	const PREFIX = 'XGID=';

	private static function trim($xgid) {
		foreach ([self::PREFIX] as $word) {
			if (Utils::startsWithIgnoreCase($xgid, $word)) {
				$xgid = substr($xgid, strlen($word));
			}
		}
		return $xgid;
	}

	private static function validate($value) {
		$xgid = self::trim($value);

		$xgidArray = explode(self::DELIM, $xgid);

		// 数チェック
		if (count($xgidArray) !== 10) {
			throw new XGIDException('0');
		}
		// チェッカーチェック
		$checker = new CheckerInfo($xgidArray[0]);

		// キューブ値チェック
		if (!in_array($xgidArray[1], self::$_ValidCubeValue)) {
			throw new XGIDException('2');
		}
		// キューブ主チェック
		if (!in_array($xgidArray[2], [self::NO_OWNER, self::OWNER_X, self::OWNER_O])) {
			throw new XGIDException('3');
		}
		// アクション主チェック
		if (!in_array($xgidArray[3], [self::OWNER_X, self::OWNER_O])) {
			throw new XGIDException('4');
		}
		// アクションとの相関チェック
		$action = new XGIDAction($xgidArray[4]);
		if ($action->isCube()) {
			// キューブ値が未指定か判定
			if ($xgidArray[1] == 0) {
				throw new XGIDException('5');
			}
			// キューブ主が未指定か判定
			if ($xgidArray[2] == self::NO_OWNER) {
				throw new XGIDException('6');
			}
		} elseif ($action->isDice()) {
			// キューブ値があり、キューブ主が未指定か判定
			if ($xgidArray[1] > 0 && $xgidArray[2] == self::NO_OWNER) {
				throw new XGIDException('7');
			}
		}
		// ポイント手前
		if (!is_numeric($xgidArray[5]) || $xgidArray[5] < 0) {
			throw new XGIDException('9');
		}
		// ポイント奥
		if (!is_numeric($xgidArray[6]) || $xgidArray[6] < 0) {
			throw new XGIDException('10');
		}
		// マッチポイント
		if (!is_numeric($xgidArray[8]) || $xgidArray[8] < 0) {
			throw new XGIDException('11');
		}
		// ルールチェック
		if ($isPointMatch = $xgidArray[8] > 0) {
			// ポイントマッチ
			if (!in_array($xgidArray[7], self::$_PointMatchRule)) {
				throw new XGIDException('12');
			}
		} else {
			// アンリミテッド
			if (!in_array($xgidArray[7], self::$_UnlimitedMatchRule)) {
				throw new XGIDException('13');
			}
		}
		// 最大キューブ
		if (!is_numeric($xgidArray[9]) || $xgidArray[9] < 0) {
			throw new XGIDException('14');
		}
		return $xgidArray;
	}

	public $checker;
	public $cube;
	public $cubeOwn;
	public $actionOwn;
	public $action;
	public $pointX;
	public $pointO;
	/**
	 * ポイントマッチ
	 * 　0：通常時
	 * 　1：クロフォード中
	 * アンリミテッド
	 * 　0：NoJacoby, NoBeaver
	 * 　1：Jacoby, NoBeaver
	 * 　2：NoJacoby, Beaver
	 * 　3：Jacoby, Beaver
	 */
	public $rule;
	public $matchPoint;
	public $maxCube;

	public function __construct($xgid=XGID::INIT) {
		list($checker, $this->cube, $this->cubeOwn, $this->actionOwn, $action, $this->pointX, $this->pointO, $this->rule, $this->matchPoint, $this->maxCube)
			= self::validate($xgid);
		$this->checker = new CheckerInfo($checker);
		$this->action = new XGIDAction($action);
	}

	public function isPointMatch() {
		return !$this->isUnlimitedMatch();
	}

	public function isUnlimitedMatch() {
		return $this->matchPoint == self::UNLIMITED;
	}

	public function isCrawford() {
		return $this->isPointMatch() && $this->rule == self::CRAWFORD;
	}

	public function isCrawfordPoint() {
		return $this->matchPoint == ($this->pointX + 1)
			|| $this->matchPoint == ($this->pointO + 1);
	}

	public function isJacoby() {
		return $this->isUnlimitedMatch() && in_array($this->rule, [self::JACOBY, self::JACOBY_BEAVER]);
	}

	public function isBeaver() {
		return $this->isUnlimitedMatch() && in_array($this->rule, [self::BEAVER, self::JACOBY_BEAVER]);
	}

	public function pipX() {
		$pipCount = $this->checker->pipCount();
		return $pipCount[self::PLAYER_X];
	}

	public function pipO() {
		$pipCount = $this->checker->pipCount();
		return $pipCount[self::PLAYER_O];
	}

	public function bearoffX() {
		$bearoffCount = $this->checker->bearoffCount();
		return $bearoffCount[self::PLAYER_X];
	}

	public function bearoffO() {
		$bearoffCount = $this->checker->bearoffCount();
		return $bearoffCount[self::PLAYER_O];
	}

	public function __toString() {
		return $this->xgidValue();
	}

	public function xgidValue() {
		return implode(self::DELIM, [
			$this->checker->checkerValue(),
			$this->cube,
			$this->cubeOwn,
			$this->actionOwn,
			$this->action->value,
			$this->pointX,
			$this->pointO,
			$this->rule,
			$this->matchPoint,
			$this->maxCube,
		]);
	}

	public function nextTurn() {
		$this->actionOwn = self::$_ReverseOwner[$this->actionOwn];
		$this->action = XGIDAction::noAction();
		return $this;
	}

	public function setTurn($playerType) {
		if (array_key_exists($playerType, self::$_Owner)) {
			$this->actionOwn = self::$_Owner[$playerType];
		} else {
			throw new XGIDException(__METHOD__ . 'プレイヤー区分不正' . $playerType);
		}
		return $this;
	}

	public function setAction($action1, $action2) {
		$this->action = new XGIDAction($action1 . $action2);
		return $this;
	}

	public function isActionPlayerType($playerType) {
		return strcmp($playerType, $this->actionPlayerType()) === 0;
	}

	public function actionPlayerType() {
		return self::$_PlayerType[$this->actionOwn];
	}

	/**
	 * @param $actionInfo MoveActionInfo
	 */
	public function executeAction($actionValue) {
		ActionInfo::create($actionValue)->execute($this);
		return $this;
	}

	public function reverseXO() {
		// チェッカー反転
		$this->checker->reverse();
		// 主反転
		$this->cubeOwn = self::$_ReverseOwner[$this->cubeOwn];
		$this->actionOwn = self::$_ReverseOwner[$this->actionOwn];
		// スコア反転
		list($this->pointX, $this->pointO) = [$this->pointO, $this->pointX];

		return $this;
	}

	public function createPositionImage(
		$xgidPrevious = null,
		$playerColor = null,
		$offPosition = PositionImage::LEFT,
		$logoType = null,
		$boardType = PositionImage::BOARD_NORMAL) {

		// プレイヤー色未指定判定
		if (!isset($playerColor)) {
			$playerColor = PositionImage::$_PlayerColorBlackWhite;
		}
		// アクション主プレイヤー区分
		$actionPlayerType = $this->actionPlayerType();
		// アクション主プレイヤー区分（ボード用）
		if (PositionImage::$_BoardType[$boardType]) {
			// プレイヤー色で決定（イレギュラー対応 TODO ザックリ仕様）
			foreach ($playerColor as $key => $value) {
				if ($value == PositionImage::BLACK) {
					$boardActionPlayerType = $key;
					break;
				}
			}
		} else {
			// 通常ケース
			$boardActionPlayerType = $actionPlayerType;
		}
		// ポジション画像生成
		$positionImage = PositionImage::create($playerColor, $offPosition);
		// ボード設定
		$positionImage
			->drawBoard($boardActionPlayerType, $boardType)
			->drawChecker($this->checker, $xgidPrevious)
			->drawDice($this->action, $actionPlayerType, $xgidPrevious)
			->drawCube($this->action, $this->cube, $this->cubeOwn, $this->actionOwn, $xgidPrevious)
			->drawLogo($logoType, $actionPlayerType);
		return $positionImage->pngData;
	}

	public function createPositionImageDB($xgidPrevious = null) {
		return $this->createPositionImage($xgidPrevious, null, PositionImage::LEFT, PositionImage::BGTV_LOGO);
	}
    
}
