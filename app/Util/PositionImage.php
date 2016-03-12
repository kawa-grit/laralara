<?php

namespace App\Util;


class PositionImageException extends \Exception {}

class PositionImage {

	// シングルトン的な遅延初期化
	private static $_Sozai;

	const MOVE_DIR = 'move/';

	public $pngData;

	public $playerColor;
	public $offPosition;

	public static function create($playerColor, $offPosition) {
		if (!isset(self::$_Sozai)) {
			self::$_Sozai = dirname(__FILE__) . '/images/';
		}
		return new PositionImage($playerColor, $offPosition);
	}

	public function drawChecker($checkerInfo, $xgidPrevious) {
		if (isset($xgidPrevious)) {
			$checkerPreviousInfo = $xgidPrevious->checker;
		} else {
			$checkerPreviousInfo = $checkerInfo;
		}
		$checkerXY = self::$_CheckerXY[$this->offPosition];
		foreach ($checkerXY as $i => $imageXY) {
			$checkerCount = $checkerInfo->point($i)->count;
			$checkerPreviousCount = $checkerPreviousInfo->point($i)->count;
			if ($checkerPreviousCount == 0 && $checkerCount == 0) {
				continue;
			}
			if ($checkerCount > 0) {
				$playerDiv = $checkerInfo->point($i)->playerType;
			} else {
				$playerDiv = $checkerPreviousInfo->point($i)->playerType;
			}
			$color = $this->playerColor[$playerDiv];
			// 画像ディレクトリ
			$baseDir = self::$_Sozai;
			if ($checkerPreviousCount > $checkerCount && $checkerCount < 5) {
				if ($checkerPreviousCount > 5) { // TODO 6より大きいすべての個数からの駒画像を用意すれば分岐不要
					$checkerPreviousCount = 5;
				}
				$checkerCount = $checkerPreviousCount . 'to' . $checkerCount;
				$baseDir = self::$_Sozai . self::MOVE_DIR;
			}
			$imageFile = $imageXY['type'] . $color . $checkerCount . '.png';
			$this->copyImage($baseDir . $imageFile, $imageXY['X'], $imageXY['Y']);
		}
		$playerOffXY = self::$_PlayerOffXY[$this->offPosition];
		foreach ($checkerInfo->bearoffCount() as $offPlayerDiv => $offCheckerCount) {
			if ($offCheckerCount > 0) {
				$offXY = $playerOffXY[$offPlayerDiv];
				$offColor = $this->playerColor[$offPlayerDiv];
				$offImageFile = $offXY['type'] . $offColor . $offCheckerCount . '.png';
				$this->copyImage(self::$_Sozai . $offImageFile, $offXY['X'], $offXY['Y']);
			}
		}
		return $this;
	}

	private function copyImage($imagePartPath, $x, $y) {
		if (!file_exists($imagePartPath)) {
			throw new PositionImageException(__METHOD__ . '画像見つからない' . $imagePartPath);
		}
		$imagePart = imagecreatefrompng($imagePartPath);
		if ($imagePart === FALSE) {
			throw new PositionImageException(__METHOD__ . '画像加工失敗読込' . $imagePartPath);
		}
		$result = imagecopy($this->pngData, $imagePart, $x, $y, 0, 0, imagesx($imagePart), imagesy($imagePart));
		if ($result === FALSE) {
			throw new PositionImageException(__METHOD__ . '画像加工失敗書込');
		}
		imagedestroy($imagePart);
	}

	private $isDrawLogoOpponent = FALSE;

	public function drawDice($xgidAction, $actionPlayerDiv, $xgidPrevious) {
		if ($xgidAction->isDice()) {
			$this->isDrawAction = TRUE;
			$diceXY = self::$_DiceXY[$actionPlayerDiv];
			$diceColor = $this->playerColor[$actionPlayerDiv];
			// 画像ディレクトリ
			$baseDir = isset($xgidPrevious) ? self::$_Sozai . self::MOVE_DIR : self::$_Sozai;
			// １個目ダイス
			$diceImageFile = $diceXY[0]['type'] . $diceColor . $xgidAction->firstDice . '.png';
			$this->copyImage($baseDir . $diceImageFile, $diceXY[0]['X'], $diceXY[0]['Y']);
			// ２個目ダイス
			$diceImageFile = $diceXY[1]['type'] . $diceColor . $xgidAction->secondDice . '.png';
			$this->copyImage($baseDir . $diceImageFile, $diceXY[1]['X'], $diceXY[1]['Y']);
		}
		return $this;
	}

	public function drawCube($xgidAction, $cubeValue, $cubeOwnValue, $actionOwnValue, $xgidPrevious) {
		if ($xgidAction->isCube()) {
			$cubeActionXY = self::$_CubeActionXY[$cubeOwnValue];
			$cubeImageFile = $cubeActionXY['type'] . $cubeValue . '.png';
			$this->copyImage(self::$_Sozai . $cubeImageFile, $cubeActionXY['X'], $cubeActionXY['Y']);
			$this->isDrawLogoOpponent = $cubeOwnValue != $actionOwnValue;
		} else {
			$cubeXY = self::$_CubeXY[$this->offPosition][$cubeOwnValue];
			$cubeImageFile = $cubeXY['type'] . $cubeValue . '.png';
			$this->copyImage(self::$_Sozai . $cubeImageFile, $cubeXY['X'], $cubeXY['Y']);
		}
		if (isset($xgidPrevious)) {
			if ($xgidPrevious->action->isCube()) {
				$cubeActionXY = self::$_CubeActionXY[$xgidPrevious->cubeOwn];
				$cubeImageFile = $cubeActionXY['type'] . $xgidPrevious->cube . '.png';
				$this->copyImage(self::$_Sozai . self::MOVE_DIR . $cubeImageFile, $cubeActionXY['X'], $cubeActionXY['Y']);
				$this->isDrawLogoOpponent = $xgidPrevious->cubeOwn != $xgidPrevious->actionOwn;
			} elseif ($xgidAction->isCube()) {
				$cubeXY = self::$_CubeXY[$this->offPosition][$xgidPrevious->cubeOwn];
				$cubeImageFile = $cubeXY['type'] . $xgidPrevious->cube . '.png';
				$this->copyImage(self::$_Sozai . self::MOVE_DIR . $cubeImageFile, $cubeXY['X'], $cubeXY['Y']);
			}
		}
		return $this;
	}

	public function drawLogo($logoType, $actionPlayerDiv) {
		if (!array_key_exists($logoType, self::$_LogoXY)) {
			return $this;
		}
		if ($this->isDrawLogoOpponent) {
			$opponentPlayerDiv = XGID::$_OpponentPlayerType[$actionPlayerDiv];
			$logoXY = self::$_LogoXY[$logoType][$opponentPlayerDiv];
		} else {
			$logoXY = self::$_LogoXY[$logoType][$actionPlayerDiv];
		}
		$logoImageFile = $logoXY['type'] . '.png';
		$this->copyImage(self::$_Sozai . $logoImageFile, $logoXY['X'], $logoXY['Y']);
		return $this;
	}

	public function drawBoard($actionPlayerDiv, $boardType) {
		$this->pngData = imagecreatefrompng(self::$_Sozai . 'Board' . $actionPlayerDiv . $this->offPosition . $boardType . '.png');
		if ($this->pngData === FALSE) {
			throw new PositionImageException(__METHOD__ . '画像加工失敗');
		}
		return $this;
	}

	private function __construct($playerColor, $offPosition) {
		$this->playerColor = $playerColor;
		$this->offPosition = $offPosition;
	}

	const BLACK = 'Black';
	const WHITE = 'White';

	public static $_PlayerColorBlackWhite = [
		XGID::PLAYER_X => self::BLACK,
		XGID::PLAYER_O => self::WHITE,
	];

	public static $_PlayerColorWhiteBlack = [
		XGID::PLAYER_X => self::WHITE,
		XGID::PLAYER_O => self::BLACK,
	];

	const BOARD_NORMAL = '';
	const BOARD_1792 = '1792';
	const BOARD_Arrow = 'Arrow';

	public static $_BoardType = [
		self::BOARD_NORMAL => FALSE,
		self::BOARD_1792 => FALSE,
		self::BOARD_Arrow => TRUE,
	];

	const LEFT = 'left';
	const RIGHT = 'right';

	const BGTV_LOGO = 'bgtv';
	const BGEXAM_LOGO = 'bgexam';

	public static $_LogoXY = [
		self::BGTV_LOGO => [
			XGID::PLAYER_X => ['X' =>  60, 'Y' => 135, 'type' => 'LogoBack-GammonTV'],
			XGID::PLAYER_O => ['X' => 234, 'Y' => 135, 'type' => 'LogoBack-GammonTV'],
		],
		self::BGEXAM_LOGO => [
			XGID::PLAYER_X => ['X' =>  48, 'Y' => 144, 'type' => 'LogoBackGammonExamCOM'],
			XGID::PLAYER_O => ['X' => 222, 'Y' => 144, 'type' => 'LogoBackGammonExamCOM'],
		],
	];

	private static $_CubeActionXY = [
		XGID::OWNER_X => ['X' => 278, 'Y' => 134, 'type' => 'Cube'],
		XGID::OWNER_O => ['X' =>  94, 'Y' => 134, 'type' => 'Cube'],
	];

	private static $_CubeXY = [
		self::LEFT => [
			XGID::NO_OWNER => ['X' => 368, 'Y' => 136, 'type' => 'Cube'],
			XGID::OWNER_X  => ['X' => 368, 'Y' => 259, 'type' => 'Cube'],
			XGID::OWNER_O  => ['X' => 368, 'Y' =>  14, 'type' => 'Cube'],
		],
		self::RIGHT => [
			XGID::NO_OWNER => ['X' => 2, 'Y' => 136, 'type' => 'Cube'],
			XGID::OWNER_X  => ['X' => 2, 'Y' => 259, 'type' => 'Cube'],
			XGID::OWNER_O  => ['X' => 2, 'Y' =>  14, 'type' => 'Cube'],
		],
	];

	private static $_DiceXY = [
		XGID::PLAYER_X => [
			['X' => 253, 'Y' => 136, 'type' => 'Dice'],
			['X' => 303, 'Y' => 136, 'type' => 'Dice'],
		],
		XGID::PLAYER_O => [
			['X' =>  69, 'Y' => 136, 'type' => 'Dice'],
			['X' => 119, 'Y' => 136, 'type' => 'Dice'],
		],
	];

	private static $_PlayerOffXY = [
		self::LEFT => [
			XGID::PLAYER_X => ['X' =>   4, 'Y' => 164, 'type' => 'OffCheckerBottom'],
			XGID::PLAYER_O => ['X' =>   4, 'Y' =>  14, 'type' => 'OffCheckerTop'],
		],
		self::RIGHT => [
			XGID::PLAYER_X => ['X' => 370, 'Y' => 164, 'type' => 'OffCheckerBottom'],
			XGID::PLAYER_O => ['X' => 370, 'Y' =>  14, 'type' => 'OffCheckerTop'],
		],
	];

	private static $_CheckerXY = [
		// 奥プレイヤーバー ⇒ 左下 ⇒ 右下 ⇒ 右上 ⇒ 左上 ⇒ 手前プレイヤーバー
		self::LEFT => [
			['X' => 187.0, 'Y' => 155.0, 'type' => 'CheckerTop'],
			['X' =>  35.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' =>  60.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' =>  85.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 110.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 135.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 160.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 213.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 238.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 263.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 288.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 313.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 338.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 338.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 313.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 288.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 263.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 238.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 213.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 160.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 135.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 110.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' =>  85.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' =>  60.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' =>  35.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 187.0, 'Y' =>  30.0, 'type' => 'CheckerBottom'],
		],
		// 奥プレイヤーバー ⇒ 右下 ⇒ 左下 ⇒ 左上 ⇒ 右上 ⇒ 手前プレイヤーバー
		self::RIGHT => [
			['X' => 187.0, 'Y' => 155.0, 'type' => 'CheckerTop'],
			['X' => 338.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 313.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 288.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 263.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 238.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 213.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 160.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 135.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' => 110.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' =>  85.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' =>  60.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' =>  35.5, 'Y' => 173.0, 'type' => 'CheckerBottom'],
			['X' =>  35.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' =>  60.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' =>  85.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 110.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 135.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 160.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 213.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 238.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 263.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 288.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 313.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 338.5, 'Y' =>  13.5, 'type' => 'CheckerTop'],
			['X' => 187.0, 'Y' =>  30.0, 'type' => 'CheckerBottom'],
		],
	];
}
