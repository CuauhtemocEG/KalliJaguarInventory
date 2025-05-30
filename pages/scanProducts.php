<!DOCTYPE html>
<html lang="es">
<head>
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
</head>
<body class="bg-light">

  <div class="container py-4 text-center">
    <h2 class="mb-4">Escanea el producto</h2>
    <div id="reader" class="mb-3"></div>
    <p class="fw-bold">Código detectado: <span id="resultado" class="text-primary"></span></p>
    <input type="text" id="lector-input" autofocus />
  </div>

  <script>
    // Función común para procesar código escaneado
    function procesarCodigo(code) {
      document.getElementById("resultado").textContent = code;

      // Redirigir usando AJAX al archivo PHP
      fetch("../js/scanProducts/saveCode.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "codigo=" + encodeURIComponent(code),
      }).then(() => {
        // Detener escáner de cámara
        scanner.stop().catch(err => console.error("Error al detener cámara:", err));
      });
    }

    // Iniciar escáner de cámara
    const scanner = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        scanner.start(
          { facingMode: "environment" },
          config,
          (code) => {
            scanner.pause(); // Pausar para evitar lecturas múltiples
            procesarCodigo(code);
          },
          (error) => {
            // Ignorar errores de escaneo en tiempo real
          }
        );
      } else {
        alert("No se encontró cámara.");
      }
    });

    // Input oculto para lectores físicos
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

    // Mantener el input enfocado incluso si el usuario toca otro elemento
    document.addEventListener("click", () => input.focus());
  </script>
</body>
</html>
