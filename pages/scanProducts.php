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
  <!-- Dentro de <body> -->
  <div id="scanner-section">
    <h2>Escanea el producto</h2>
    <div id="reader"></div>
    <p><strong>Código detectado:</strong> <span id="resultado"></span></p>
  </div>

  <div id="form-section"></div>

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

      // Oculta lector y carga formulario
      document.getElementById("scanner-section").style.display = "none";

      fetch("updateStockProduct.php?codigo=" + encodeURIComponent(code))
        .then(res => res.text())
        .then(html => {
          document.getElementById("form-section").innerHTML = html;

          // Reejecutar scripts del formulario cargado dinámicamente
          const script = document.createElement('script');
          script.innerHTML = `
          document.getElementById("form-actualizar").addEventListener("submit", function(e) {
            e.preventDefault();
            const datos = new FormData(this);
            fetch("updateStockProduct.php", {
              method: "POST",
              body: datos
            })
            .then(res => res.json())
            .then(data => {
              Swal.fire({
                title: data.status === "ok" ? "¡Éxito!" : "Error",
                text: data.message,
                icon: data.status === "ok" ? "success" : "error",
                confirmButtonText: "Aceptar"
              });

              if (data.status === "ok") {
                document.getElementById("stock-actual").textContent = document.getElementById("nuevo_stock").value;

                // Mostrar botón para nuevo escaneo
                const btn = document.createElement('button');
                btn.textContent = "Escanear nuevo producto";
                btn.className = "btn btn-primary mt-3";
                btn.onclick = () => location.reload();
                document.getElementById("form-section").appendChild(btn);
              }
            });
          });
        `;
          document.body.appendChild(script);
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
  </script>
</body>

</html>