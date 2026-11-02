<?php

// Conexão com o banco de dados.
require_once "conexao.php";
require_once "auth.php";
verificarLogin();

// Recebe a ação, o tipo e o ID enviados pelo formulário (POST) ou pelos links de excluir (GET).
$acao = $_REQUEST["acao"] ?? "";
$tipo = $_REQUEST["tipo"] ?? "";
$id   = $_REQUEST["id"] ?? "";

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

        $governante = ($_POST["governante_id"] ?? "") !== "" ? $_POST["governante_id"] : null;
        $codigoIso = strtoupper(trim($_POST["codigo_iso"] ?? ""));
        $codigoIso = $codigoIso !== "" ? $codigoIso : null;
        $capital = trim($_POST["capital"] ?? "");
        $capital = $capital !== "" ? $capital : null;
        $regiao = trim($_POST["regiao"] ?? "");
        $regiao = $regiao !== "" ? $regiao : null;
        $subRegiao = trim($_POST["sub_regiao"] ?? "");
        $subRegiao = $subRegiao !== "" ? $subRegiao : null;

        if ($codigoIso !== null && !preg_match('/^[A-Z]{2}$/', $codigoIso)) {
            throw new InvalidArgumentException("O código ISO deve ter exatamente duas letras.");
        }

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
                        moeda = ?,
                        codigo_iso = ?,
                        capital = ?,
                        regiao = ?,
                        sub_regiao = ?
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
                $codigoIso,
                $capital,
                $regiao,
                $subRegiao,
                $id
            ]);

            $mensagem = "País atualizado com sucesso.";

        } else {

            // Cadastra um novo país.
            $sql = "INSERT INTO paises
                    (nome, continente_id, populacao, area, idioma, governante_id, clima, regime_politico, moeda,
                     codigo_iso, capital, regiao, sub_regiao)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
                $codigoIso,
                $capital,
                $regiao,
                $subRegiao
            ]);

            $mensagem = "País cadastrado com sucesso.";
        }
    }


    // CIDADES
    if ($acao == "salvar" && $tipo == "cidade") {

        $governante = ($_POST["governante_id"] ?? "") !== "" ? $_POST["governante_id"] : null;
        $fundacao = ($_POST["data_fundacao"] ?? "") !== "" ? $_POST["data_fundacao"] : null;
        $latitude = ($_POST["latitude"] ?? "") !== "" ? (float)$_POST["latitude"] : null;
        $longitude = ($_POST["longitude"] ?? "") !== "" ? (float)$_POST["longitude"] : null;

        if (($latitude === null) !== ($longitude === null)) {
            throw new InvalidArgumentException("Informe latitude e longitude juntas para exibir a cidade no mapa.");
        }
        if (($latitude !== null && ($latitude < -90 || $latitude > 90)) ||
            ($longitude !== null && ($longitude < -180 || $longitude > 180))) {
            throw new InvalidArgumentException("As coordenadas informadas são inválidas.");
        }

        if ($id != "") {

            // Atualiza uma cidade.
            $sql = "UPDATE cidades
                    SET nome = ?,
                        pais_id = ?,
                        populacao = ?,
                        area = ?,
                        clima = ?,
                        governante_id = ?,
                        data_fundacao = ?,
                        latitude = ?,
                        longitude = ?
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
                $latitude,
                $longitude,
                $id
            ]);

            $mensagem = "Cidade atualizada com sucesso.";

        } else {

            // Cadastra uma nova cidade.
            $sql = "INSERT INTO cidades
                    (nome, pais_id, populacao, area, clima, governante_id, data_fundacao, latitude, longitude)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $comando = $pdo->prepare($sql);

            $comando->execute([
                $_POST["nome"],
                $_POST["pais_id"],
                $_POST["populacao"],
                $_POST["area"],
                $_POST["clima"],
                $governante,
                $fundacao,
                $latitude,
                $longitude
            ]);

            $mensagem = "Cidade cadastrada com sucesso.";
        }
    }


    // USUÁRIOS (cadastro de quem pode acessar o sistema)
    if ($acao == "salvar" && $tipo == "usuario") {

        $nome = $_POST["nome"] ?? "";
        $login = trim($_POST["login"] ?? "");
        $senha = $_POST["senha"] ?? "";
        $confirmarSenha = $_POST["confirmar_senha"] ?? "";

        if ($senha !== $confirmarSenha) {

            $mensagem = "A senha e a confirmação de senha não são iguais.";

        } elseif (strlen($senha) < 6) {

            $mensagem = "A senha deve ter pelo menos 6 caracteres.";

        } else {

            // Nunca guardamos a senha "pura": ela sempre vira um hash antes de ir pro banco.
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nome, login, senha, primeiro_acesso) VALUES (?, ?, ?, 1)";
            $comando = $pdo->prepare($sql);
            $comando->execute([$nome, $login, $hash]);

            $log = $pdo->prepare("INSERT INTO logs (usuario_id, login_tentado, acao) VALUES (?, ?, ?)");
            $log->execute([$pdo->lastInsertId(), $login, "Novo usuário cadastrado"]);

            $mensagem = "Usuário cadastrado com sucesso. Ele(a) vai precisar trocar a senha no primeiro acesso.";
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

} catch (InvalidArgumentException $erro) {

    header("Location: index.php?msg=" . urlencode($erro->getMessage()));
    exit;

} catch (PDOException $erro) {

    // O MySQL manda o código de erro "de verdade" dentro de errorInfo[1].
    $codigoMysql = $erro->errorInfo[1] ?? null;

    if ($codigoMysql == 1451) {
        // 1451 = violação de chave estrangeira (ex.: excluir um continente
        // que ainda tem países vinculados, ou um país que tem cidades vinculadas).
        $mensagem = "Não foi possível excluir: existem registros vinculados a este item.";

    } elseif ($codigoMysql == 1062) {
        // 1062 = valor repetido em uma coluna UNIQUE (login ou código ISO).
        $mensagem = "Já existe um registro com esse login ou código ISO. Confira os dados informados.";

    } else {
        $mensagem = "Erro ao executar a ação: " . $erro->getMessage();
    }

    header("Location: index.php?msg=" . urlencode($mensagem));
    exit;
}
