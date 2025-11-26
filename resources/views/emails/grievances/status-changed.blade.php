@extends('emails.layouts.base')

@section('title', 'Atualização de Status - FUNAE')

@section('content')
<div class="email-header">
    <h1>Atualização de Status</h1>
</div>

<div class="email-content">
    <p>Prezado(a),</p>

    <p>O estado da sua {{ $grievance->type_label_lowercase }} foi atualizado.</p>

    <div class="reference-box">
        <div class="reference-label">Número de Referência</div>
        <div class="reference-number">{{ $grievance->reference_number }}</div>
    </div>

    <div class="status-change">
        <span class="status-badge status-old">{{ $oldStatusLabel }}</span>
        <span class="status-arrow">→</span>
        <span class="status-badge status-new">{{ $newStatusLabel }}</span>
    </div>

    <div class="info-box">
        @if($newStatus === 'under_review')
            <p>A sua {{ $grievance->type_label_lowercase }} está a ser analisada pela nossa equipa técnica.</p>
        @elseif($newStatus === 'assigned')
            <p>A sua {{ $grievance->type_label_lowercase }} foi atribuída a um técnico especializado.</p>
        @elseif($newStatus === 'in_progress')
            <p>O processamento da sua {{ $grievance->type_label_lowercase }} está em andamento.</p>
        @elseif($newStatus === 'pending_approval')
            <p>A resolução da sua {{ $grievance->type_label_lowercase }} está pendente de aprovação.</p>
        @elseif($newStatus === 'resolved')
            <p>A sua {{ $grievance->type_label_lowercase }} foi resolvida com sucesso!</p>
        @elseif($newStatus === 'rejected')
            <p>A sua {{ $grievance->type_label_lowercase }} foi considerada não procedente após análise.</p>
        @else
            <p>O status da sua {{ $grievance->type_label_lowercase }} foi atualizado.</p>
        @endif
    </div>

    <p>
        Pode continuar a acompanhar o progresso da sua {{ $grievance->type_label_lowercase }} online a qualquer momento usando o número de referência acima.
    </p>

    <div class="signature">
        <p class="signature-text">Atenciosamente,</p>
        <p class="signature-name">Equipa FUNAE</p>
    </div>
</div>
@endsection
