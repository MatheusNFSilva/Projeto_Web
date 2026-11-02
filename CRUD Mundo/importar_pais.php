<?php
require_once "conexao.php";
require_once "auth.php";
require_once "config/api.php";
verificarLogin();

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$mensagem = "";
$sucesso = false;
$idCadastrado = null;
$nome = "";
$codigo = "";

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Acesso inválido. Utilize o formulário de importação.");
    }

    $nomeBusca = trim($_POST["pais"] ?? "");
    if ($nomeBusca === "") {
        throw new Exception("Informe o nome de um país.");
    }
    if (strlen($nomeBusca) > 100) {
        throw new Exception("O nome informado é muito longo.");
    }
    if (!isset($apiKey) || $apiKey === "") {
        throw new Exception("A chave da REST Countries não foi configurada.");
    }
    if (!function_exists("curl_init")) {
        throw new Exception("A extensão cURL não está habilitada no PHP. Ative-a no XAMPP.");
    }

    // Consulta GET à REST Countries API, conforme o material da atividade.
    $url = "https://api.restcountries.com/countries/v5/names.common/" . rawurlencode($nomeBusca);
    $curl = curl_init($url);
    if ($curl === false) {
        throw new Exception("Não foi possível iniciar a comunicação com a API.");
    }

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $apiKey,
            "Accept: application/json"
        ]
    ]);

    $resposta = curl_exec($curl);
    $statusHTTP = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $erroCurl = curl_error($curl);
    curl_close($curl);

    if ($resposta === false) {
        throw new Exception("Falha na comunicação com a API: " . $erroCurl);
    }
    if ($statusHTTP === 404) {
        throw new Exception("País não encontrado. Confira o nome digitado.");
    }
    if ($statusHTTP !== 200) {
        throw new Exception("A API retornou o erro HTTP " . $statusHTTP . ". Confira a chave de acesso.");
    }

    $dados = json_decode($resposta, true, 512, JSON_THROW_ON_ERROR);
    $pais = $dados["data"]["objects"][0] ?? null;
    if (!is_array($pais)) {
        throw new Exception("A API não retornou dados válidos para esse país.");
    }

    // Extração dos campos usados na atividade.
    $nome = trim((string)($pais["names"]["common"] ?? ""));
    $codigo = strtoupper(trim((string)($pais["codes"]["alpha_2"] ?? "")));
    $capital = $pais["capitals"][0]["name"] ?? null;
    $populacao = $pais["population"] ?? 0;
    $area = $pais["area"]["kilometers"] ?? 0;
    $regiao = trim((string)($pais["region"] ?? ""));
    $subRegiao = $pais["subregion"] ?? null;
    // O projeto original possui uma tabela de continentes separada da região da API.
    $continentesApi = $pais["continents"] ?? [];
    $continenteNome = (is_array($continentesApi) && isset($continentesApi[0]) && is_string($continentesApi[0]))
        ? trim($continentesApi[0])
        : $regiao;

    if ($nome === "" || strlen($nome) > 120 || !preg_match('/^[A-Z]{2}$/', $codigo)) {
        throw new Exception("Os dados obrigatórios recebidos da API são inválidos.");
    }
    if (!is_numeric($populacao) || (float)$populacao < 0) {
        throw new Exception("A população retornada pela API é inválida.");
    }
    if (!is_numeric($area) || (float)$area < 0) {
        $area = 0;
    }

    if ($capital !== null && !is_string($capital)) {
        $capital = null;
    }
    if ($regiao === "") {
        $regiao = "Não informado";
    }
    if ($continenteNome === "") {
        $continenteNome = $regiao;
    }
    if ($subRegiao !== null && !is_string($subRegiao)) {
        $subRegiao = null;
    }

    // A tabela antiga possui campos obrigatórios que não existem na resposta simplificada da atividade.
    // Idioma e moeda são aproveitados quando estiverem disponíveis na resposta.
    $idiomas = [];
    foreach (($pais["languages"] ?? []) as $idioma) {
        if (is_array($idioma)) {
            $nomeIdioma = $idioma["name"] ?? $idioma["native_name"] ?? null;
            if (is_string($nomeIdioma) && $nomeIdioma !== "") {
                $idiomas[] = $nomeIdioma;
            }
        } elseif (is_string($idioma) && $idioma !== "") {
            $idiomas[] = $idioma;
        }
    }
    $idiomaTexto = $idiomas ? implode(", ", array_unique($idiomas)) : "Não informado";
    $idiomaTexto = function_exists("mb_substr") ? mb_substr($idiomaTexto, 0, 80) : substr($idiomaTexto, 0, 80);

    $moedas = [];
    foreach (($pais["currencies"] ?? []) as $moeda) {
        if (is_array($moeda)) {
            $nomeMoeda = $moeda["name"] ?? null;
            if (is_string($nomeMoeda) && $nomeMoeda !== "") {
                $moedas[] = $nomeMoeda;
            }
        } elseif (is_string($moeda) && $moeda !== "") {
            $moedas[] = $moeda;
        }
    }
    $moedaTexto = $moedas ? implode(", ", array_unique($moedas)) : "Não informado";
    $moedaTexto = function_exists("mb_substr") ? mb_substr($moedaTexto, 0, 60) : substr($moedaTexto, 0, 60);

    if ($capital !== null) {
        $capital = function_exists("mb_substr") ? mb_substr($capital, 0, 120) : substr($capital, 0, 120);
    }
    if ($subRegiao !== null) {
        $subRegiao = function_exists("mb_substr") ? mb_substr($subRegiao, 0, 100) : substr($subRegiao, 0, 100);
    }
    $regiao = function_exists("mb_substr") ? mb_substr($regiao, 0, 100) : substr($regiao, 0, 100);
    $continenteNome = function_exists("mb_substr") ? mb_substr($continenteNome, 0, 100) : substr($continenteNome, 0, 100);

    $pdo->beginTransaction();

    // Impede que um país seja importado duas vezes (pelo código ISO ou pelo nome).
    $verificar = $pdo->prepare("SELECT id FROM paises WHERE codigo_iso = :codigo OR LOWER(nome) = LOWER(:nome) LIMIT 1");
    $verificar->execute([":codigo" => $codigo, ":nome" => $nome]);
    if ($verificar->fetch()) {
        throw new Exception("Este país já está cadastrado no CRUD Mundo.");
    }

    // Garante que o continente/região exista para respeitar a chave estrangeira do projeto original.
    $buscarContinente = $pdo->prepare("SELECT id FROM continentes WHERE nome = ? LIMIT 1");
    $buscarContinente->execute([$regiao]);
    $continente = $buscarContinente->fetch();

    if ($continente) {
        $continenteId = (int)$continente["id"];
    } else {
        $criarContinente = $pdo->prepare(
            "INSERT INTO continentes (nome, populacao, area, total_paises) VALUES (?, 0, 0, 0)"
        );
        $criarContinente->execute([$continenteNome]);
        $continenteId = (int)$pdo->lastInsertId();
    }

    $sql = "INSERT INTO paises
        (nome, continente_id, populacao, area, idioma, governante_id, clima, regime_politico, moeda,
         codigo_iso, capital, regiao, sub_regiao)
        VALUES
        (:nome, :continente_id, :populacao, :area, :idioma, NULL, :clima, :regime, :moeda,
         :codigo_iso, :capital, :regiao, :sub_regiao)";

    $inserir = $pdo->prepare($sql);
    $inserir->execute([
        ":nome" => $nome,
        ":continente_id" => $continenteId,
        ":populacao" => (int)$populacao,
        ":area" => (float)$area,
        ":idioma" => $idiomaTexto,
        ":clima" => "Não informado",
        ":regime" => "Não informado",
        ":moeda" => $moedaTexto,
        ":codigo_iso" => $codigo,
        ":capital" => $capital,
        ":regiao" => $regiao,
        ":sub_regiao" => $subRegiao
    ]);

    $idCadastrado = $pdo->lastInsertId();
    $atualizarTotal = $pdo->prepare("UPDATE continentes SET total_paises = (SELECT COUNT(*) FROM paises WHERE continente_id = ?) WHERE id = ?");
    $atualizarTotal->execute([$continenteId, $continenteId]);

    $pdo->commit();
    $mensagem = "País cadastrado com sucesso!";
    $sucesso = true;

} catch (PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($erro->getMessage());
    $mensagem = "Erro ao gravar no banco de dados. Verifique a estrutura do banco e tente novamente.";
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $mensagem = $erro->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da importação - CRUD Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container resultado-api">
        <section class="lista">
            <h1>CRUD Mundo</h1>
            <h2>Resultado da importação</h2>
            <div class="mensagem <?= $sucesso ? "" : "erro" ?>"><?= h($mensagem) ?></div>
            <?php if ($sucesso): ?>
                <p><strong>ID cadastrado:</strong> <?= h($idCadastrado) ?></p>
                <p><strong>País:</strong> <?= h($nome) ?></p>
                <p><strong>Código ISO:</strong> <?= h($codigo) ?></p>
            <?php endif; ?>
            <p><a href="index.php">Voltar ao CRUD Mundo</a></p>
            <p><a href="listar_paises.php">Visualizar países cadastrados</a></p>
        </section>
    </main>
</body>
</html>
