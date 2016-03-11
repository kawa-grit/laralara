<?php

namespace App\Http\Controllers\Regular;

use Illuminate\Http\Request;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        return view('home');
    }

    public function show(Request $request) {
        return 'Controller SECOND!'.$request->input('ppp', 'hoge');
    }
}
