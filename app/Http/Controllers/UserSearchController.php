<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $term = $request->q;

        $users = DB::table('users')
            ->where('id', '!=', Auth::id())
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'username')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}