<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Exports\StatisticsExport;
use App\Models\User;
use App\Models\Grievance;

echo "=== DEBUG EXPORT ===\n";

try {
    // 1. Testar o usuário
    $user = User::find(8);
    if (!$user) {
        die("❌ Usuário 8 não encontrado\n");
    }
    echo "✅ Usuário: " . $user->name . "\n";
    
    // 2. Testar a instância
    echo "2. Criando StatisticsExport...\n";
    $export = new StatisticsExport('12months', $user);
    
    // 3. Testar método getSubmissionsDataForExport diretamente
    echo "3. Testando getSubmissionsDataForExport()...\n";
    
    // Usar Reflection para acessar método privado
    $reflection = new ReflectionClass($export);
    $method = $reflection->getMethod('getSubmissionsDataForExport');
    $method->setAccessible(true);
    
    $submissions = $method->invoke($export);
    echo "   ✅ Sucesso! " . $submissions->count() . " submissões\n";
    
    // 4. Testar PDF
    echo "4. Testando exportPdf()...\n";
    $method = $reflection->getMethod('exportPdf');
    $method->setAccessible(true);
    
    $path = $method->invoke($export, 'debug-test-' . time(), 'public');
    echo "   ✅ PDF criado: " . $path . "\n";
    
    // 5. Testar store completo
    echo "5. Testando store('pdf')...\n";
    $path = $export->store('pdf', 'final-test-' . time(), 'public');
    echo "   ✅ Store completo: " . $path . "\n";

    echo "6. Testando store('csv')...\n";
try {
    $path = $export->store('csv', 'csv-test-' . time(), 'public');
    echo "   ✅ CSV criado: " . $path . "\n";
} catch (Exception $e) {
    echo "   ❌ CSV erro: " . $e->getMessage() . "\n";
}
    
} catch (Exception $e) {
    echo "❌ ERRO CRÍTICO:\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    
    // Verificar se é o erro de assignedTechnician
    if (strpos($e->getMessage(), 'assignedTechnician') !== false) {
        echo "\n🔍 ENCONTRADO assignedTechnician NO ERRO!\n";
        echo "Mas não encontrei no código... Pode ser cache?\n";
    }
}