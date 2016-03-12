<?php

namespace App\Util;


class ActionDiceException extends \Exception {}

class ActionDice extends ActionInfo {

	const CANNOT_MOVE = 'Cannot Move';

	public $playerDiv;
	public $dice1;
	public $dice2;

	public $xgidValueBefore;
	public $xgidValueAfter;

	public $checkerMoveArray;

	public $errorMessageArray;

	public function __construct($actionValue) {
		parent::__construct($actionValue);
		$this->checkerMoveArray = [];
		if (!$this->noAction()) {
			foreach (explode(' ', $this->actionValue) as $actionItem) {
				if ($actionItem === '') {
					continue;
				}
				// '0/'はNoMoveの意とする（おそらく特殊仕様）
				if (Utils::startsWith($actionItem, '0/')) {
					continue;
				}
				$this->checkerMoveArray[] = new CheckerMove($actionItem);
			}
		}
		$this->errorMessageArray = [];
	}

	public function execute($xgid) {
		foreach ($this->checkerMoveArray as $checkerMove) {
			$checkerMove->execute($xgid);
		}
	}

	public function noAction() {
		return $this->cannotMove() || $this->notMove();
	}

	public function cannotMove() {
		return strcasecmp($this->actionValue, self::CANNOT_MOVE) === 0;
	}

	public function notMove() {
		return preg_match('/^[\?]+$/', $this->actionValue);
	}

	// TODO ここから下はどこで使うのか？
	public function calcXGID($xgid) {
		try {
			$this->xgidValueBefore = $xgid->setTurn($this->playerDiv)->setAction($this->dice1, $this->dice2)->xgidValue();
			$this->xgidValueAfter = $xgid->executeAction($this)->xgidValue();
		} catch (Exception $ex) {
			if (!isset($this->xgidValueBefore)) {
				$this->xgidValueBefore = $xgid->xgidValue();
			}
			if (!isset($this->xgidValueAfter)) {
				$this->xgidValueAfter = $xgid->xgidValue();
			}
			$this->errorMessageArray[] = $ex->getMessage();
		}
		return $this;
	}

	public function equalsDBrecord($move) {
		if ($this->playerDiv != $move['player_div']
			|| $this->dice1 != $move['dice1']
			|| $this->dice2 != $move['dice2']
			|| $this->actionValue != $move['action']
			|| $this->xgidValueBefore != $move['xgid']
			|| $this->xgidValueAfter != $move['moved_xgid']) {
			return false;
		}
		return true;
	}
}
