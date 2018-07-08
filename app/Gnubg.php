<?php

namespace App;


class GnubgException extends \Exception {}

class Gnubg {
    
    public $result = FALSE;
    public $xgid;
    public $out = NULL;
    public $rate = NULL;
    public $cubeEqs = [];
    public $moveEqs = [];

    public function toArray() {
        $cubeEqs = [];
        foreach ($this->cubeEqs as $e) {
            $cubeEqs[] = $e->toArray();
        }
        $moveEqs = [];
        foreach ($this->moveEqs as $e) {
            $moveEqs[] = $e->toArray();
        }
        return [
            'result'=>$this->result,
            'xgid'=>$this->xgid,
            'out'=>$this->out,
            'rate'=>isset($this->rate) ? $this->rate->toArray() : NULL,
            'cubeEquities'=>$cubeEqs,
            'moveEquities'=>$moveEqs,
        ];
    }

    public static function execute($xgid) {
        $g = new Gnubg($xgid);
        $cmd = storage_path(sprintf('app/public/%s', uniqid('gnubg_cmd_')));
        file_put_contents($cmd, "set xgid ${xgid}\neval\nhint\nquit");
        exec(
            '/virtual/kkk/usr/gnubg/bin/gnubg < '.$cmd,
            $g->out,
            $vvv);

        if ($vvv === 0) {
            $g->result = TRUE;
            for ($i = 0; $i < count($g->out); $i++) {
                $o = $g->out[$i];
                if (preg_match('/^Cube.* equities:$/', $o)) {
                    $g->cubeEqs = [
                        Equity::cubeEquity($g->out[$i + 1]),
                        Equity::cubeEquity($g->out[$i + 2]),
                        Equity::cubeEquity($g->out[$i + 3]),
                    ];
                } elseif (preg_match('/^[ ]+[0-9]+\. Cubeful [0-9]-ply(.*)Eq\.:[ ]+([-+][0-9]\.[0-9]+)/', $o, $matches)) {
                    $g->moveEqs[] = Equity::moveEquity(trim($matches[1]), trim($matches[2]))->addDiff($o)
                        ->addMoveRate($g->out[$i + 1]);
                } elseif (preg_match('/^static:(.*)$/', $o, $matches)) {
                    $g->rate = Rate::evalRate(trim($matches[1]));
                }
            }
            return $g;
        } else {
            throw new GnubgException('Command fail with '.$xgid);
        }
    }

    public function __construct($xgid) {
        $this->xgid = $xgid;
    }
}


class Equity {

    public $value;
    public $equity;
    public $equityDiff = NULL;
    public $rate = NULL;

    public function toArray() {
        return [
            'value'=>$this->value,
            'equity'=>$this->equity,
            'equityDiff'=>$this->equityDiff,
            'rate'=>isset($this->rate) ? $this->rate->toArray() : NULL,
        ];
    }

    public function __construct($v, $e) {
        $this->value = $v;
        $this->equity = floatval($e);
    }

    public function addDiff($line) {
        if (preg_match('/\([ ]*([+-][0-9]\.[0-9]+)\)$/', $line, $matches)) {
            $this->equityDiff = floatval(trim($matches[1]));
        } else {
            $this->equityDiff = 0;
        }
        return $this;
    }

    public function addMoveRate($line) {
        $this->rate = Rate::moveRate($line);
        return $this;
    }

    public static function moveEquity($v, $e) {
        return new Equity(trim($v), trim($e));
    }

    public static function cubeEquity($line) {
        if (preg_match('/^[0-9]\. ([a-zA-Z, ]+)([-+][0-9]\.[0-9]+)/', $line, $matches)) {
            $e = new Equity(trim($matches[1]), trim($matches[2]));
            return $e->addDiff($line);
        }
        throw new GnubgException('Miss preg match as cube.:'.$line);
    }
}


class Rate {
    public $win;
    public $winGammon;
    public $winBackgammon;
    public $lose;
    public $loseGammon;
    public $loseBackgammon;

    public function toArray() {
        return [
            'win'=>$this->win,
            'winGammon'=>$this->winGammon,
            'winBackgammon'=>$this->winBackgammon,
            'lose'=>$this->lose,
            'loseGammon'=>$this->loseGammon,
            'loseBackgammon'=>$this->loseBackgammon,
        ];
    }

    public static function moveRate($line) {
        if (preg_match('/^[ ]+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+-\s+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)$/', $line, $matches)) {
            $r = new Rate();
            $r->win = floatval(trim($matches[1]));
            $r->winGammon = floatval(trim($matches[2]));
            $r->winBackgammon = floatval(trim($matches[3]));
            $r->lose = floatval(trim($matches[4]));
            $r->loseGammon = floatval(trim($matches[5]));
            $r->loseBackgammon = floatval(trim($matches[6]));
            return $r;
        }
        throw new GnubgException('Miss preg match as move rate.:'.$line);
    }

    public static function evalRate($line) {
        if (preg_match('/^([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+([0-1]\.[0-9]+)\s+/', $line, $matches)) {
            $r = new Rate();
            $r->win = floatval(trim($matches[1]));
            $r->winGammon = floatval(trim($matches[2]));
            $r->winBackgammon = floatval(trim($matches[3]));
            $r->lose = 1 - $r->win;
            $r->loseGammon = floatval(trim($matches[4]));
            $r->loseBackgammon = floatval(trim($matches[5]));
            return $r;
        }
        throw new GnubgException('Miss preg match as eval rate.:'.$line);
    }
}
