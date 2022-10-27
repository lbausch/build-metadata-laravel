<?php

namespace Lbausch\BuildMetadataLaravel;

use Illuminate\Support\Arr;

class Metadata
{
    public function __construct(
        /**
         * Metadata.
         */
        protected array $metadata = [],
    ) {
    }

    /**
     * Create from JSON.
     */
    public static function fromJson(string $data): static
    {
        $metadata = json_decode($data, $associative = true, 512, JSON_THROW_ON_ERROR);

        return new static($metadata);
    }

    /**
     * Get metadata.
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->metadata;
        }

        return Arr::get($this->metadata, $key, $default);
    }

    /**
     * Determine if an item exists in the metadata.
     */
    public function has(string $key): bool
    {
        $data = $this->get();

        return Arr::has($data, $key);
    }
}
