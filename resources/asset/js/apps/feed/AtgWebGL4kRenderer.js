/**
 * AtgWebGL4kRenderer.js
 *
 * Principal-grade WebGL2 AMD FidelityFX Contrast-Adaptive Sharpening (CAS)
 * post-processor for HTML5 <video> elements.
 *
 * - Works natively on iOS Safari 17+/18+ and Desktop Chrome/Firefox/Safari.
 * - Sharpens soft/low-res edges while suppressing JPEG/H.264 block & mosquito noise.
 * - Zero extra server storage; zero CPU pixel loops (100% GPU fragment shader).
 * - Automatic CORS/SecurityError guard: silently disables overlay on untrusted cross-origin streams.
 */

const VERTEX_SHADER_SRC = `#version 300 es
in vec2 a_pos;
in vec2 a_uv;
out vec2 v_uv;
void main() {
    v_uv = a_uv;
    gl_Position = vec4(a_pos, 0.0, 1.0);
}`;

const CAS_FRAGMENT_SHADER_SRC = `#version 300 es
precision highp float;
uniform sampler2D u_tex;
uniform vec2 u_texelSize;
uniform float u_sharpness;
in vec2 v_uv;
out vec4 outColor;

void main() {
    vec3 c = texture(u_tex, v_uv).rgb;
    vec3 n = texture(u_tex, v_uv + vec2(0.0, -u_texelSize.y)).rgb;
    vec3 s = texture(u_tex, v_uv + vec2(0.0,  u_texelSize.y)).rgb;
    vec3 w = texture(u_tex, v_uv + vec2(-u_texelSize.x, 0.0)).rgb;
    vec3 e = texture(u_tex, v_uv + vec2( u_texelSize.x, 0.0)).rgb;

    vec3 minRGB = min(c, min(min(n, s), min(w, e)));
    vec3 maxRGB = max(c, max(max(n, s), max(w, e)));

    // FidelityFX CAS adaptive amplification: suppresses noise/blocks, sharpens soft edges
    vec3 rcpMax = 1.0 / max(maxRGB, vec3(0.0001));
    vec3 amp = clamp(min(minRGB, 2.0 - maxRGB) * rcpMax, 0.0, 1.0);
    amp = sqrt(amp);

    float peak = -1.0 / mix(8.0, 5.0, clamp(u_sharpness, 0.0, 1.0));
    vec3 weight = amp * peak;
    vec3 rcpWeight = 1.0 / (1.0 + 4.0 * weight);

    vec3 sharpened = clamp((c + weight * (n + s + w + e)) * rcpWeight, 0.0, 1.0);
    outColor = vec4(sharpened, 1.0);
}`;

function compileShader(gl, type, source) {
    const shader = gl.createShader(type);
    if (!shader) return null;
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
        gl.deleteShader(shader);
        return null;
    }
    return shader;
}

/**
 * Attach a WebGL2 FidelityFX CAS GPU enhancer to a <video> and target <canvas>.
 * Returns a cleanup function, or null if WebGL2 is unavailable (e.g. jsdom).
 *
 * @param {HTMLVideoElement} video
 * @param {HTMLCanvasElement} canvas
 * @param {Object} [opts]
 * @param {function(): void} [opts.onFallback] - Called if CORS or GPU failure requires hiding canvas
 * @returns {(() => void) | null}
 */
export function attachWebGL4kCas(video, canvas, opts = {}) {
    if (!video || !canvas || typeof window === 'undefined') return null;

    let gl = null;
    try {
        gl = canvas.getContext('webgl2', {
            alpha: false,
            antialias: false,
            depth: false,
            stencil: false,
            powerPreference: 'high-performance',
            preserveDrawingBuffer: false,
        });
    } catch (_) {
        return null;
    }
    if (!gl) return null;

    const vs = compileShader(gl, gl.VERTEX_SHADER, VERTEX_SHADER_SRC);
    const fs = compileShader(gl, gl.FRAGMENT_SHADER, CAS_FRAGMENT_SHADER_SRC);
    if (!vs || !fs) {
        if (vs) gl.deleteShader(vs);
        if (fs) gl.deleteShader(fs);
        return null;
    }

    const program = gl.createProgram();
    if (!program) return null;
    gl.attachShader(program, vs);
    gl.attachShader(program, fs);
    gl.linkProgram(program);
    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        gl.deleteProgram(program);
        return null;
    }

    gl.useProgram(program);

    // Fullscreen quad (pos x,y + uv u,v)
    const quadData = new Float32Array([
        -1, -1, 0, 1,
         1, -1, 1, 1,
        -1,  1, 0, 0,
         1,  1, 1, 0,
    ]);
    const vbo = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, vbo);
    gl.bufferData(gl.ARRAY_BUFFER, quadData, gl.STATIC_DRAW);

    const aPos = gl.getAttribLocation(program, 'a_pos');
    const aUv = gl.getAttribLocation(program, 'a_uv');
    gl.enableVertexAttribArray(aPos);
    gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 16, 0);
    gl.enableVertexAttribArray(aUv);
    gl.vertexAttribPointer(aUv, 2, gl.FLOAT, false, 16, 8);

    const tex = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, tex);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);

    const uTex = gl.getUniformLocation(program, 'u_tex');
    const uTexelSize = gl.getUniformLocation(program, 'u_texelSize');
    const uSharpness = gl.getUniformLocation(program, 'u_sharpness');
    gl.uniform1i(uTex, 0);

    let destroyed = false;
    let rafId = null;
    let vfcId = null;

    const triggerFallback = () => {
        if (destroyed) return;
        cleanup();
        if (typeof opts.onFallback === 'function') {
            opts.onFallback();
        }
    };

    const renderFrame = () => {
        if (destroyed || !video || video.readyState < 2) return;
        const vw = video.videoWidth || 0;
        const vh = video.videoHeight || 0;
        if (vw <= 0 || vh <= 0) return;

        // Orientation-aware Full-HD GPU CAS sharpening (1920x1080 landscape, 1080x1920 portrait)
        // Keeps GPU texture bandwidth 4x leaner than 3840x2160 so feed scrolling stays locked at 60fps
        const shortEdge = Math.min(vw, vh);
        const scaleUp = shortEdge < 720 ? 1.5 : 1.0;
        const maxW = vw >= vh ? 1920 : 1080;
        const maxH = vw >= vh ? 1080 : 1920;
        const fitFactor = Math.min(1.0, maxW / (vw * scaleUp), maxH / (vh * scaleUp));
        const targetW = Math.max(2, Math.round(vw * scaleUp * fitFactor));
        const targetH = Math.max(2, Math.round(vh * scaleUp * fitFactor));
        if (canvas.width !== targetW || canvas.height !== targetH) {
            canvas.width = targetW;
            canvas.height = targetH;
        }

        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.bindTexture(gl.TEXTURE_2D, tex);
        try {
            gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGB, gl.RGB, gl.UNSIGNED_BYTE, video);
        } catch (_) {
            // Cross-origin video without CORS headers -> silently fall back to native <video>
            triggerFallback();
            return;
        }

        // Gentle FidelityFX CAS to sharpen edges without amplifying H.264 blocks or fabric Moiré
        const sharpness = shortEdge < 1080 ? 0.28 : 0.18;
        gl.uniform2f(uTexelSize, 1.0 / canvas.width, 1.0 / canvas.height);
        gl.uniform1f(uSharpness, sharpness);
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
    };

    const pump = () => {
        if (destroyed) return;
        renderFrame();
        if (!video.paused && !video.ended && !document.hidden) {
            if (typeof video.requestVideoFrameCallback === 'function') {
                vfcId = video.requestVideoFrameCallback(() => pump());
            } else {
                rafId = requestAnimationFrame(pump);
            }
        }
    };

    const onPlay = () => pump();
    const onLoadedData = () => renderFrame();
    const onSeeked = () => renderFrame();

    video.addEventListener('play', onPlay);
    video.addEventListener('loadeddata', onLoadedData);
    video.addEventListener('seeked', onSeeked);

    if (video.readyState >= 2) {
        renderFrame();
        if (!video.paused && !video.ended) {
            pump();
        }
    }

    const cleanup = () => {
        if (destroyed) return;
        destroyed = true;
        if (rafId !== null) cancelAnimationFrame(rafId);
        if (vfcId !== null && typeof video.cancelVideoFrameCallback === 'function') {
            try { video.cancelVideoFrameCallback(vfcId); } catch (_) {}
        }
        video.removeEventListener('play', onPlay);
        video.removeEventListener('loadeddata', onLoadedData);
        video.removeEventListener('seeked', onSeeked);
        try {
            gl.deleteTexture(tex);
            gl.deleteBuffer(vbo);
            gl.deleteProgram(program);
            gl.deleteShader(vs);
            gl.deleteShader(fs);
            const loseExt = gl.getExtension('WEBGL_lose_context');
            if (loseExt) loseExt.loseContext();
        } catch (_) {}
    };

    return cleanup;
}
