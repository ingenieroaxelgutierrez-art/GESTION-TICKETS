// ====================================
// LOGIN ANIMADO - JAVASCRIPT
// ====================================

(function() {
    'use strict';

    // Estado
    let mousePos = { x: 175, y: 175 };
    let isPasswordFocused = false;
    let isTyping = false;
    let showPassword = false;
    let idleTime = 0;
    let mouseNearForm = false;

    // Referencias DOM
    const charactersSvg = document.getElementById('charactersSvg');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    // Personajes
    const characters = {
        orange: document.getElementById('orangeSemicircle'),
        purple: document.getElementById('purpleRectangle'),
        black: document.getElementById('blackRectangle'),
        bird: document.getElementById('yellowBird')
    };

    // ====================================
    // UTILIDADES
    // ====================================

    function calculateEyePosition(charX, charY, maxDistance = 12) {
        const rect = charactersSvg.getBoundingClientRect();
        const svgX = (mousePos.x / window.innerWidth) * 350;
        const svgY = (mousePos.y / window.innerHeight) * 400;
        
        const dx = svgX - charX;
        const dy = svgY - charY;
        const angle = Math.atan2(dy, dx);
        const distance = Math.min(Math.sqrt(dx * dx + dy * dy) / 20, maxDistance);
        
        return {
            x: Math.cos(angle) * distance,
            y: Math.sin(angle) * distance
        };
    }

    // Guardar posiciones base originales de pupilas y brillos
    const originalPositions = {};
    
    function initializePositions() {
        Object.entries(characters).forEach(([key, character]) => {
            if (!character) return;
            
            originalPositions[key] = {
                pupils: [],
                shines: []
            };
            
            character.querySelectorAll('.pupil').forEach(pupil => {
                originalPositions[key].pupils.push({
                    cx: parseFloat(pupil.getAttribute('cx')),
                    cy: parseFloat(pupil.getAttribute('cy'))
                });
            });
            
            character.querySelectorAll('.shine').forEach(shine => {
                originalPositions[key].shines.push({
                    cx: parseFloat(shine.getAttribute('cx')),
                    cy: parseFloat(shine.getAttribute('cy'))
                });
            });
        });
    }
    
    function updatePupils(character, charX, charY, maxDist = 8) {
        const shouldCoverEyes = isPasswordFocused && !showPassword;
        
        if (!character) return;
        
        const eyesGroup = character.querySelector('.eyes');
        const eyesClosedGroup = character.querySelector('.eyes-closed');
        const charKey = Object.keys(characters).find(key => characters[key] === character);
        
        if (shouldCoverEyes && character.id !== 'yellowBird') {
            // Cerrar ojos: ocultar ojos abiertos, mostrar líneas cerradas
            if (eyesGroup) eyesGroup.style.display = 'none';
            if (eyesClosedGroup) eyesClosedGroup.style.display = 'block';
            showBlush(character, true);
        } else {
            // Ojos abiertos
            if (eyesGroup) eyesGroup.style.display = 'block';
            if (eyesClosedGroup) eyesClosedGroup.style.display = 'none';
            showBlush(character, false);
            
            // Calcular posición de ojos
            const eyePos = (character.id === 'yellowBird' && isPasswordFocused && !showPassword) 
                ? calculateEyePosition(charX - 60, charY, maxDist)
                : calculateEyePosition(charX, charY, maxDist);
            
            // Usar posiciones base guardadas
            if (originalPositions[charKey] && eyesGroup) {
                const pupils = eyesGroup.querySelectorAll('.pupil');
                const shines = eyesGroup.querySelectorAll('.shine');
                
                pupils.forEach((pupil, index) => {
                    const basePos = originalPositions[charKey].pupils[index];
                    if (basePos) {
                        pupil.setAttribute('cx', basePos.cx + eyePos.x);
                        pupil.setAttribute('cy', basePos.cy + eyePos.y);
                    }
                });
                
                shines.forEach((shine, index) => {
                    const basePos = originalPositions[charKey].shines[index];
                    if (basePos) {
                        shine.setAttribute('cx', basePos.cx + eyePos.x);
                        shine.setAttribute('cy', basePos.cy + eyePos.y);
                    }
                });
            }
        }
    }

    function showBlush(character, show) {
        const blush = character.querySelector('.blush');
        if (blush) {
            blush.style.display = show ? 'block' : 'none';
        }
    }

    function updateMouth(character, type) {
        const mouth = character.querySelector('.mouth');
        if (!mouth) return;
        
        const isWhiteStroke = character.id === 'blackRectangle';
        const strokeColor = isWhiteStroke ? '#FFF' : '#333';
        
        let pathData = '';
        
        switch(type) {
            case 'error':
                if (character.id === 'orangeSemicircle') {
                    pathData = 'M 90 290 Q 110 285 130 290';
                } else if (character.id === 'purpleRectangle') {
                    pathData = 'M 120 185 Q 140 180 160 185';
                } else if (character.id === 'blackRectangle') {
                    pathData = 'M 185 230 Q 205 225 225 230';
                } else if (character.id === 'yellowBird') {
                    pathData = 'M 255 240 Q 272 235 290 240';
                }
                break;
                
            case 'success':
                if (character.id === 'orangeSemicircle') {
                    pathData = 'M 85 285 Q 110 300 135 285';
                } else if (character.id === 'purpleRectangle') {
                    pathData = 'M 115 180 Q 140 196 165 180';
                } else if (character.id === 'blackRectangle') {
                    pathData = 'M 182 225 Q 205 242 228 225';
                } else if (character.id === 'yellowBird') {
                    pathData = 'M 252 235 Q 272 250 292 235';
                }
                break;
                
            case 'nervous':
                if (character.id === 'orangeSemicircle') {
                    mouth.setAttribute('d', '');
                    mouth.parentElement.innerHTML += '<ellipse class="mouth-nervous" cx="110" cy="288" rx="6" ry="4" fill="#333"/>';
                    return;
                } else if (character.id === 'purpleRectangle') {
                    mouth.setAttribute('d', '');
                    mouth.parentElement.innerHTML += '<ellipse class="mouth-nervous" cx="140" cy="183" rx="8" ry="5" fill="#333"/>';
                    return;
                }
                break;
                
            default:
                if (character.id === 'orangeSemicircle') {
                    pathData = 'M 90 285 Q 110 295 130 285';
                } else if (character.id === 'purpleRectangle') {
                    pathData = 'M 120 180 Q 140 190 160 180';
                } else if (character.id === 'blackRectangle') {
                    pathData = 'M 185 225 Q 205 235 225 225';
                } else if (character.id === 'yellowBird') {
                    pathData = 'M 255 235 Q 272 245 290 235';
                }
        }
        
        mouth.setAttribute('d', pathData);
        mouth.setAttribute('stroke', strokeColor);
        
        // Limpiar bocas nerviosas
        const nervousMouth = character.querySelectorAll('.mouth-nervous');
        nervousMouth.forEach(m => m.remove());
    }

    function updateEyebrows(character, type) {
        const brows = character.querySelectorAll('.brow');
        if (!brows.length) return;
        
        const isWhiteStroke = character.id === 'blackRectangle';
        const strokeColor = isWhiteStroke ? '#FFF' : '#333';
        
        let leftPath = '', rightPath = '';
        
        switch(type) {
            case 'error':
                if (character.id === 'orangeSemicircle') {
                    leftPath = 'M 73 260 Q 85 258 97 260';
                    rightPath = 'M 123 260 Q 135 258 147 260';
                } else if (character.id === 'purpleRectangle') {
                    leftPath = 'M 115 143 Q 125 140 135 143';
                    rightPath = 'M 145 143 Q 155 140 165 143';
                } else if (character.id === 'blackRectangle') {
                    leftPath = 'M 181 188 Q 192 185 203 188';
                    rightPath = 'M 207 188 Q 218 185 229 188';
                } else if (character.id === 'yellowBird') {
                    leftPath = 'M 249 198 Q 260 195 271 198';
                    rightPath = 'M 274 198 Q 285 195 296 198';
                }
                break;
                
            case 'success':
                if (character.id === 'orangeSemicircle') {
                    leftPath = 'M 73 262 Q 85 258 97 262';
                    rightPath = 'M 123 262 Q 135 258 147 262';
                } else if (character.id === 'purpleRectangle') {
                    leftPath = 'M 115 147 Q 125 143 135 147';
                    rightPath = 'M 145 147 Q 155 143 165 147';
                } else if (character.id === 'blackRectangle') {
                    leftPath = 'M 181 192 Q 192 188 203 192';
                    rightPath = 'M 207 192 Q 218 188 229 192';
                } else if (character.id === 'yellowBird') {
                    leftPath = 'M 249 202 Q 260 197 271 202';
                    rightPath = 'M 274 202 Q 285 197 296 202';
                }
                break;
                
            case 'sneaky':
                if (character.id === 'yellowBird') {
                    leftPath = 'M 249 200 Q 260 197 271 200';
                    rightPath = 'M 274 200 Q 285 197 296 200';
                }
                break;
                
            default:
                leftPath = '';
                rightPath = '';
        }
        
        if (brows[0]) {
            brows[0].setAttribute('d', leftPath);
            brows[0].setAttribute('stroke', strokeColor);
        }
        if (brows[1]) {
            brows[1].setAttribute('d', rightPath);
            brows[1].setAttribute('stroke', strokeColor);
        }
    }

    function showDecoration(character, decorationType, show) {
        const decorations = {
            'hearts': character.querySelector('.hearts'),
            'stars': character.querySelector('.stars'),
            'notes': character.querySelector('.notes'),
            'sweat': character.querySelector('.sweat-drops'),
            'exclamation': character.querySelector('.exclamation')
        };
        
        if (decorations[decorationType]) {
            decorations[decorationType].style.display = show ? 'block' : 'none';
        }
    }

    // ====================================
    // ACTUALIZACIONES DE PERSONAJES
    // ====================================

    function updateCharacters() {
        // Naranja
        updatePupils(characters.orange, 100, 280, 8);
        
        // Morado
        updatePupils(characters.purple, 140, 170, 7);
        
        // Negro
        updatePupils(characters.black, 200, 210, 7);
        
        // Pájaro (siempre mira)
        updatePupils(characters.bird, 270, 220, 8);
        
        // Mostrar signos de exclamación en el pájaro cuando escribe contraseña
        if (isPasswordFocused && !showPassword) {
            showDecoration(characters.bird, 'exclamation', true);
            updateEyebrows(characters.bird, 'sneaky');
        } else {
            showDecoration(characters.bird, 'exclamation', false);
        }
    }

    function setEmotion(emotion) {
        Object.values(characters).forEach(char => {
            updateMouth(char, emotion);
            updateEyebrows(char, emotion);
            
            if (emotion === 'success') {
                showDecoration(char, 'hearts', char.id === 'orangeSemicircle');
                showDecoration(char, 'stars', char.id === 'purpleRectangle');
                showDecoration(char, 'notes', char.id === 'blackRectangle');
                
                // Animación de salto
                char.style.animation = 'jump 0.5s ease-in-out';
                setTimeout(() => {
                    char.style.animation = '';
                }, 500);
            } else if (emotion === 'error') {
                // Animación de sacudida
                char.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    char.style.animation = '';
                }, 500);
            } else {
                showDecoration(char, 'hearts', false);
                showDecoration(char, 'stars', false);
                showDecoration(char, 'notes', false);
            }
        });
    }

    function setNervous(nervous) {
        if (nervous) {
            showDecoration(characters.orange, 'sweat', true);
            updateMouth(characters.orange, 'nervous');
        } else {
            showDecoration(characters.orange, 'sweat', false);
            updateMouth(characters.orange, 'normal');
        }
    }

    // ====================================
    // PARPADEO ALEATORIO
    // ====================================

    function randomBlink() {
        const charArray = Object.values(characters);
        const randomChar = charArray[Math.floor(Math.random() * charArray.length)];
        
        if (!randomChar) return;
        
        const eyesGroup = randomChar.querySelector('.eyes');
        const eyesClosedGroup = randomChar.querySelector('.eyes-closed');
        
        // Cerrar ojos
        if (eyesGroup) eyesGroup.style.display = 'none';
        if (eyesClosedGroup) eyesClosedGroup.style.display = 'block';
        
        setTimeout(() => {
            // Abrir ojos
            if (eyesGroup) eyesGroup.style.display = 'block';
            if (eyesClosedGroup) eyesClosedGroup.style.display = 'none';
        }, 150);
    }

    setInterval(randomBlink, 3000);

    // ====================================
    // EVENT LISTENERS
    // ====================================

    // Seguimiento del mouse
    window.addEventListener('mousemove', (e) => {
        mousePos = { x: e.clientX, y: e.clientY };
        mouseNearForm = e.clientX > window.innerWidth / 2;
        
        // Si el mouse está cerca del formulario, personajes miran con interés
        if (mouseNearForm) {
            Object.values(characters).forEach(char => {
                if (char && char.id !== 'yellowBird') {
                    char.style.transform = 'translateX(3px)';
                }
            });
        } else {
            Object.values(characters).forEach(char => {
                if (char) {
                    char.style.transform = '';
                }
            });
        }
        
        updateCharacters();
    });

    // Password focus
    if (passwordInput) {
        passwordInput.addEventListener('focus', () => {
            isPasswordFocused = true;
            setNervous(true);
            
            // El pájaro se emociona
            if (characters.bird) {
                const beak = characters.bird.querySelector('.beak');
                if (beak) {
                    beak.style.transform = 'translateX(5px)';
                    setTimeout(() => {
                        beak.style.transform = '';
                    }, 300);
                }
            }
            
            updateCharacters();
        });
        
        passwordInput.addEventListener('blur', () => {
            isPasswordFocused = false;
            setTimeout(() => {
                setNervous(false);
                updateCharacters();
            }, 500);
        });
    }

    // Typing
    [emailInput, passwordInput].forEach(input => {
        if (input) {
            input.addEventListener('input', () => {
                isTyping = true;
                
                // Reacción cuando están escribiendo
                Object.values(characters).forEach(char => {
                    if (char && char.id !== 'yellowBird') {
                        const body = char.querySelector('.body');
                        if (body) {
                            body.style.transform = 'scale(1.02)';
                            setTimeout(() => {
                                body.style.transform = '';
                            }, 200);
                        }
                    }
                });
                
                setTimeout(() => {
                    isTyping = false;
                }, 500);
            });
        }
    });

    // Toggle password visibility
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            showPassword = !showPassword;
            passwordInput.type = showPassword ? 'text' : 'password';
            const icon = togglePasswordBtn.querySelector('.eye-icon');
            if (showPassword) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            
            // Reacción al mostrar/ocultar contraseña
            if (showPassword) {
                // Todos abren los ojos con sorpresa
                Object.values(characters).forEach(char => {
                    if (char) {
                        const eyeWhites = char.querySelectorAll('.eye-white');
                        eyeWhites.forEach(eye => {
                            const ry = eye.getAttribute('ry');
                            eye.setAttribute('ry', parseFloat(ry) * 1.3);
                            setTimeout(() => {
                                eye.setAttribute('ry', ry);
                            }, 300);
                        });
                    }
                });
            } else {
                // Se cubren los ojos (excepto el pájaro)
                Object.values(characters).forEach(char => {
                    if (char && char.id !== 'yellowBird') {
                        const body = char.querySelector('.body');
                        if (body) {
                            body.style.transform = 'rotate(-2deg)';
                            setTimeout(() => {
                                body.style.transform = '';
                            }, 300);
                        }
                    }
                });
            }
            
            updateCharacters();
        });
    }

    // Form submit
    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const originalHtml = loginBtn.innerHTML;
            
            loginBtn.innerHTML = '<span class="btn-text">Iniciando...</span>';
            loginBtn.disabled = true;
            loginBtn.classList.add('loading');

            // Usar la función ajax si está disponible
            if (typeof ajax === 'function') {
                const url = BASE_URL + '/login';
                
                ajax(url, formData, function(res) {
                    console.log('[LOGIN DEBUG] Response:', res);
                    
                    if (res && res.success === true) {
                        console.log('[LOGIN DEBUG] Login success!');
                        setEmotion('success');
                        
                        setTimeout(() => {
                            window.location.href = res.redirect || (BASE_URL + '/dashboard');
                        }, 1000);
                    } else {
                        const errorMsg = (res && res.error) ? res.error : 'Error al iniciar sesión';
                        console.error('[LOGIN DEBUG] Login failed:', errorMsg);
                        
                        setEmotion('error');
                        
                        setTimeout(() => {
                            alert(errorMsg);
                            loginBtn.innerHTML = originalHtml;
                            loginBtn.disabled = false;
                            loginBtn.classList.remove('loading');
                            setEmotion('normal');
                        }, 1000);
                    }
                }, 'POST', true);
            } else {
                // Fallback: submit normal
                this.submit();
            }
        });
    }

    // Inicializar
    initializePositions();
    updateCharacters();

})();
