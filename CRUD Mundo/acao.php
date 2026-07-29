<?php

// Conexão com o banco de dados.
require_once "conexao.php";

// Recebe a ação, o tipo e o ID enviados pelo formulário.
$acao = $_POST["acao"] ?? "";
$tipo = $_POST["tipo"] ?? "";
$id   = $_POST["id"] ?? "";

try {

    // CONTINENTES
    if ($acao == "salvar" && $tipo == "continente") {

        if ($id != "") {

            // Atualiza um continente.
            $sql = "UPDATE continentes
                    SET nome = ?,
                        populacao = ?,
                        area = ?,
                        total_paises = ?
                    WHERE id = ?";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["total_paises"],
                $id
            ]);

            $mensagem = "Continente atualizado com sucesso.";

        } else {

            // Cadastra um novo continente.
            $sql = "INSERT INTO continentes
                    (nome, populacao, area, total_paises)
                    VALUES (?, ?, ?, ?)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["total_paises"]
            ]);

            $mensagem = "Continente cadastrado com sucesso.";
        }
    }


    // GOVERNANTES
    if ($acao == "salvar" && $tipo == "governante") {

        if ($id != "") {

            // Atualiza um governante.
            $sql = "UPDATE governantes
                    SET nome = ?,
                        partido_politico = ?,
                        data_nascimento = ?,
                        idade = ?,
                        data_inicio_mandato = ?,
                        data_fim_mandato = ?
                    WHERE id = ?";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["partido_politico"],
                $_POST["data_nascimento"],
                $_POST["idade"],
                $_POST["data_inicio_mandato"],
                $_POST["data_fim_mandato"] != "" ? $_POST["data_fim_mandato"] : null,
                $id
            ]);

            $mensagem = "Governante atualizado com sucesso.";

        } else {

            // Cadastra um novo governante.
            $sql = "INSERT INTO governantes
                    (nome, partido_politico, data_nascimento, idade, data_inicio_mandato, data_fim_mandato)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["partido_politico"],
                $_POST["data_nascimento"],
                $_POST["idade"],
                $_POST["data_inicio_mandato"],
                $_POST["data_fim_mandato"] != "" ? $_POST["data_fim_mandato"] : null
            ]);

            $mensagem = "Governante cadastrado com sucesso.";
        }
    }


    // PAÍSES
    if ($acao == "salvar" && $tipo == "pais") {

        $governante = $_POST["governante_id"] != "" ? $_POST["governante_id"] : null;

        if ($id != "") {

            // Atualiza um país.
            $sql = "UPDATE paises
                    SET nome = ?,
                        continente_id = ?,
                        populacao = ?,
                        area = ?,
                        idioma = ?,
                        governante_id = ?,
                        clima = ?,
                        regime_politico = ?,
                        moeda = ?
                    WHERE id = ?";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["continente_id"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["idioma"],
                $governante,
                $_POST["clima"],
                $_POST["regime_politico"],
                $_POST["moeda"],
                $id
            ]);

            $mensagem = "País atualizado com sucesso.";

        } else {

            // Cadastra um novo país.
            $sql = "INSERT INTO paises
                    (nome, continente_id, populacao, area, idioma, governante_id, clima, regime_politico, moeda)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["continente_id"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["idioma"],
                $governante,
                $_POST["clima"],
                $_POST["regime_politico"],
                $_POST["moeda"]
            ]);

            $mensagem = "País cadastrado com sucesso.";
        }
    }


    // CIDADES
    if ($acao == "salvar" && $tipo == "cidade") {

        $governante = $_POST["governante_id"] != "" ? $_POST["governante_id"] : null;
        $fundacao    = $_POST["data_fundacao"] != "" ? $_POST["data_fundacao"] : null;

        if ($id != "") {

            // Atualiza uma cidade.
            $sql = "UPDATE cidades
                    SET nome = ?,
                        pais_id = ?,
                        populacao = ?,
                        area = ?,
                        clima = ?,
                        governante_id = ?,
                        data_fundacao = ?
                    WHERE id = ?";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["pais_id"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["clima"],
                $governante,
                $fundacao,
                $id
            ]);

            $mensagem = "Cidade atualizada com sucesso.";

        } else {

            // Cadastra uma nova cidade.
            $sql = "INSERT INTO cidades
                    (nome, pais_id, populacao, area, clima, governante_id, data_fundacao)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["pais_id"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["clima"],
                $governante,
                $fundacao
            ]);

            $mensagem = "Cidade cadastrada com sucesso.";
        }
    }


    // EXCLUSÕES
    if ($acao == "deletar") {

        if ($tipo == "continente") {
            $sql = "DELETE FROM continentes WHERE id = ?";
        }

        if ($tipo == "governante") {
            $sql = "DELETE FROM governantes WHERE id = ?";
        }

        if ($tipo == "pais") {
            $sql = "DELETE FROM paises WHERE id = ?";
        }

        if ($tipo == "cidade") {
            $sql = "DELETE FROM cidades WHERE id = ?";
        }

        $comando = $pdo->prepare($sql);
        $comando->execute([$id]);

        $mensagem = "Registro excluído com sucesso.";
    }


    // Retorna para a página principal.
    header("Location: index.php?msg=" . urlencode($mensagem ?? "Ação realizada."));
    exit;

} catch (PDOException $erro) {

    die("Erro ao executar a ação: " . $erro->getMessage());
}

?>
```
