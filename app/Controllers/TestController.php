<?php

namespace App\Controllers;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;

class TestController
{
    public function index(Request $request): Response
    {
        return response()->json(['message' => 'Controller working']);
    }
}