<?php

namespace App\Controllers;

use FF\Framework\Http\Response;

/**
 * HomeController
 * 
 * Handles the home page.
 */
class HomeController
{
    /**
     * Show home page
     * 
     * @return Response
     */
    public function index(): Response
    {
        $content = view('home', [
            'title' => 'FF Framework - Modern PHP MVC Framework'
        ]);
        
        return response($content);
    }
}
