<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Extend\Import;

class ImportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importfiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process some of the import queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hash = DB::table('import_data')->select('hash', 'log_date')->orderBy('priority', 'desc')->orderBy('import_id', 'desc')->first();
        if ($hash != null)
        {
            $importParser = new Import();
            $importParser->parseImportData ($hash);
            $importParser->formatLogData (true);
            $importParser->saveLogData ();
        }
    }
}
