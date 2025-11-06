<?php

namespace App\Jobs;

use App\Models\Visitors;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecordVisitJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public array $visitData;

    public function __construct(array $visitData)
    {
        $this->visitData = $visitData;
    }

    public function handle()
    {
        // Optionally additional processing before saving
        Visitors::create($this->visitData);
    }
}
