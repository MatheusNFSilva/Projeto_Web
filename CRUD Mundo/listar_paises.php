<?php
require_once "conexao.php";
require_once "auth.php";
verificarLogin();

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// O CRUD original utiliza a coluna nome para identificar o país.
$sql = "SELECT p.*, c.nome AS continente_nome
        FROM paises p
        INNER JOIN continentes c ON c.id = p.continente_id
        ORDER BY p.nome ASC";
$paises = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Países cadastrados - CRUD Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <section class="lista">
            <h1>Países cadastrados</h1>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>País</th><th>ISO</th><th>Capital</th><th>População</th><th>Área (km²)</th><th>Região</th><th>Sub-região</th><th>Continente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paises as $pais): ?>
                            <tr>
                                <td><?= (int)$pais["id"] ?></td>
                                <td><?= h($pais["nome"]) ?></td>
                                <td><?= h($pais["codigo_iso"] ?? "") ?></td>
                                <td><?= h($pais["capital"] ?? "Não informada") ?></td>
                                <td><?= number_format((float)$pais["populacao"], 0, ",", ".") ?></td>
                                <td><?= number_format((float)$pais["area"], 2, ",", ".") ?></td>
                                <td><?= h($pais["regiao"] ?? "") ?></td>
                                <td><?= h($pais["sub_regiao"] ?? "") ?></td>
                                <td><?= h($pais["continente_nome"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$paises): ?>
                            <tr><td colspan="9">Nenhum país cadastrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p><a href="index.php">Voltar ao CRUD Mundo</a></p>
        </section>
    </main>
</body>
</html>
