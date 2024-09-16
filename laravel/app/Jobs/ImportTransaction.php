<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportTransaction implements ShouldQueue
{
    use Batchable, Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        Transaction::create($this->data);
    }
}
