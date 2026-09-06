<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Logout extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();      // セッションを破棄
        $request->session()->regenerateToken(); // CSRFトークンを作り直す        
        return redirect('/')->with('success', 'You\'ve successfully logged out!');
    }
}
