<?php

namespace FF\Assets;

/**
 * AssetManager - Asset Management
 * 
 * Manages CSS, JavaScript, and other static assets.
 * Handles asset versioning and URL generation.
 */
class AssetManager
{
    /**
     * Registered assets
     * 
     * @var array
     */
    protected array $assets = [
        'css' => [],
        'js' => [],
    ];

    /**
     * Asset version for cache busting
     * 
     * @var string|null
     */
    protected ?string $version = null;

    /**
     * Public URL base path
     * 
     * @var string
     */
    protected string $basePath = '/assets';

    /**
     * Create a new AssetManager instance
     * 
     * @param string $basePath The base path for assets
     * @param string|null $version The asset version
     */
    public function __construct(string $basePath = '/assets', ?string $version = null)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->version = $version;
    }

    /**
     * Register a CSS asset
     * 
     * @param string $name The asset name
     * @param string $path The asset path
     * @return self
     */
    public function css(string $name, string $path): self
    {
        $this->assets['css'][$name] = $path;
        return $this;
    }

    /**
     * Register a JavaScript asset
     * 
     * @param string $name The asset name
     * @param string $path The asset path
     * @return self
     */
    public function js(string $name, string $path): self
    {
        $this->assets['js'][$name] = $path;
        return $this;
    }

    /**
     * Get a CSS asset URL
     * 
     * @param string $name The asset name
     * @return string The asset URL
     */
    public function getCss(string $name): string
    {
        return $this->getAssetUrl('css', $name);
    }

    /**
     * Get a JavaScript asset URL
     * 
     * @param string $name The asset name
     * @return string The asset URL
     */
    public function getJs(string $name): string
    {
        return $this->getAssetUrl('js', $name);
    }

    /**
     * Get all CSS asset URLs
     * 
     * @return array
     */
    public function getAllCss(): array
    {
        return array_map(fn($name) => $this->getCss($name), array_keys($this->assets['css']));
    }

    /**
     * Get all JavaScript asset URLs
     * 
     * @return array
     */
    public function getAllJs(): array
    {
        return array_map(fn($name) => $this->getJs($name), array_keys($this->assets['js']));
    }

    /**
     * Get asset URL with version
     * 
     * @param string $type The asset type (css or js)
     * @param string $name The asset name
     * @return string The asset URL
     */
    protected function getAssetUrl(string $type, string $name): string
    {
        if (!isset($this->assets[$type][$name])) {
            throw new \Exception("Asset not found: {$type}/{$name}");
        }

        $path = $this->assets[$type][$name];
        $url = $this->basePath . '/' . ltrim($path, '/');

        // Add version for cache busting
        if ($this->version) {
            $url .= '?v=' . $this->version;
        }

        return $url;
    }

    /**
     * Render CSS link tags
     * 
     * @param array $names Optional specific CSS assets to render
     * @return string HTML
     */
    public function renderCss(array $names = []): string
    {
        $names = empty($names) ? array_keys($this->assets['css']) : $names;
        $html = '';

        foreach ($names as $name) {
            $url = $this->getCss($name);
            $html .= '<link rel="stylesheet" href="' . htmlspecialchars($url) . '">' . "\n";
        }

        return $html;
    }

    /**
     * Render JavaScript script tags
     * 
     * @param array $names Optional specific JS assets to render
     * @return string HTML
     */
    public function renderJs(array $names = []): string
    {
        $names = empty($names) ? array_keys($this->assets['js']) : $names;
        $html = '';

        foreach ($names as $name) {
            $url = $this->getJs($name);
            $html .= '<script src="' . htmlspecialchars($url) . '"><\/script>' . "\n";
        }

        return $html;
    }

    /**
     * Set the version
     * 
     * @param string $version The version
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get all registered assets
     * 
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }
}
