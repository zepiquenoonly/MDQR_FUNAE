<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'FUNAE')</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            background-color: #f4f4f5;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f5;
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            /* background-color: #F15F22; */
            padding: 32px 40px;
            text-align: center;
        }
        .email-header-logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 16px;
        }
        .email-header h1 {
            color: #000000;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }
        .email-header-icon {
            font-size: 32px;
            margin-bottom: 8px;
            display: none;
        }
        .email-content {
            padding: 40px;
            color: #374151;
            font-size: 16px;
            line-height: 1.7;
        }
        .email-content p {
            margin: 0 0 16px 0;
        }
        .reference-box {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 2px solid #F15F22;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .reference-label {
            font-size: 12px;
            font-weight: 600;
            color: #9a3412;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .reference-number {
            font-size: 28px;
            font-weight: 800;
            color: #F15F22;
            letter-spacing: 2px;
        }
        .info-box {
            background-color: #f9fafb;
            border-left: 4px solid #F15F22;
            border-radius: 0 8px 8px 0;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .info-box-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            font-size: 15px;
        }
        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #4b5563;
        }
        .info-box li {
            margin-bottom: 8px;
        }
        .detail-box {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .detail-row {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 500;
        }
        .success-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #10b981;
            border-radius: 10px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .success-box-title {
            font-size: 18px;
            font-weight: 700;
            color: #065f46;
            margin-bottom: 8px;
        }
        .success-box-text {
            color: #047857;
            font-size: 14px;
        }
        .warning-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #ef4444;
            border-radius: 10px;
            padding: 24px;
            margin: 24px 0;
        }
        .warning-box-title {
            font-size: 16px;
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 8px;
        }
        .warning-box-text {
            color: #b91c1c;
            font-size: 14px;
        }
        .technician-box {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 1px solid #F15F22;
            border-radius: 10px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .technician-box-title {
            font-size: 12px;
            font-weight: 600;
            color: #9a3412;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .technician-name {
            font-size: 20px;
            font-weight: 700;
            color: #c2410c;
            margin-bottom: 4px;
        }
        .technician-email {
            font-size: 14px;
            color: #ea580c;
        }
        .status-change {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 24px;
            margin: 24px 0;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-old {
            background-color: #f3f4f6;
            color: #6b7280;
            text-decoration: line-through;
        }
        .status-new {
            background: linear-gradient(135deg, #F15F22 0%, #e54d10 100%);
            color: #ffffff;
        }
        .status-arrow {
            display: inline-block;
            font-size: 24px;
            color: #F15F22;
            margin: 0 16px;
            vertical-align: middle;
        }
        .comment-box {
            background-color: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            margin: 24px 0;
        }
        .comment-meta {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .comment-author {
            font-weight: 600;
            color: #374151;
        }
        .comment-text {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            color: #374151;
            font-size: 15px;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 32px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #F15F22 0%, #e54d10 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 14px rgba(241, 95, 34, 0.4);
        }
        .button-success {
            background: linear-gradient(135deg, #16A34A 0%, #15803d 100%);
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.4);
        }
        .signature {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .signature-text {
            color: #6b7280;
            font-size: 15px;
        }
        .signature-name {
            font-weight: 700;
            color: #F15F22;
            font-size: 16px;
        }
        .email-footer {
            background-color: #1f2937;
            padding: 32px 40px;
            text-align: center;
        }
        .footer-text {
            color: #9ca3af;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }
        .footer-brand {
            color: #F15F22;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <img src="{{ asset('images/Logotipo-scaled.png') }}" alt="FUNAE Logo" class="email-header-logo">
                {{-- <h1>@yield('title', 'FUNAE')</h1> --}}
            </div>
            @yield('content')

            <div class="email-footer">
                <p class="footer-text">
                    Esta é uma mensagem automática. Por favor não responda a este email.<br>
                    © {{ date('Y') }} <span class="footer-brand">FUNAE</span> - Fundo Nacional de Energia
                </p>
            </div>
        </div>
    </div>
</body>
</html>
