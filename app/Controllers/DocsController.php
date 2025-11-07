<?php

namespace App\Controllers;

use FF\Http\Response;

/**
 * DocsController
 *
 * Serves documentation pages using standard view rendering.
 * Templates render exactly as authored; variable output remains auto-escaped
 * by the view engine unless explicitly marked with raw_html().
 */
class DocsController
{
    /**
     * Tags and attributes permitted within documentation responses.
     *
     * @var array<int,string>
     */
    private const DOCS_ALLOWED_TAGS = [
        'a', 'abbr', 'article', 'aside', 'b', 'blockquote', 'body', 'br',
        'caption', 'code', 'del', 'div', 'em', 'figcaption', 'figure',
        'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header',
        'hr', 'html', 'i', 'img', 'ins', 'li', 'link', 'main', 'mark',
        'meta', 'nav', 'ol', 'p', 'pre', 'section', 'small', 'span', 'strong',
        'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'title',
        'tr', 'ul'
    ];

    /**
     * @var array<string,array<int,string>>
     */
    private const DOCS_ALLOWED_ATTRIBUTES = [
        '*' => ['class'],
        'a' => ['href', 'title', 'rel', 'target'],
        'abbr' => ['title'],
        'caption' => ['align'],
        'html' => ['lang'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'link' => ['rel', 'href'],
        'meta' => ['charset', 'name', 'content', 'http-equiv'],
        'ol' => ['start'],
        'table' => ['summary'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['scope', 'colspan', 'rowspan'],
    ];

    /**
     * Available documentation sections.
     *
     * @var array<int,string>
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
        'deployment',
    ];

    /**
     * Show documentation index.
     */
    public function index(): Response
    {
        return $this->renderDocsView('docs/index', [
            'title' => 'Documentation - FF Framework',
            'sections' => $this->sections,
        ]);
    }

    /**
     * Show specific documentation section.
     */
    public function show(string $section): Response
    {
        if (!in_array($section, $this->sections, true)) {
            session()->flash('error', 'Documentation section not found');
            return redirect('/docs');
        }

        return $this->renderDocsView('docs/' . $section, [
            'title' => ucfirst($section) . ' - Documentation - FF Framework',
            'section' => $section,
            'sections' => $this->sections,
        ]);
    }

    /**
     * Render a documentation view.
     */
    protected function renderDocsView(string $view, array $data): Response
    {
        $content = view($view, $data);
        return response($content);
    }
}
