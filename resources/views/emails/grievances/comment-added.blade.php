@extends('emails.layouts.base')

@section('title', 'Nova Atualização - FUNAE')

@section('content')
<div class="email-header">
    <h1>Nova Atualização</h1>
</div>

<div class="email-content">
    <p>Prezado(a),</p>

    <p>Foi adicionado um novo comentário à sua {{ $grievance->type_label_lowercase }}.</p>

    <div class="reference-box">
        <div class="reference-label">Número de Referência</div>
        <div class="reference-number">{{ $grievance->reference_number }}</div>
    </div>

    <div class="comment-box">
        <div class="comment-meta">
            <span class="comment-author">{{ $update->user ? $update->user->name : 'Sistema' }}</span>
            • {{ $update->created_at->format('d/m/Y H:i') }}
        </div>

        <div class="comment-text">
            @if($update->comment)
                {{ $update->comment }}
            @else
                {{ $update->description }}
            @endif
        </div>
    </div>

    <p>
        Continue a acompanhar o progresso da sua {{ $grievance->type_label_lowercase }} através do link acima.
    </p>

    <div class="signature">
        <p class="signature-text">Atenciosamente,</p>
        <p class="signature-name">Equipa FUNAE</p>
    </div>
</div>
@endsection
