<?php

namespace PubNub\Endpoints\DataSync;

/**
 * Base for the paginated DataSync list endpoints.
 *
 * DataSync paginates with a single opaque cursor rather than the start/end tokens used by App Context,
 * which is why this does not reuse ObjectsCollectionEndpoint.
 */
abstract class DataSyncCollectionEndpoint extends DataSyncEndpoint
{
    protected ?string $cursor = null;

    protected ?int $limit = null;

    protected ?string $filterFast = null;

    protected ?string $filter = null;

    /** @var array<int|string, string>|string|null */
    protected $sort = null;

    /**
     * Opaque token identifying the next page, taken from meta.next_cursor of the previous response.
     *
     * @return $this
     */
    public function cursor(string $cursor): static
    {
        $this->cursor = $cursor;
        return $this;
    }

    /**
     * Maximum number of items per page. The server defaults to 20 and caps at 100.
     *
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Filter expression in the App Context Query Language, evaluated against strongly consistent
     * storage. Supports only a limited number of conditions.
     *
     * Only properties declared with a `filtering` mode other than `none` in the class registry
     * can be filtered on.
     *
     * @return $this
     */
    public function filterFast(string $filterFast): static
    {
        $this->filterFast = $filterFast;
        return $this;
    }

    /**
     * Filter expression in the App Context Query Language, evaluated against eventually consistent
     * storage. Supports logical operators and nested conditions.
     *
     * @return $this
     */
    public function filter(string $filter): static
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Accepts either a ready-made string ("name:desc,type") or an array
     * (['name' => 'desc', 'type']) that gets joined into one.
     *
     * Only properties declared with a `filtering` mode other than `none` in the class registry
     * can be sorted on.
     *
     * @param array<int|string, string>|string $sort
     * @return $this
     */
    public function sort($sort): static
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * Query parameters shared by every list endpoint, raw - DataSyncEndpoint::buildParams()
     * encodes them once the signature has been taken.
     *
     * @return array<string, string>
     */
    protected function collectionParams(): array
    {
        $params = [];

        if (!empty($this->cursor)) {
            $params['cursor'] = $this->cursor;
        }

        if ($this->limit !== null) {
            $params['limit'] = (string) $this->limit;
        }

        if (!empty($this->filterFast)) {
            $params['filter_fast'] = $this->filterFast;
        }

        if (!empty($this->filter)) {
            $params['filter'] = $this->filter;
        }

        $sort = $this->buildSortValue();

        if ($sort !== null) {
            $params['sort'] = $sort;
        }

        return $params;
    }

    private function buildSortValue(): ?string
    {
        if (empty($this->sort)) {
            return null;
        }

        if (is_string($this->sort)) {
            return $this->sort;
        }

        $entries = [];

        foreach ($this->sort as $key => $value) {
            if (is_int($key)) {
                $entries[] = $value;
            } elseif ($value === 'asc' || $value === 'desc') {
                $entries[] = "$key:$value";
            } else {
                $entries[] = $key;
            }
        }

        return join(",", $entries);
    }
}
