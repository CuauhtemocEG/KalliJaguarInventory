<div class="container py-4 text-center">
  <div id="scanner-section">
    <h2 class="mb-4">Escanea el producto</h2>
    <div id="reader" class="mb-3"></div>
    <p class="fw-bold">Código detectado: <span id="resultado" class="text-primary"></span></p>
    <input type="text" id="lector-input" autofocus />
  </div>
</div>

<style>
  #reader {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    border: 2px solid #ccc;
    border-radius: 8px;
  }

  #lector-input {
    opacity: 0;
    position: absolute;
    left: -9999px;
  }
</style>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
  const scanner = new Html5Qrcode("reader");
  const config = {
    fps: 10,
    qrbox: {
      width: 250,
      height: 250
    }
  };

  function procesarCodigo(code) {
    document.getElementById("resultado").textContent = code;
    scanner.stop();
    window.location.href = 'index.php?page=updateStockProduct&codigo=' + encodeURIComponent(code);
  }

  Html5Qrcode.getCameras().then(cameras => {
    if (cameras && cameras.length) {
      scanner.start({ facingMode: "environment" }, config, code => {
        scanner.pause();
        procesarCodigo(code);
      });
    } else {
      alert("No se encontró cámara.");
    }
  });

  const input = document.getElementById("lector-input");
  input.focus();
  let buffer = "";

  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      procesarCodigo(buffer.trim());
      buffer = "";
    } else {
      buffer += e.key;
    }
  });

  document.addEventListener("click", () => input.focus());
</script>
