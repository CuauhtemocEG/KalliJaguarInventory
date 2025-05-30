<?php require './controllers/mainController.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Escanear y Actualizar Producto</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <div id="scanner-section">
      <h2 class="mb-4">Escanea el producto</h2>
      <div id="reader" class="mb-3"></div>
      <p class="fw-bold">Código detectado: <span id="resultado" class="text-primary"></span></p>
      <input type="text" id="lector-input" autofocus />
    </div>

    <div id="form-section" class="mt-4"></div>
  </div>

  <script>
    const scanner = new Html5Qrcode("reader");
    const config = {
      fps: 10,
      qrbox: {
        width: 250,
        height: 250
      }
    };

    // Cuando escaneas un producto
    function procesarCodigo(code) {
      document.getElementById("resultado").textContent = code;
      scanner.stop();

      document.getElementById("scanner-section").style.display = "none";

      // 🔧 Llamar directamente al archivo PHP que devuelve el HTML del formulario
      fetch('./updateStockProduct.php?codigo=' + encodeURIComponent(code))
        .then(res => res.text())
        .then(html => {
          document.getElementById("form-section").innerHTML = html;

          const form = document.getElementById("form-actualizar");
          if (!form) {
            Swal.fire("Error", "Producto no encontrado o error en el formulario.", "error");
            return;
          }

          form.addEventListener("submit", function(e) {
            e.preventDefault();
            const datos = new FormData(form);

            // 🔧 POST directo al archivo PHP del backend
            fetch('./updateStockProduct.php', {
                method: "POST",
                body: datos
              })
              .then(res => res.json())
              .then(data => {
                Swal.fire({
                  title: data.status === "ok" ? "¡Éxito!" : "Error",
                  text: data.message,
                  icon: data.status === "ok" ? "success" : "error"
                });

                if (data.status === "ok") {
                  document.getElementById("stock-actual").textContent = document.getElementById("nuevo_stock").value;

                  const btn = document.createElement("button");
                  btn.textContent = "Escanear nuevo producto";
                  btn.className = "btn btn-primary mt-3";
                  btn.onclick = () => location.reload();
                  document.getElementById("form-section").appendChild(btn);
                }
              });
          });
        });
    }

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        scanner.start({
          facingMode: "environment"
        }, config, code => {
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
</body>

</html>