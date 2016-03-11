<?php

namespace App\Http\Controllers\Regular;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class PositionController extends BaseController {

    private function makeImage() {
        $prexgid = new \App\Util\XGID('XGID=--aCBBB-C---d-a--d-c-bB-A-:0:0:-1:11:0:0:0:11:10');
        $xgid = new \App\Util\XGID('XGID=--aCBBB-C---d-a--d-c-bB-A-:0:0:-1:11:0:0:0:11:10');
        //$xgid->executeAction('8/7(2) 3/1 22/14*');
        $xgid->executeAction('13/11(2) 4/1* 23/11');
        $image = $xgid->createPositionImage($prexgid);
        $imagePath = storage_path('app/public/hoge.png'); // TODO
        imagepng($image, $imagePath);
        imagedestroy($image);
        return $imagePath;
    }

    public function test() {
        return $this->makeImage();
    }

    public function images() {
        return \Response::make(\File::get($this->makeImage()), 200)->header('Content-type', 'image/png');
    }

    public function download() {
        return response()->download($this->makeImage());
    }
}
