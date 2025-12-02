@extends('emails.layouts.base')

@section('content')
<div class="email-header" style="background: linear-gradient(135deg, #16A34A 0%, #15803d 100%);">
    <h1>{{ $grievance->type_label }} Resolvida</h1>
</div>

<div class="email-content">
    <p>Prezado(a),</p>

    <p>Temos o prazer de informar que a sua {{ $grievance->type_label_lowercase }} foi <strong>resolvida com sucesso!</strong></p>

    <div class="reference-box" style="border-color: #16A34A;">
        <div class="reference-label" style="color: #166534;">Número de Referência</div>
        <div class="reference-number" style="color: #16A34A;">{{ $grievance->reference_number }}</div>
    </div>

    <div class="success-box">
        <div class="success-box-title">{{ $grievance->type_label }} Resolvida</div>
        @if($grievance->resolved_at)
        <p class="success-box-text">
            <strong>Data de Resolução:</strong> {{ $grievance->resolved_at->format('d/m/Y H:i') }}
        </p>
        @endif
        @if($grievance->resolvedBy)
        <p class="success-box-text">
            <strong>Resolvida por:</strong> {{ $grievance->resolvedBy->name }}
        </p>
        @endif
    </div>

    @if($grievance->resolution_notes)
    <div class="info-box" style="border-left-color: #16A34A;">
        <div class="info-box-title">Detalhes da Resolução</div>
        <p style="margin: 0; color: #374151;">{{ $grievance->resolution_notes }}</p>
    </div>
    @endif

    <div class="info-box">
        <div class="info-box-title">Agradecemos a sua colaboração!</div>
        <p style="margin: 0; color: #4b5563;">
            Se tiver alguma dúvida sobre a resolução ou se o problema persistir,
            não hesite em entrar em contacto connosco.
        </p>
    </div>

    <p>
        Obrigado por utilizar o nosso sistema de reclamações.
    </p>

    <div class="signature">
        <p class="signature-text">Atenciosamente,</p>
        <p class="signature-name">Equipa FUNAE</p>
    </div>
</div>
@endsection
