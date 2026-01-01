<?php

namespace FF\Pagination;

/**
 * Paginator - Simple Pagination Helper
 *
 * Provides pagination functionality for lists of items.
 */
class Paginator
{
    protected array $items;
    protected int $total;
    protected int $perPage;
    protected int $currentPage;
    protected int $lastPage;
    protected string $path;
    protected array $query = [];

    /**
     * Create a new paginator instance
     *
     * @param array $items All items
     * @param int $total Total number of items
     * @param int $perPage Items per page
     * @param int $currentPage Current page number
     * @param string $path Base URL path
     * @param array $query Additional query parameters
     */
    public function __construct(
        array $items,
        int $total,
        int $perPage = 15,
        int $currentPage = 1,
        string $path = '',
        array $query = []
    ) {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage > 0 ? $perPage : 15;
        $this->currentPage = max(1, $currentPage);
        $this->lastPage = max(1, (int) ceil($total / $this->perPage));
        $this->path = $path;
        $this->query = $query;

        // Ensure current page is valid
        if ($this->currentPage > $this->lastPage) {
            $this->currentPage = $this->lastPage;
        }
    }

    /**
     * Get the items for the current page
     *
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the total number of items
     *
     * @return int
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get the number of items per page
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get the last page number
     *
     * @return int
     */
    public function lastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * Determine if there are more pages to show
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Determine if there are pages to show before current
     *
     * @return bool
     */
    public function hasPreviousPages(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Get the URL for a given page number
     *
     * @param int $page
     * @return string
     */
    public function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        $query = array_merge($this->query, ['page' => $page]);
        $queryString = http_build_query($query);

        return $this->path . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Get the URL for the next page
     *
     * @return string|null
     */
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage + 1);
        }

        return null;
    }

    /**
     * Get the URL for the previous page
     *
     * @return string|null
     */
    public function previousPageUrl(): ?string
    {
        if ($this->hasPreviousPages()) {
            return $this->url($this->currentPage - 1);
        }

        return null;
    }

    /**
     * Get the first item number on the current page
     *
     * @return int
     */
    public function firstItem(): int
    {
        return $this->total > 0 ? (($this->currentPage - 1) * $this->perPage) + 1 : 0;
    }

    /**
     * Get the last item number on the current page
     *
     * @return int
     */
    public function lastItem(): int
    {
        return min($this->firstItem() + count($this->items) - 1, $this->total);
    }

    /**
     * Get an array of page numbers to display in pagination links
     *
     * @param int $onEachSide Number of pages to show on each side of current page
     * @return array
     */
    public function getPageRange(int $onEachSide = 3): array
    {
        $start = max(1, $this->currentPage - $onEachSide);
        $end = min($this->lastPage, $this->currentPage + $onEachSide);

        return range($start, $end);
    }

    /**
     * Render pagination links
     *
     * @return string HTML for pagination
     */
    public function links(): string
    {
        if ($this->lastPage <= 1) {
            return '';
        }

        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

        // Previous button
        if ($this->hasPreviousPages()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . h($this->previousPageUrl()) . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }

        // Page numbers
        $pages = $this->getPageRange();

        // First page + ellipsis
        if (!in_array(1, $pages)) {
            $html .= '<li class="page-item"><a class="page-link" href="' . h($this->url(1)) . '">1</a></li>';
            if ($pages[0] > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Page numbers in range
        foreach ($pages as $page) {
            if ($page == $this->currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . h($this->url($page)) . '">' . $page . '</a></li>';
            }
        }

        // Ellipsis + last page
        if (!in_array($this->lastPage, $pages)) {
            if (end($pages) < $this->lastPage - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . h($this->url($this->lastPage)) . '">' . $this->lastPage . '</a></li>';
        }

        // Next button
        if ($this->hasMorePages()) {
            $html .= '<li class="page-item"><a class="page-link" href="' . h($this->nextPageUrl()) . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }

    /**
     * Get pagination info text (e.g., "Showing 1 to 15 of 100 items")
     *
     * @return string
     */
    public function info(): string
    {
        if ($this->total == 0) {
            return 'No items found';
        }

        return sprintf(
            'Showing %d to %d of %d items',
            $this->firstItem(),
            $this->lastItem(),
            $this->total
        );
    }
}
