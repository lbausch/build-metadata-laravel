<?php

namespace Lbausch\BuildMetadataLaravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lbausch\BuildMetadataLaravel\Metadata;

class CachedBuildMetadata
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        /**
         * Build metadata.
         */
        public Metadata $build_metadata,
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel|array
    {
        return [];
    }
}
