<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Scanner Mahayana</title>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.tailwindcss.com"></script>

<style>
body {
    background: radial-gradient(circle at top, #0f172a, #020617);
    font-family: sans-serif;
}

.glass {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.1);
}

#reader {
    border-radius: 16px;
    overflow: hidden;
}

/* glow scan */
.scan-glow {
    box-shadow: 0 0 25px rgba(59,130,246,0.5);
}
</style>
</head>

<body class="text-white flex flex-col items-center justify-center min-h-screen px-4">

<!-- HEADER -->
<div class="text-center mb-6">
    <h1 class="text-2xl font-bold tracking-widest">SCAN TIKET BUS</h1>
    <p class="text-xs text-gray-400">Arahkan kamera ke QR Code</p>
</div>

<!-- SCANNER -->
<div class="glass p-4 rounded-2xl w-full max-w-sm scan-glow">
    <div id="reader"></div>
</div>

<!-- CONTROL -->
<div class="flex gap-2 mt-4 w-full max-w-sm">
    <button onclick="startCamera()" class="flex-1 bg-blue-600 hover:bg-blue-700 p-3 rounded-lg text-sm font-bold">
        Start
    </button>
    <button onclick="stopCamera()" class="flex-1 bg-red-600 hover:bg-red-700 p-3 rounded-lg text-sm font-bold">
        Stop
    </button>
</div>

<!-- RESULT -->
<div id="result" class="mt-5 text-center text-sm font-bold"></div>

<script>

let html5QrCode;
let lastScan = 0;

/* =========================
   SOUND SUCCESS / ERROR
========================= */
function playBeep(type){
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.type = "sine";
    osc.frequency.value = (type === 'ok') ? 880 : 220;

    gain.gain.value = 0.15;

    osc.start();

    setTimeout(() => {
        osc.stop();
        ctx.close();
    }, 180);
}

/* =========================
   VIBRATION
========================= */
function vibrate(type){
    if(!navigator.vibrate) return;

    navigator.vibrate(type === 'ok' ? [80,40,80] : [200]);
}

/* =========================
   UI MESSAGE
========================= */
function showMessage(text, type){
    const box = document.getElementById('result');

    box.innerHTML = (type === 'success' ? "✅ " : "❌ ") + text;
    box.className = "mt-5 text-center text-sm font-bold " +
        (type === 'success' ? "text-green-400" : "text-red-400");
}

/* =========================
   HANDLE SCAN
========================= */
function handleResult(decodedText){

    const now = Date.now();
    if(now - lastScan < 2500) return;
    lastScan = now;

    fetch('validate_qr.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'code=' + encodeURIComponent(decodedText)
    })
    .then(res => res.json())
    .then(res => {

        if(res.status === 'success'){
            showMessage(res.message, 'success');

            playBeep('ok');
            vibrate('ok');

        } else {
            showMessage(res.message, 'error');

            playBeep('err');
            vibrate('err');
        }

    })
    .catch(() => {
        showMessage("Server error", "error");

        playBeep('err');
        vibrate('err');
    });
}

/* =========================
   START CAMERA
========================= */
function startCamera(){

    html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {

        if(!devices.length){
            showMessage("Camera tidak ditemukan", "error");
            return;
        }

        let cameraId = devices[0].id;

        devices.forEach(cam => {
            if(cam.label.toLowerCase().includes("back")){
                cameraId = cam.id;
            }
        });

        html5QrCode.start(
            cameraId,
            { fps: 10, qrbox: 250 },
            handleResult
        );

    }).catch(() => {
        showMessage("Tidak bisa akses kamera", "error");
    });
}

/* =========================
   STOP CAMERA
========================= */
function stopCamera(){
    if(html5QrCode){
        html5QrCode.stop()
        .then(() => html5QrCode.clear())
        .catch(() => {});
    }
}

</script>

</body>
</html>