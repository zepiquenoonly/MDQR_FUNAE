@extends('emails.layouts.base')

@section('title', 'Reclamação Atribuída - FUNAE')

@section('content')
<div class="email-header">
    <h1>{{ $grievance->type_label }} Atribuída</h1>
</div>

<div class="email-content">
    <p>Prezado(a),</p>

    <p>A sua {{ $grievance->type_label_lowercase }} foi atribuída a um técnico especializado da nossa equipa.</p>

    <div class="reference-box">
        <div class="reference-label">Número de Referência</div>
        <div class="reference-number">{{ $grievance->reference_number }}</div>
    </div>

    <div class="technician-box">
        <div class="technician-box-title">Técnico Responsável</div>
        <div class="technician-name">{{ $assignedUser->name }}</div>
        <div class="technician-email">{{ $assignedUser->email }}</div>
    </div>

    <div class="info-box">
        <div class="info-box-title">O que isto significa?</div>
        <ul>
            <li>A sua {{ $grievance->type_label_lowercase }} está a ser analisada por um técnico especializado</li>
            <li>O técnico irá investigar a situação e trabalhar na resolução</li>
            <li>Receberá atualizações regulares sobre o progresso</li>
        </ul>
    </div>

    <p>
        Continuamos comprometidos em resolver a sua {{ $grievance->type_label_lowercase }} no menor tempo possível.
    </p>

    <div class="signature">
        <p class="signature-text">Atenciosamente,</p>
        <p class="signature-name">Equipa FUNAE</p>
    </div>
</div>
@endsection
