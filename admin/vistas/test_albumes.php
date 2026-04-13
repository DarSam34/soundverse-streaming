<!DOCTYPE html>
<html>
<head>
    <title>Prueba Álbumes</title>
</head>
<body>
    <h2>Prueba de Álbumes</h2>
    <div id="resultado">Cargando...</div>

    <script>
    // Usar la URL completa con el puerto 8080
    var url = 'http://localhost:80/soundverse-streaming/admin/php/queries.php?caso=listar_albumes';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            document.getElementById('resultado').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('resultado').innerHTML = 'Error: ' + error.message;
        });
    </script>
</body>
</html>