<?php

namespace App\Jobs\Vrm;

use App\Facades\Vrm\MediaForge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMediaForgeUpload implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $files;
    protected $folder;
    protected $operations;

    /**
     * Create a new job instance.
     *
     * @param array|\Illuminate\Http\UploadedFile[] $files
     * @param string $folder
     * @param array $operations
     */
    public function __construct(array $files, string $folder, array $operations = [])
    {
        $this->files = $files;
        $this->folder = $folder;
        $this->operations = $operations;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mediaForge = MediaForge::upload($this->files)->to($this->folder);

        // Dynamically apply operations
        foreach ($this->operations as $operation => $params) {
            if (method_exists($mediaForge, $operation)) {
                // If params is array or scalar
                if (is_array($params)) {
                    $mediaForge = $mediaForge->$operation(...$params);
                } elseif ($params !== null) {
                    $mediaForge = $mediaForge->$operation($params);
                } else {
                    $mediaForge = $mediaForge->$operation();
                }
            }
        }

        // Run processing
        $mediaForge->run();
    }
}
