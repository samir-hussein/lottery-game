<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile_number' => 'required|numeric',
            'email' => 'required|email:filter',
            'gender' => 'required|in:male,female',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], 422);
        }

        Player::create($request->all());

        return response()->json([
            'success' => 'player created successfully.'
        ], 201);
    }
}
