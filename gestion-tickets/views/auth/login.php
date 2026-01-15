<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="/gestion-tickets/assets/css/login-animated.css">

<div class="animated-login-page">
    <div class="login-content-wrapper">
        <!-- Personajes Animados -->
        <div class="characters-section">
            <svg id="charactersSvg" width="100%" height="400" viewBox="0 0 350 350">
                <!-- Semicírculo Naranja -->
                <g id="orangeSemicircle" class="character">
                    <path class="body" d="M 50 300 A 60 60 0 0 1 170 300 Z" fill="#FF8C42"/>
                    <g class="sweat-drops" style="display:none;">
                        <ellipse cx="75" cy="255" rx="3" ry="4" fill="#6BB6FF" opacity="0.7"/>
                    </g>
                    <g class="eyes">
                        <ellipse class="eye-white left" cx="85" cy="270" rx="12" ry="14" fill="white"/>
                        <ellipse class="eye-white right" cx="135" cy="270" rx="12" ry="14" fill="white"/>
                        <circle class="pupil left" cx="85" cy="270" r="6" fill="#333"/>
                        <circle class="pupil right" cx="135" cy="270" r="6" fill="#333"/>
                        <circle class="shine left" cx="87" cy="268" r="2" fill="white" opacity="0.8"/>
                        <circle class="shine right" cx="137" cy="268" r="2" fill="white" opacity="0.8"/>
                    </g>
                    <g class="eyes-closed" style="display:none;">
                        <line class="closed-left" x1="73" y1="270" x2="97" y2="270" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                        <line class="closed-right" x1="123" y1="270" x2="147" y2="270" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                    </g>
                    <g class="eyebrows">
                        <path class="brow left" d="" stroke="#333" stroke-width="2.5" fill="none"/>
                        <path class="brow right" d="" stroke="#333" stroke-width="2.5" fill="none"/>
                    </g>
                    <path class="mouth" d="M 90 285 Q 110 295 130 285" stroke="#333" stroke-width="2.5" fill="none"/>
                    <g class="hearts" style="display:none;">
                        <text x="50" y="270" font-size="14" fill="#FF6B9D" opacity="0.8">♥</text>
                        <text x="155" y="265" font-size="12" fill="#FF6B9D" opacity="0.8">♥</text>
                    </g>
                </g>

                <!-- Rectángulo Morado -->
                <g id="purpleRectangle" class="character">
                    <rect class="body" x="90" y="100" width="100" height="120" fill="#7B68EE" rx="8"/>
                    <g class="stars" style="display:none;">
                        <text x="75" y="95" font-size="16" fill="#FFD700">★</text>
                        <text x="185" y="100" font-size="14" fill="#FFD700">★</text>
                        <text x="80" y="225" font-size="12" fill="#FFD700">✨</text>
                    </g>
                    <g class="blush" style="display:none;">
                        <ellipse cx="105" cy="170" rx="8" ry="6" fill="#FF6B9D" opacity="0.5"/>
                        <ellipse cx="175" cy="170" rx="8" ry="6" fill="#FF6B9D" opacity="0.5"/>
                    </g>
                    <g class="eyes">
                        <ellipse class="eye-white left" cx="125" cy="155" rx="14" ry="16" fill="white"/>
                        <ellipse class="eye-white right" cx="155" cy="155" rx="14" ry="16" fill="white"/>
                        <circle class="pupil left" cx="125" cy="155" r="7" fill="#333"/>
                        <circle class="pupil right" cx="155" cy="155" r="7" fill="#333"/>
                        <circle class="shine left" cx="127" cy="153" r="2.5" fill="white" opacity="0.9"/>
                        <circle class="shine right" cx="157" cy="153" r="2.5" fill="white" opacity="0.9"/>
                    </g>
                    <g class="eyes-closed" style="display:none;">
                        <line class="closed-left" x1="115" y1="155" x2="135" y2="155" stroke="#333" stroke-width="4" stroke-linecap="round"/>
                        <line class="closed-right" x1="145" y1="155" x2="165" y2="155" stroke="#333" stroke-width="4" stroke-linecap="round"/>
                    </g>
                    <g class="eyebrows">
                        <path class="brow left" d="" stroke="#333" stroke-width="2.5" fill="none"/>
                        <path class="brow right" d="" stroke="#333" stroke-width="2.5" fill="none"/>
                    </g>
                    <path class="mouth" d="M 120 180 Q 140 190 160 180" stroke="#333" stroke-width="2.5" fill="none"/>
                </g>

                <!-- Rectángulo Negro -->
                <g id="blackRectangle" class="character">
                    <rect class="body" x="170" y="160" width="70" height="100" fill="#2C2C2C" rx="6"/>
                    <g class="notes" style="display:none;">
                        <text x="155" y="155" font-size="14" fill="#FFD700">♪</text>
                        <text x="235" y="165" font-size="16" fill="#FFD700">♫</text>
                    </g>
                    <g class="eyes">
                        <ellipse class="eye-white left" cx="192" cy="200" rx="11" ry="13" fill="white"/>
                        <ellipse class="eye-white right" cx="218" cy="200" rx="11" ry="13" fill="white"/>
                        <circle class="pupil left" cx="192" cy="200" r="6" fill="#333"/>
                        <circle class="pupil right" cx="218" cy="200" r="6" fill="#333"/>
                        <circle class="shine left" cx="194" cy="198" r="2" fill="white" opacity="0.9"/>
                        <circle class="shine right" cx="220" cy="198" r="2" fill="white" opacity="0.9"/>
                    </g>
                    <g class="eyes-closed" style="display:none;">
                        <line class="closed-left" x1="181" y1="200" x2="203" y2="200" stroke="#FFF" stroke-width="3" stroke-linecap="round"/>
                        <line class="closed-right" x1="207" y1="200" x2="229" y2="200" stroke="#FFF" stroke-width="3" stroke-linecap="round"/>
                    </g>
                    <g class="eyebrows">
                        <path class="brow left" d="" stroke="#FFF" stroke-width="2.5" fill="none"/>
                        <path class="brow right" d="" stroke="#FFF" stroke-width="2.5" fill="none"/>
                    </g>
                    <path class="mouth" d="M 185 225 Q 205 235 225 225" stroke="#FFF" stroke-width="2" fill="none"/>
                </g>

                <!-- Pájaro Amarillo -->
                <g id="yellowBird" class="character">
                    <rect class="body" x="240" y="180" width="70" height="90" fill="#FFD700" rx="35" ry="45"/>
                    <polygon class="beak" points="310,215 340,220 310,225" fill="#FF8C42"/>
                    <g class="exclamation" style="display:none;">
                        <text x="305" y="195" font-size="16" fill="#FF6B9D" opacity="0.8">!</text>
                    </g>
                    <g class="eyes">
                        <ellipse class="eye-white left" cx="260" cy="210" rx="11" ry="13" fill="white"/>
                        <ellipse class="eye-white right" cx="285" cy="210" rx="11" ry="13" fill="white"/>
                        <circle class="pupil left" cx="260" cy="210" r="6" fill="#333"/>
                        <circle class="pupil right" cx="285" cy="210" r="6" fill="#333"/>
                        <circle class="shine left" cx="262" cy="208" r="2" fill="white" opacity="0.9"/>
                        <circle class="shine right" cx="287" cy="208" r="2" fill="white" opacity="0.9"/>
                    </g>
                    <g class="eyes-closed" style="display:none;">
                        <line class="closed-left" x1="249" y1="210" x2="271" y2="210" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                        <line class="closed-right" x1="274" y1="210" x2="296" y2="210" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                    </g>
                    <g class="eyebrows">
                        <path class="brow left" d="" stroke="#333" stroke-width="2.5" fill="none"/>
                        <path class="brow right" d="" stroke="#333" stroke-width="2.5" fill="none"/>
                    </g>
                    <path class="mouth" d="M 255 235 Q 272 245 290 235" stroke="#333" stroke-width="2" fill="none"/>
                </g>
            </svg>
        </div>

        <!-- Formulario de Login -->
        <div class="login-form-section">
            <div class="login-card-animated">
                <div class="login-header">
                    <div class="login-icon">
                        <img src="/gestion-tickets/assets/img/favicon_rideco.png" alt="Logo Rideco">
                    </div>
                    <h2 class="login-title">Bienvenido!</h2>
                    <p class="login-subtitle">Por favor ingresa tus datos</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-error animated-alert">
                        ❌ <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($success)): ?>
                    <div class="alert alert-success animated-alert">
                        ✅ <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form action="/gestion-tickets/login" method="POST" id="loginForm" class="login-form">
                    <?php
                    if (function_exists('csrf_field')) {
                        echo csrf_field();
                    } else {
                        echo '<input type="hidden" name="csrf_token" value="">';
                    }
                    ?>

                    <div class="form-group-animated">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="user@example.com"
                            required
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-group-animated">
                        <label for="password">Contraseña</label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="eye-icon fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <span class="btn-text">Iniciar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/gestion-tickets/assets/js/login-animated.js"></script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
// Manejo del formulario de login: usa la función ajax incluida en footer
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);

    const btn = document.getElementById('loginBtn');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="loading"></span> Iniciando...';
    btn.disabled = true;

    // Construir URL correcta
    const url = BASE_URL + '/login';

    ajax(url, fd, function(res) {
        console.log('[LOGIN DEBUG] Response:', res);
        
        if (res && res.success === true) {
            console.log('[LOGIN DEBUG] Login success, redirecting to:', res.redirect);
            window.location.href = res.redirect || (BASE_URL + '/dashboard');
        } else {
            const errorMsg = (res && res.error) ? res.error : 'Error al iniciar sesión (respuesta vacía o inválida)';
            console.error('[LOGIN DEBUG] Login failed:', errorMsg, 'Response object:', res);
            alert(errorMsg);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }, 'POST', true);
});
</script>