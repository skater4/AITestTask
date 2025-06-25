<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>AI-интервьюер</title>
    <style>
        #recBtn { padding: 1rem 2rem; font-size: 1.2rem; cursor: pointer; }
        #status { margin-top: 1rem; }
        #meter {
            width: 300px;
            height: 40px;
            background: #eee;
            border: 1px solid #ccc;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<button id="recBtn">🎙️ Зажми и говори</button>
<p id="status"></p>
<canvas id="meter"></canvas>
<audio id="replyAudio" controls style="display:none; margin-top: 1rem;"></audio>

<script>
    const btn = document.getElementById('recBtn');
    const status = document.getElementById('status');
    const replyAudio = document.getElementById('replyAudio');
    const meterCanvas = document.getElementById('meter');
    const meterCtx = meterCanvas.getContext('2d');

    let mediaRecorder, chunks = [];
    let audioContext, analyser, source, dataArray, animationId;

    async function startRec() {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        // визуализация
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioContext.createAnalyser();
        source = audioContext.createMediaStreamSource(stream);
        source.connect(analyser);
        analyser.fftSize = 256;
        dataArray = new Uint8Array(analyser.frequencyBinCount);

        function drawMeter() {
            animationId = requestAnimationFrame(drawMeter);
            analyser.getByteFrequencyData(dataArray);
            const volume = Math.max(...dataArray);
            meterCtx.clearRect(0, 0, meterCanvas.width, meterCanvas.height);
            meterCtx.fillStyle = '#4caf50';
            meterCtx.fillRect(0, 0, volume / 255 * meterCanvas.width, meterCanvas.height);
        }
        drawMeter();

        // запись
        chunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
        mediaRecorder.ondataavailable = e => chunks.push(e.data);
        mediaRecorder.onstop = async () => {
            cancelAnimationFrame(animationId);
            meterCtx.clearRect(0, 0, meterCanvas.width, meterCanvas.height);
            audioContext.close();

            status.textContent = 'Отправляю…';
            const blob = new Blob(chunks, { type: 'audio/webm' });
            const fd = new FormData();
            fd.append('audio', blob, 'recording.webm');

            const res = await fetch('transcribe.php', { method: 'POST', body: fd });
            const data = await res.json();

            status.textContent = data.text ?? '—';
            if (data.audioUrl) {
                replyAudio.src = data.audioUrl + '?t=' + Date.now();
                replyAudio.style.display = 'block';
                replyAudio.play();
            }
        };

        mediaRecorder.start();
        status.textContent = 'Запись…';
    }

    function stopRec() {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            status.textContent = 'Обработка…';
        }
    }

    // мышь
    btn.addEventListener('mousedown', startRec);
    btn.addEventListener('mouseup', stopRec);
    btn.addEventListener('mouseleave', stopRec);

    // сенсор
    btn.addEventListener('touchstart', e => { e.preventDefault(); startRec(); });
    btn.addEventListener('touchend',   e => { e.preventDefault(); stopRec();  });
</script>
</body>
</html>
