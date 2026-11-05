<?php

namespace App\Jobs;

use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Services\EpubBookImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportEpubBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Don't retry: a failed import leaves the uploaded file deleted, so a
     * retry would just fail again with a missing-file error.
     */
    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public readonly string $path,
        public readonly int $languageId,
    ) {}

    public function handle(EpubBookImporter $importer, LanguageRepositoryInterface $languages): void
    {
        try {
            $language = $languages->findOrFail($this->languageId);

            $importer->import(Storage::disk('local')->path($this->path), $language->code);
        } finally {
            Storage::disk('local')->delete($this->path);
        }
    }
}
