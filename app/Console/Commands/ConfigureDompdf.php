<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureDompdf extends Command
{
    protected $signature = 'dompdf:configure';
    protected $description = 'Configure DOMPDF for Windows/Linux compatibility';

    public function handle()
    {
        $this->info('Configurando DOMPDF...');
        
        // Diretório de fontes
        $fontDir = storage_path('fonts');
        
        if (!File::exists($fontDir)) {
            File::makeDirectory($fontDir, 0755, true);
            $this->info('Diretório de fontes criado: ' . $fontDir);
        }
        
        // Copiar fontes básicas - IGNORAR fontes que não existem
        $dompdfFontDir = base_path('vendor/dompdf/dompdf/lib/fonts');
        
        if (File::exists($dompdfFontDir)) {
            $this->info('Fontes disponíveis no DOMPDF:');
            $availableFonts = File::files($dompdfFontDir);
            foreach ($availableFonts as $font) {
                $this->line('- ' . $font->getFilename());
            }
            
            // Tentar copiar fontes básicas, mas ignorar erros
            $fontsToTry = ['Helvetica.afm', 'Helvetica-Bold.afm', 'Helvetica-Oblique.afm', 'Courier.afm'];
            
            foreach ($fontsToTry as $font) {
                $source = $dompdfFontDir . DIRECTORY_SEPARATOR . $font;
                $destination = $fontDir . DIRECTORY_SEPARATOR . $font;
                
                if (File::exists($source)) {
                    try {
                        File::copy($source, $destination);
                        $this->info('✅ Fonte copiada: ' . $font);
                    } catch (\Exception $e) {
                        $this->warn('⚠️ Não foi possível copiar: ' . $font . ' - ' . $e->getMessage());
                    }
                } else {
                    $this->warn('⚠️ Fonte não encontrada: ' . $font);
                }
            }
        } else {
            $this->error('❌ Diretório de fontes do DOMPDF não encontrado: ' . $dompdfFontDir);
        }
        
        // Criar arquivo de configuração para DOMPDF
        $this->createDompdfConfig();
        
        // Testar configuração
        $this->info('Testando configuração...');
        $this->testDompdf();
        
        return Command::SUCCESS;
    }
    
    private function createDompdfConfig()
    {
        $fontDir = str_replace('\\', '/', storage_path('fonts'));
        $tempDir = str_replace('\\', '/', sys_get_temp_dir());
        $chroot = str_replace('\\', '/', realpath(base_path()));
        
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'defaultFont' => 'helvetica',
            'defaultFontSize' => 12,
            'defaultFontType' => 'normal',
            'fontDir' => $fontDir,
            'fontCache' => $fontDir,
            'tempDir' => $tempDir,
            'chroot' => $chroot,
            'allowedProtocols' => ['http://', 'https://'],
            'enableCssFloat' => false, // Desabilitar para compatibilidade
            'enableFontSubsetting' => false, // Desabilitar para evitar problemas
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'dpi' => 96,
        ];
        
        $configPath = config_path('dompdf.php');
        
        if (!File::exists($configPath)) {
            $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            File::put($configPath, $content);
            $this->info('Arquivo de configuração criado: ' . $configPath);
        } else {
            $this->info('Arquivo de configuração já existe: ' . $configPath);
        }
    }
    
    private function testDompdf()
    {
        try {
            // Teste simples com HTML básico
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Teste DOMPDF</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #333; }
                    p { color: #666; }
                </style>
            </head>
            <body>
                <h1>Teste DOMPDF</h1>
                <p>Data: ' . date('Y-m-d H:i:s') . '</p>
                <p>Sistema: ' . PHP_OS . '</p>
                <p>Fontes: ' . storage_path('fonts') . '</p>
            </body>
            </html>';
            
            $pdf = \PDF::loadHTML($html)
                ->setPaper('A4')
                ->setOptions([
                    'defaultFont' => 'helvetica',
                    'isRemoteEnabled' => false,
                ]);
                
            $output = $pdf->output();
            
            if (strlen($output) > 0) {
                $this->info('✅ DOMPDF funcionando corretamente! Tamanho do PDF: ' . strlen($output) . ' bytes');
                
                // Salvar teste
                $testPath = storage_path('app/test-dompdf.pdf');
                File::put($testPath, $output);
                $this->info('✅ PDF de teste salvo em: ' . $testPath);
            } else {
                $this->error('❌ DOMPDF não gerou conteúdo');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Erro no DOMPDF: ' . $e->getMessage());
            $this->error('Arquivo: ' . $e->getFile());
            $this->error('Linha: ' . $e->getLine());
        }
    }
}