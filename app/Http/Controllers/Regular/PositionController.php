<?php

namespace App\Http\Controllers\Regular;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;


class PositionController extends BaseController {

    private function makeImage($xgid, $move) {
        $xgidObj = new \App\Util\XGID($xgid);
        if (isset($move)) {
            $xgidObj->executeAction($move);
            $image = $xgidObj->createPositionImage(new \App\Util\XGID($xgid));
        } else {
            $image = $xgidObj->createPositionImage();
        }
        $imagePath = storage_path(sprintf('app/public/%s.png', uniqid('position_')));
        imagepng($image, $imagePath);
        imagedestroy($image);
        return $imagePath;
    }

    public function calc(Request $request, $xgid) {
        $client = new \GuzzleHttp\Client();
        $jsonPath = storage_path(sprintf('app/public/%s.json', uniqid('xgid_')));
        $client->get('http://app.river.xvs.jp/xgid/'.$xgid, [
            'save_to'=>$jsonPath
        ]);
        $gnubgResult = json_decode(file_get_contents($jsonPath));
        unlink($jsonPath);
        // 付加情報(移動後XGID)
        foreach ($gnubgResult->moveEquities as &$moveEquities) {
            $moveEquities->xgid = (new \App\Util\XGID($xgid))->executeAction($moveEquities->value)->nextTurn()->xgidValue();
        }
        foreach ($gnubgResult->cubeEquities as &$cubeEquities) {
            if (\App\Util\ActionCube::match($cubeEquities->value)) {
                $cubeEquities->xgid = (new \App\Util\XGID($xgid))->executeAction($cubeEquities->value)->xgidValue();
            } else {
                $cubeEquities->xgid = NULL;
            }
        }
        return response()->json($gnubgResult);
    }

    public function images(Request $request, $xgid) {
        return \Response::make(\File::get($this->makeImage($xgid, $request->input('m'))), 200)->header('Content-type', 'image/png');
    }

    public function download(Request $request, $xgid) {
        return response()->download($this->makeImage($xgid, $request->input('m')));
    }
}
