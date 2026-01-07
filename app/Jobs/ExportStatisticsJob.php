<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Services\ExcelExportService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
        $this->fileName = 'estatisticas-' . $period . '-' . now()->format('Y-m-d-H-i-s') . '-' . uniqid();
    }

    public function handle()
    {
        Log::info("=== INICIANDO EXPORT JOB ===");
        Log::info("Período: {$this->period}");
        Log::info("Formato: {$this->format}");
        Log::info("User ID: {$this->userId}");
        
        try {
            $user = User::find($this->userId);
            if (!$user) {
                throw new \Exception("Usuário não encontrado: {$this->userId}");
            }
            
            // Criar diretório se não existir
            $exportDir = storage_path('app/public/exports');
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
                Log::info("Diretório criado: {$exportDir}");
            }
            
            Log::info("Iniciando exportação {$this->format}...");
            
            $path = '';
            
            switch ($this->format) {
                case 'xlsx':
                    $service = new ExcelExportService($this->period, $user);
                    $path = $service->exportXlsx($this->fileName);
                    break;
                    
                case 'csv':
                    $service = new ExcelExportService($this->period, $user);
                    $path = $service->exportCsv($this->fileName);
                    break;
                    
                case 'pdf':
                    // Usar o StatisticsExport original para PDF
                    $export = new \App\Exports\StatisticsExport($this->period, $user);
                    $path = $export->store('pdf', $this->fileName, 'public');
                    break;
                    
                default:
                    throw new \Exception("Formato não suportado: {$this->format}");
            }
            
            Log::info("Exportação concluída. Caminho: {$path}");
            
            // Verificar se arquivo foi criado
            $fullPath = storage_path("app/public/{$path}");
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                Log::info("✅ Arquivo criado com sucesso! Tamanho: {$size} bytes");
            } else {
                Log::error("❌ Arquivo não foi criado: {$fullPath}");
                throw new \Exception("Falha ao criar arquivo: {$path}");
            }
            
            Log::info("=== JOB CONCLUÍDO COM SUCESSO ===");
            
        } catch (\Exception $e) {
            Log::error("❌ ERRO NO ExportStatisticsJob: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}