<?php
require_once "conexao.php";
require_once "auth.php";
require_once "config/google_maps.php";
verificarLogin();

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$cidade = "São José dos Campos";
$latitude = -23.2199059;
$longitude = -45.9069379;
$erro = "";

if (isset($_GET["cidade"])) {
    $idCidade = filter_var($_GET["cidade"], FILTER_VALIDATE_INT);
    if (!$idCidade) {
        $erro = "Código de cidade inválido.";
    } else {
        $stmt = $pdo->prepare("SELECT nome, latitude, longitude FROM cidades WHERE id = ?");
        $stmt->execute([$idCidade]);
        $registro = $stmt->fetch();

        if (!$registro) {
            $erro = "Cidade não encontrada.";
        } elseif ($registro["latitude"] === null || $registro["longitude"] === null) {
            $erro = "Esta cidade ainda não possui coordenadas. Edite o cadastro e informe latitude e longitude.";
            $cidade = $registro["nome"];
        } else {
            $cidade = $registro["nome"];
            $latitude = (float)$registro["latitude"];
            $longitude = (float)$registro["longitude"];
        }
    }
}

$chaveConfigurada = isset($googleMapsKey) && $googleMapsKey !== "" && $googleMapsKey !== "SUA_CHAVE_API";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Mundo - Google Maps</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #mapa { width: 100%; height: 430px; border-radius: 12px; margin-top: 16px; }
        .mapa-acoes { margin-top: 16px; }
    </style>
</head>
<body>
    <main class="container">
        <section class="lista">
            <h1>CRUD Mundo</h1>
            <h2>Localização: <?= h($cidade) ?></h2>

            <?php if ($erro): ?>
                <div class="mensagem erro"><?= h($erro) ?></div>
            <?php elseif (!$chaveConfigurada): ?>
                <div class="mensagem erro">
                    Configure sua chave do Google Maps no arquivo config/google_maps.php para carregar o mapa.
                </div>
                <p>Latitude: <?= h($latitude) ?> | Longitude: <?= h($longitude) ?></p>
            <?php else: ?>
                <div id="mapa"></div>
                <script>
                    function iniciarMapa() {
                        const localizacao = {
                            lat: <?= json_encode($latitude) ?>,
                            lng: <?= json_encode($longitude) ?>
                        };
                        const mapa = new google.maps.Map(document.getElementById("mapa"), {
                            zoom: 12,
                            center: localizacao
                        });
                        new google.maps.Marker({
                            position: localizacao,
                            map: mapa,
                            title: <?= json_encode($cidade, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
                        });
                    }
                </script>
                <script src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode($googleMapsKey) ?>&callback=iniciarMapa&loading=async" async></script>
            <?php endif; ?>

            <p class="mapa-acoes"><a href="index.php">Voltar ao CRUD Mundo</a></p>
        </section>
    </main>
</body>
</html>
