<?php

namespace App\Console\Commands;

use App\Services\EpubBookImporter;
use Illuminate\Console\Command;

class ImportEpubBookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:import {path} {--language=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an EPUB book into the books/book_pages/paragraphs/sentences tables so it can be read and translated';

    public function handle(EpubBookImporter $importer): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $book = $importer->import($path, $this->option('language'));

        $this->info("Imported \"{$book->title}\" as book #{$book->id} with {$book->pages()->count()} page(s) and {$book->paragraphs()->count()} paragraph(s).");

        return self::SUCCESS;
    }
}
