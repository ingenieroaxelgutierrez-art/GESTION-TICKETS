// ===============================
// AJAX GLOBAL FUNCTION (MEJORADA)
// ===============================
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ||
           document.querySelector('input[name="csrf_token"]')?.value || '';
}

/**
 * ajax(url, data, callback, method = 'POST', expectJson = true)
 * data: FormData | Object | null
 * Maneja GET, POST, PUT, DELETE con validación CSRF automática
 */
function ajax(url, data = null, callback = () => {}, method = 'POST', expectJson = true) {
    let body = null;
    let headers = { 
        'X-Requested-With': 'XMLHttpRequest'
    };

    const csrf = getCsrfToken();
    if (csrf) {
        headers['X-CSRF-Token'] = csrf;
    }

    // Preparar datos según el tipo de método
    if (method === 'GET' && data && typeof data === 'object') {
        const qs = new URLSearchParams(data).toString();
        url = url + (url.includes('?') ? '&' : '?') + qs;
    } 
    else if (data instanceof FormData) {
        body = data;
        if (csrf) body.append('csrf_token', csrf);
    } 
    else if (data && typeof data === 'object') {
        body = new FormData();
        for (const k in data) body.append(k, data[k]);
        if (csrf) body.append('csrf_token', csrf);
    } else {
        body = data;
    }

    fetch(url, {
        method: method,
        body: method === 'GET' ? null : body,
        credentials: 'include',
        headers: headers
    })
    .then(async res => {
        const contentType = res.headers.get('content-type') || '';
        let responseData;
        let rawText = '';
    
        try {
            rawText = await res.text();
        
            // Intenta parsear como JSON si está disponible o si expectJson=true
            if (expectJson && rawText) {
                try {
                    // Elimina BOM si existe
                    const cleanText = rawText.replace(/^\ufeff/, '');
                    responseData = JSON.parse(cleanText);
                } catch (parseErr) {
                    // Si no es JSON válido pero expectJson=true, retorna error
                    responseData = { success: false, error: 'Respuesta inválida del servidor', raw: rawText };
                }
            } else {
                responseData = rawText;
            }
        } catch (e) {
            console.error('[AJAX TEXT ERROR]:', e);
            responseData = { success: false, error: 'Error al procesar respuesta' };
        }

        if (!res.ok) {
            // Llamar al callback con un objeto de error
            const errObj = (responseData && typeof responseData === 'object') 
                ? responseData 
                : { success: false, error: 'HTTP ' + res.status, status: res.status };
            try { callback(errObj); } catch (e) { console.error('Error en callback:', e); }
            return;
        }

        try { callback(responseData); } catch (e) { console.error('Error en callback:', e); }
    })
    .catch(err => {
        console.error("[AJAX ERROR]:", err);
        try { callback({ success: false, error: err.message }); } catch (e) {}
    });
}

