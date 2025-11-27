@extends('emails.layouts.base')

@section('content')
<div class="email-header" style="background: linear-gradient(135deg, #DC2626 0%, #b91c1c 100%);">
    <h1>{{ $grievance->type_label }} Não Procedente</h1>
</div>

<div class="email-content">
    <p>Prezado(a),</p>

    <p>Após análise detalhada, informamos que a sua {{ $grievance->type_label_lowercase }} foi considerada <strong>não procedente</strong>.</p>

    <div class="reference-box" style="border-color: #DC2626; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
        <div class="reference-label" style="color: #991b1b;">Número de Referência</div>
        <div class="reference-number" style="color: #DC2626;">{{ $grievance->reference_number }}</div>
    </div>

    <div class="warning-box">
        <div class="warning-box-title">Status: Rejeitada</div>
        <p class="warning-box-text">
            A sua {{ $grievance->type_label_lowercase }} foi analisada pela nossa equipa técnica e considerada não procedente.
        </p>
    </div>

    @if($reason)
    <div class="info-box" style="border-left-color: #DC2626;">
        <div class="info-box-title">Justificativa</div>
        <p style="margin: 0; color: #374151;">{{ $reason }}</p>
    </div>
    @endif

    <div class="info-box">
        <div class="info-box-title">O que fazer agora?</div>
        <ul>
            <li>Pode rever os detalhes da análise</li>
            <li>Se discordar da decisão, pode submeter uma nova {{ $grievance->type_label_lowercase }} com informações adicionais</li>
            <li>Para esclarecimentos, pode entrar em contacto através dos nossos canais habituais</li>
        </ul>
    </div>

    <p>
        Agradecemos a sua compreensão e continuamos ao seu dispor.
    </p>

    <div class="signature">
        <p class="signature-text">Atenciosamente,</p>
        <p class="signature-name">Equipa FUNAE</p>
    </div>
</div>
@endsection
