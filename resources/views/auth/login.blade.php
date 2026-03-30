<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión — SGC F&C Chile SPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 40px 36px 36px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }

        .login-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .login-logo-icon {
            width: 44px;
            height: 44px;
            background: var(--navy);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .login-logo-name { font-size: 1rem;  font-weight: 700; color: var(--navy); line-height: 1.2; }
        .login-logo-sub  { font-size: .72rem; color: var(--text-muted); }

        .login-title {
            font-size: .82rem;
            color: var(--text-muted);
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-icon-wrap .form-control {
            padding-left: 38px;
        }

        .btn-primary { margin-top: 6px; letter-spacing: .04em; }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: .75rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="login-card">

    {{-- Logo --}}
    <div class="login-logo">
        <div class="login-logo-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <rect x="3"  y="3" width="4" height="18" fill="#fff"/>
                <rect x="10" y="3" width="4" height="18" fill="#fff"/>
                <rect x="17" y="3" width="4" height="18" fill="#fff"/>
            </svg>
        </div>
        <div>
            <div class="login-logo-name">F&C Chile SPA</div>
            <div class="login-logo-sub">Ingeniería &amp; Construcción</div>
        </div>
    </div>

    <div class="login-title">Control y Gestión Transversal</div>

    {{-- Mensaje de sesión cerrada --}}
    @if (session('mensaje'))
        <div class="alert alert-success">{{ session('mensaje') }}</div>
    @endif

    {{-- Error de credenciales --}}
    @if ($errors->has('credenciales'))
        <div class="alert alert-danger">{{ $errors->first('credenciales') }}</div>
    @endif

    {{-- Formulario --}}
    <form method="POST" action="{{ route('login.post') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">Correo corporativo</label>
            <div class="input-icon-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="usuario@fycchilespa.cl"
                    autocomplete="email"
                    required
                >
            </div>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Contraseña --}}
        <div class="form-group">
            <label class="form-label" for="clave">Contraseña</label>
            <div class="input-icon-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <input
                    id="clave"
                    type="password"
                    name="clave"
                    class="form-control @error('clave') is-invalid @enderror"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>
            @error('clave')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            Ingresar
        </button>
    </form>

    <div class="login-footer">
        Sistema de Gestión Corporativo &copy; {{ date('Y') }}
    </div>

</div>

</body>
</html>
