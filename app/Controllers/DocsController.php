<?php

namespace App\Controllers;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;

/**
 * DocsController
 * 
 * Handles documentation pages.
 */
class DocsController
{
    /**
     * Available documentation sections
     * 
     * @var array
     */
    protected array $sections = [
        'installation',
        'routing',
        'controllers',
        'database',
        'models',
        'validation',
        'authentication',
        'sessions',
        'caching',
        'logging',
        'events',
        'security',
        'views',
        'helpers',
        'deployment'
    ];

    /**
     * Show documentation index
     * 
     * @return Response
     */
    public function index(): Response
    {
        $content = view('docs/index', [
            'title' => 'Documentation - FF Framework',
            'sections' => $this->sections
        ]);
        
        return response($content);
    }

    /**
     * Show specific documentation section
     * 
     * @param string $section The section name
     * @return Response
     */
    public function show(string $section): Response
    {
        if (!in_array($section, $this->sections)) {
            session()->flash('error', 'Documentation section not found');
            return redirect('/docs');
        }

        $content = view('docs/' . $section, [
            'title' => ucfirst($section) . ' - Documentation - FF Framework',
            'section' => $section,
            'sections' => $this->sections
        ]);
        
        return response($content);
    }
}
