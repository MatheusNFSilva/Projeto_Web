<?php
require_once 'conexao.php';

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$msg = $_GET['msg'] ?? '';

// Dados para listas
$continentes = $pdo->query("SELECT * FROM continentes ORDER BY nome")->fetchAll();
$governantes = $pdo->query("SELECT * FROM governantes ORDER BY nome")->fetchAll();
$paises = $pdo->query("
    SELECT p.*, c.nome AS continente_nome, g.nome AS governante_nome
    FROM paises p
    INNER JOIN continentes c ON c.id = p.continente_id
    LEFT JOIN governantes g ON g.id = p.governante_id
    ORDER BY p.nome
")->fetchAll();
$cidades = $pdo->query("
    SELECT ci.*, p.nome AS pais_nome, g.nome AS governante_nome
    FROM cidades ci
    INNER JOIN paises p ON p.id = ci.pais_id
    LEFT JOIN governantes g ON g.id = ci.governante_id
    ORDER BY ci.nome
")->fetchAll();

// Edição simples por tipo
$tipoEdicao = $_GET['tipo'] ?? '';
$idEdicao = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$registro = [];

if ($tipoEdicao && $idEdicao) {
    if ($tipoEdicao === 'continente') {
        $stmt = $pdo->prepare("SELECT * FROM continentes WHERE id = ?");
        $stmt->execute([$idEdicao]);
        $registro = $stmt->fetch() ?: [];
    }
    if ($tipoEdicao === 'governante') {
        $stmt = $pdo->prepare("SELECT * FROM governantes WHERE id = ?");
        $stmt->execute([$idEdicao]);
        $registro = $stmt->fetch() ?: [];
    }
    if ($tipoEdicao === 'pais') {
        $stmt = $pdo->prepare("SELECT * FROM paises WHERE id = ?");
        $stmt->execute([$idEdicao]);
        $registro = $stmt->fetch() ?: [];
    }
    if ($tipoEdicao === 'cidade') {
        $stmt = $pdo->prepare("SELECT * FROM cidades WHERE id = ?");
        $stmt->execute([$idEdicao]);
        $registro = $stmt->fetch() ?: [];
    }
}

function selected($a, $b) {
    return (string)$a === (string)$b ? 'selected' : '';
}

function valueOrEmpty($arr, $key) {
    return isset($arr[$key]) ? h($arr[$key]) : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Mundo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topo">
        <div>
            <h1>CRUD Mundo</h1>
            <p>Projeto simples em PHP, MySQL, CSS e JavaScript</p>
        </div>

        <div class="busca">
            <input type="text" id="searchGlobal" placeholder="Pesquisar país, cidade, continente ou governante...">
        </div>
    </header>

    <main class="container">
        <?php if ($msg): ?>
            <div class="mensagem"><?= h($msg) ?></div>
        <?php endif; ?>

        <section class="cards">
            <article class="card">
                <h2><?= $tipoEdicao === 'continente' ? 'Editar continente' : 'Novo continente' ?></h2>
                <form action="acao.php" method="post" class="formulario">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="tipo" value="continente">
                    <input type="hidden" name="id" value="<?= $tipoEdicao === 'continente' ? (int)$idEdicao : '' ?>">

                    <label>Nome
                        <input type="text" name="nome" required value="<?= $tipoEdicao === 'continente' ? valueOrEmpty($registro, 'nome') : '' ?>">
                    </label>
                    <label>População
                        <input type="number" name="populacao" required value="<?= $tipoEdicao === 'continente' ? valueOrEmpty($registro, 'populacao') : '' ?>">
                    </label>
                    <label>Área (km²)
                        <input type="number" step="0.01" name="area" required value="<?= $tipoEdicao === 'continente' ? valueOrEmpty($registro, 'area') : '' ?>">
                    </label>
                    <label>Total de países
                        <input type="number" name="total_paises" required value="<?= $tipoEdicao === 'continente' ? valueOrEmpty($registro, 'total_paises') : '' ?>">
                    </label>

                    <button type="submit"><?= $tipoEdicao === 'continente' ? 'Atualizar' : 'Cadastrar' ?></button>
                </form>
            </article>

            <article class="card">
                <h2><?= $tipoEdicao === 'governante' ? 'Editar governante' : 'Novo governante' ?></h2>
                <form action="acao.php" method="post" class="formulario">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="tipo" value="governante">
                    <input type="hidden" name="id" value="<?= $tipoEdicao === 'governante' ? (int)$idEdicao : '' ?>">

                    <label>Nome
                        <input type="text" name="nome" required value="<?= $tipoEdicao === 'governante' ? valueOrEmpty($registro, 'nome') : '' ?>">
                    </label>
                    <label>Partido político
                        <input type="text" name="partido_politico" required value="<?= $tipoEdicao === 'governante' ? valueOrEmpty($registro, 'partido_politico') : '' ?>">
                    </label>
                    <label>Data de nascimento
                        <input type="date" name="data_nascimento" required value="<?= $tipoEdicao === 'governante' ? valueOrEmpty($registro, 'data_nascimento') : '' ?>">
                    </label>
                    <label>Idade
                        <input type="number" name="idade" required value="<?= $tipoEdicao === 'governante' ? valueOrEmpty($registro, 'idade') : '' ?>">
                    </label>
                    <label>Início do mandato
                        <input type="date" name="data_inicio_mandato" required value="<?= $tipoEdicao === 'governante' ? valueOrEmpty($registro, 'data_inicio_mandato') : '' ?>">
                    </label>
                    <label>Fim do mandato
                        <input type="date" name="data_fim_mandato" value="<?= $tipoEdicao === 'governante' ? valueOrEmpty($registro, 'data_fim_mandato') : '' ?>">
                    </label>

                    <button type="submit"><?= $tipoEdicao === 'governante' ? 'Atualizar' : 'Cadastrar' ?></button>
                </form>
            </article>

            <article class="card">
                <h2><?= $tipoEdicao === 'pais' ? 'Editar país' : 'Novo país' ?></h2>
                <form action="acao.php" method="post" class="formulario">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="tipo" value="pais">
                    <input type="hidden" name="id" value="<?= $tipoEdicao === 'pais' ? (int)$idEdicao : '' ?>">

                    <label>Nome
                        <input type="text" name="nome" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'nome') : '' ?>">
                    </label>

                    <label>Continente
                        <select name="continente_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($continentes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $tipoEdicao === 'pais' ? selected($registro['continente_id'] ?? '', $c['id']) : '' ?>>
                                    <?= h($c['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>População
                        <input type="number" name="populacao" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'populacao') : '' ?>">
                    </label>
                    <label>Área (km²)
                        <input type="number" step="0.01" name="area" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'area') : '' ?>">
                    </label>
                    <label>Idioma
                        <input type="text" name="idioma" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'idioma') : '' ?>">
                    </label>

                    <label>Governante
                        <select name="governante_id">
                            <option value="">Sem governante</option>
                            <?php foreach ($governantes as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= $tipoEdicao === 'pais' ? selected($registro['governante_id'] ?? '', $g['id']) : '' ?>>
                                    <?= h($g['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>Clima
                        <input type="text" name="clima" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'clima') : '' ?>">
                    </label>
                    <label>Regime político
                        <input type="text" name="regime_politico" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'regime_politico') : '' ?>">
                    </label>
                    <label>Moeda
                        <input type="text" name="moeda" required value="<?= $tipoEdicao === 'pais' ? valueOrEmpty($registro, 'moeda') : '' ?>">
                    </label>

                    <button type="submit"><?= $tipoEdicao === 'pais' ? 'Atualizar' : 'Cadastrar' ?></button>
                </form>
            </article>

            <article class="card">
                <h2><?= $tipoEdicao === 'cidade' ? 'Editar cidade' : 'Nova cidade' ?></h2>
                <form action="acao.php" method="post" class="formulario">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="tipo" value="cidade">
                    <input type="hidden" name="id" value="<?= $tipoEdicao === 'cidade' ? (int)$idEdicao : '' ?>">

                    <label>Nome
                        <input type="text" name="nome" required value="<?= $tipoEdicao === 'cidade' ? valueOrEmpty($registro, 'nome') : '' ?>">
                    </label>

                    <label>País
                        <select name="pais_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($paises as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $tipoEdicao === 'cidade' ? selected($registro['pais_id'] ?? '', $p['id']) : '' ?>>
                                    <?= h($p['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>População
                        <input type="number" name="populacao" required value="<?= $tipoEdicao === 'cidade' ? valueOrEmpty($registro, 'populacao') : '' ?>">
                    </label>
                    <label>Área (km²)
                        <input type="number" step="0.01" name="area" required value="<?= $tipoEdicao === 'cidade' ? valueOrEmpty($registro, 'area') : '' ?>">
                    </label>
                    <label>Clima
                        <input type="text" name="clima" required value="<?= $tipoEdicao === 'cidade' ? valueOrEmpty($registro, 'clima') : '' ?>">
                    </label>

                    <label>Governante
                        <select name="governante_id">
                            <option value="">Sem governante</option>
                            <?php foreach ($governantes as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= $tipoEdicao === 'cidade' ? selected($registro['governante_id'] ?? '', $g['id']) : '' ?>>
                                    <?= h($g['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>Data de fundação
                        <input type="date" name="data_fundacao" value="<?= $tipoEdicao === 'cidade' ? valueOrEmpty($registro, 'data_fundacao') : '' ?>">
                    </label>

                    <button type="submit"><?= $tipoEdicao === 'cidade' ? 'Atualizar' : 'Cadastrar' ?></button>
                </form>
            </article>
        </section>

        <section class="lista">
            <h2>Continentes cadastrados</h2>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th><th>População</th><th>Área</th><th>Total países</th><th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($continentes as $c): ?>
                            <tr data-search="<?= h($c['nome'] . ' ' . $c['populacao'] . ' ' . $c['area']) ?>">
                                <td><?= h($c['nome']) ?></td>
                                <td><?= h($c['populacao']) ?></td>
                                <td><?= h($c['area']) ?></td>
                                <td><?= h($c['total_paises']) ?></td>
                                <td class="acoes">
                                    <a href="?tipo=continente&id=<?= $c['id'] ?>">Editar</a>
                                    <a href="acao.php?acao=deletar&tipo=continente&id=<?= $c['id'] ?>" class="excluir">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="lista">
            <h2>Governantes cadastrados</h2>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th><th>Partido</th><th>Nascimento</th><th>Idade</th><th>Início</th><th>Fim</th><th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($governantes as $g): ?>
                            <tr data-search="<?= h($g['nome'] . ' ' . $g['partido_politico']) ?>">
                                <td><?= h($g['nome']) ?></td>
                                <td><?= h($g['partido_politico']) ?></td>
                                <td><?= h($g['data_nascimento']) ?></td>
                                <td><?= h($g['idade']) ?></td>
                                <td><?= h($g['data_inicio_mandato']) ?></td>
                                <td><?= h($g['data_fim_mandato']) ?></td>
                                <td class="acoes">
                                    <a href="?tipo=governante&id=<?= $g['id'] ?>">Editar</a>
                                    <a href="acao.php?acao=deletar&tipo=governante&id=<?= $g['id'] ?>" class="excluir">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="lista">
            <h2>Países cadastrados</h2>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th><th>Continente</th><th>População</th><th>Área</th><th>Idioma</th><th>Governante</th><th>Clima</th><th>Regime</th><th>Moeda</th><th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paises as $p): ?>
                            <tr data-search="<?= h($p['nome'] . ' ' . $p['continente_nome'] . ' ' . $p['idioma'] . ' ' . $p['moeda']) ?>">
                                <td><?= h($p['nome']) ?></td>
                                <td><?= h($p['continente_nome']) ?></td>
                                <td><?= h($p['populacao']) ?></td>
                                <td><?= h($p['area']) ?></td>
                                <td><?= h($p['idioma']) ?></td>
                                <td><?= h($p['governante_nome'] ?? 'Sem governante') ?></td>
                                <td><?= h($p['clima']) ?></td>
                                <td><?= h($p['regime_politico']) ?></td>
                                <td><?= h($p['moeda']) ?></td>
                                <td class="acoes">
                                    <a href="?tipo=pais&id=<?= $p['id'] ?>">Editar</a>
                                    <a href="acao.php?acao=deletar&tipo=pais&id=<?= $p['id'] ?>" class="excluir">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="lista">
            <h2>Cidades cadastradas</h2>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th><th>País</th><th>População</th><th>Área</th><th>Clima</th><th>Governante</th><th>Fundação</th><th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cidades as $ci): ?>
                            <tr data-search="<?= h($ci['nome'] . ' ' . $ci['pais_nome']) ?>">
                                <td><?= h($ci['nome']) ?></td>
                                <td><?= h($ci['pais_nome']) ?></td>
                                <td><?= h($ci['populacao']) ?></td>
                                <td><?= h($ci['area']) ?></td>
                                <td><?= h($ci['clima']) ?></td>
                                <td><?= h($ci['governante_nome'] ?? 'Sem governante') ?></td>
                                <td><?= h($ci['data_fundacao']) ?></td>
                                <td class="acoes">
                                    <a href="?tipo=cidade&id=<?= $ci['id'] ?>">Editar</a>
                                    <a href="acao.php?acao=deletar&tipo=cidade&id=<?= $ci['id'] ?>" class="excluir">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>
