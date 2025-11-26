@extends('emails.layouts.base')

@section('title', 'Reclamação Recebida - FUNAE')

@section('content')
<div class="email-header">
    <h1>{{ $grievance->type_label }} Recebida</h1>
</div>

<div class="email-content">
    <p>Prezado(a),</p>

    <p>A sua {{ $grievance->type_label_lowercase }} foi recebida com sucesso pelo sistema de gestão de reclamações da <strong>FUNAE</strong>.</p>

    <div class="reference-box">
        <div class="reference-label">Número de Referência</div>
        <div class="reference-number">{{ $grievance->reference_number }}</div>
    </div>

    <div class="detail-box">
        <div class="detail-row">
            <span class="detail-label">Categoria:</span>
            <span class="detail-value">{{ $grievance->category }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Data de Submissão:</span>
            <span class="detail-value">{{ $grievance->submitted_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Estado Atual:</span>
            <span class="detail-value">{{ $grievance->status_label }}</span>
        </div>
    </div>

    <p>
        <strong>Guarde este número de referência</strong> para poder acompanhar o progresso da sua reclamação.
    </p>

    <div class="info-box">
        <div class="info-box-title">Próximos Passos</div>
        <ul>
            <li>A sua {{ $grievance->type_label_lowercase }} será analisada pela nossa equipa</li>
            <li>Receberá notificações por email sobre cada atualização</li>
            <li>Pode acompanhar o progresso online a qualquer momento</li>
        </ul>
    </div>

    <p>
        Se tiver alguma dúvida, pode entrar em contacto connosco através dos canais habituais.
    </p>

    <div class="signature">
        <p class="signature-text">Atenciosamente,</p>
        <p class="signature-name">Equipa FUNAE</p>
    </div>
</div>
@endsection
