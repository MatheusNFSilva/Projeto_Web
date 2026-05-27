<?php

$turma = $_POST['turma'] ?? '';
$quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 0;

$gerar_campos = isset($_POST['gerar_campos']);
$processar = isset($_POST['processar']);

function h($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Análise Estatística de Turma</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .box { background: #fff; padding: 20px; border-radius: 8px; max-width: 1100px; margin: auto; }
        input { padding: 8px; margin: 4px 0 12px 0; width: 100%; box-sizing: border-box; }
        .linha { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .linha2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #e9e9e9; }
        .botao { width: auto; padding: 10px 15px; cursor: pointer; }
        .resumo { background: #f9f9f9; padding: 15px; border-left: 4px solid #333; margin-top: 20px; }
    </style>
</head>
<body>
<div class="box">
    <h1>Sistema de Análise de Notas</h1>

<?php if (!$gerar_campos && !$processar): ?>

    <form method="post">
        <label>Nome da turma:</label>
        <input type="text" name="turma" required>

        <label>Quantidade de alunos:</label>
        <input type="number" name="quantidade" min="1" required>

        <button class="botao" type="submit" name="gerar_campos">Gerar campos dos alunos</button>
    </form>

<?php elseif ($gerar_campos && !$processar): ?>

    <h2>Turma: <?php echo h($turma); ?></h2>

    <form method="post">
        <input type="hidden" name="turma" value="<?php echo h($turma); ?>">
        <input type="hidden" name="quantidade" value="<?php echo h($quantidade); ?>">

        <?php for ($i = 0; $i < $quantidade; $i++): ?>
            <h3>Aluno <?php echo $i + 1; ?></h3>
            <div class="linha">
                <div>
                    <label>Nome</label>
                    <input type="text" name="nome[]" required>
                </div>
                <div>
                    <label>Prova 1</label>
                    <input type="number" step="0.1" name="nota1[]" required>
                </div>
                <div>
                    <label>Prova 2</label>
                    <input type="number" step="0.1" name="nota2[]" required>
                </div>
                <div>
                    <label>Trabalho</label>
                    <input type="number" step="0.1" name="trabalho[]" required>
                </div>
            </div>
        <?php endfor; ?>

        <button class="botao" type="submit" name="processar">Processar dados</button>
    </form>

<?php elseif ($processar): ?>

    <?php
    $nomes = $_POST['nome'] ?? [];
    $notas1 = $_POST['nota1'] ?? [];
    $notas2 = $_POST['nota2'] ?? [];
    $trabalhos = $_POST['trabalho'] ?? [];

    $alunos = [];
    $soma_medias = 0;
    $soma_total_notas = 0;
    $maior_media = null;
    $menor_media = null;
    $aprovados = 0;
    $recuperacao = 0;
    $reprovados = 0;

    for ($i = 0; $i < $quantidade; $i++) {
        $nome = trim($nomes[$i] ?? 'Aluno');
        $n1 = (float)($notas1[$i] ?? 0);
        $n2 = (float)($notas2[$i] ?? 0);
        $nt = (float)($trabalhos[$i] ?? 0);

        $media = ($n1 + $n2 + $nt) / 3;
        $raiz = sqrt($n1 + $n2 + $nt);
        $maior = max($n1, $n2, $nt);
        $menor = min($n1, $n2, $nt);
        $diferenca = abs($maior - $menor);

        if ($media >= 7) {
            $situacao = "Aprovado";
            $aprovados++;
        } elseif ($media >= 5) {
            $situacao = "Recuperação";
            $recuperacao++;
        } else {
            $situacao = "Reprovado";
            $reprovados++;
        }

        $alunos[] = [
            'nome' => $nome,
            'n1' => $n1,
            'n2' => $n2,
            'nt' => $nt,
            'media' => $media,
            'raiz' => $raiz,
            'dif' => $diferenca,
            'situacao' => $situacao
        ];

        $soma_medias += $media;
        $soma_total_notas += ($n1 + $n2 + $nt);

        if ($maior_media === null || $media > $maior_media) {
            $maior_media = $media;
        }
        if ($menor_media === null || $media < $menor_media) {
            $menor_media = $media;
        }
    }

    $media_turma = ($quantidade > 0) ? ($soma_medias / $quantidade) : 0;
    $percentual_aprovacao = ($quantidade > 0) ? (($aprovados / $quantidade) * 100) : 0;

    if ($percentual_aprovacao >= 80) {
        $mensagem = "Desempenho geral excelente!";
    } elseif ($percentual_aprovacao >= 60) {
        $mensagem = "Desempenho geral bom, mas ainda pode melhorar.";
    } else {
        $mensagem = "Desempenho geral baixo. É preciso reforço.";
    }
    ?>

    <h2>Relatório da Turma: <?php echo h($turma); ?></h2>

    <table>
        <tr>
            <th>Aluno</th>
            <th>Prova 1</th>
            <th>Prova 2</th>
            <th>Trabalho</th>
            <th>Média</th>
            <th>Raiz da Soma</th>
            <th>Diferença</th>
            <th>Situação</th>
        </tr>
        <?php foreach ($alunos as $a): ?>
            <tr>
                <td><?php echo h($a['nome']); ?></td>
                <td><?php echo number_format($a['n1'], 1, ',', '.'); ?></td>
                <td><?php echo number_format($a['n2'], 1, ',', '.'); ?></td>
                <td><?php echo number_format($a['nt'], 1, ',', '.'); ?></td>
                <td><?php echo number_format($a['media'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($a['raiz'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($a['dif'], 2, ',', '.'); ?></td>
                <td><?php echo h($a['situacao']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="resumo">
        <h3>Resumo da Turma</h3>
        <p><strong>Média geral da turma:</strong> <?php echo number_format($media_turma, 2, ',', '.'); ?></p>
        <p><strong>Maior média encontrada:</strong> <?php echo number_format($maior_media, 2, ',', '.'); ?></p>
        <p><strong>Menor média encontrada:</strong> <?php echo number_format($menor_media, 2, ',', '.'); ?></p>
        <p><strong>Quantidade de aprovados:</strong> <?php echo $aprovados; ?></p>
        <p><strong>Quantidade de recuperações:</strong> <?php echo $recuperacao; ?></p>
        <p><strong>Quantidade de reprovados:</strong> <?php echo $reprovados; ?></p>
        <p><strong>Percentual de aprovação:</strong> <?php echo number_format($percentual_aprovacao, 2, ',', '.'); ?>%</p>
        <p><strong>Soma total de todas as notas:</strong> <?php echo number_format($soma_total_notas, 2, ',', '.'); ?></p>
        <p><strong>Mensagem automática:</strong> <?php echo h($mensagem); ?></p>
    </div>

    <p><a href="<?php echo $_SERVER['PHP_SELF']; ?>">Voltar</a></p>

<?php endif; ?>

</div>
</body>
</html>
