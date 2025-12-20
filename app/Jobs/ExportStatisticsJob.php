<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StatisticsExport;

class ExportStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $period;
    protected $format;
    protected $userId;
    protected $fileName;

    public function __construct($period, $format, $userId)
    {
        $this->period = $period;
        $this->format = $format;
        $this->userId = $userId;
        $this->fileName = 'statistics_' . $userId . '_' . now()->timestamp . '.' . $format;
    }

    public function handle()
    {
        $export = new StatisticsExport($this->period); // cria exportação de acordo com teu período
        Excel::store($export, 'public/exports/' . $this->fileName);

        // Marca o ficheiro como pronto no cache (30min)
        cache()->put("export_ready_{$this->fileName}", true, now()->addMinutes(30));
    }
}
