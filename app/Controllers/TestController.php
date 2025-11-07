<?php

namespace App\Controllers;

use FF\Http\Request;
use FF\Http\Response;

class TestController
{
    public function index(Request $request): Response
    {
        return response()->json(['message' => 'Controller working']);
    }
}