<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$dbPath = __DIR__ . '/dados.db';
$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Criar tabela
$pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    email TEXT NOT NULL
)");

// Se veio com ID (edição), busca dados para preencher o formulário
$editando = false;
$usuarioEdicao = ['id' => '', 'nome' => '', 'email' => ''];

if (isset($_GET['editar'])) {
    $editando = true;
    $idEditar = (int) $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$idEditar]);
    $usuarioEdicao = $stmt->fetch(PDO::FETCH_ASSOC) ?? $usuarioEdicao;
}

if (isset($_GET['deletar'])) {
    try {
        $idDeletar = (int) $_GET['deletar']; 
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$idDeletar]); 

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    } catch (PDOException $e) {
        echo "Erro ao deletar usuário: " . $e->getMessage();
    }
}

// Inserir ou atualizar
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($id) {
        // Atualizar
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        $stmt->execute([$nome, $email, $id]);
    } else {
        // Inserir novo
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->execute([$nome, $email]);
    }

    // Redirecionar para limpar o formulário e evitar reenvio
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Buscar todos os usuários
$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Formulário com Edição e Exclusão de dados</title>
</head>
<body>
    <h2><?= $editando ? "Editar Usuário" : "Cadastrar Novo Usuário" ?></h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($usuarioEdicao['id']) ?>">

        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($usuarioEdicao['nome']) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($usuarioEdicao['email']) ?>" required><br><br>

        <input type="submit" value="<?= $editando ? "Atualizar" : "Salvar" ?>">
        <?php if ($editando): ?>
            <a href="<?= $_SERVER['PHP_SELF'] ?>">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Usuários Cadastrados</h2>
    <?php if (count($usuarios) > 0): ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Editar</th>
                <th>Apagar</th>
            </tr>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><a href="?editar=<?= $u['id'] ?>">Editar</a></td>
                    <td><a href="?deletar=<?= $u['id'] ?>">Deletar</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum usuário encontrado.</p>
    <?php endif; ?>
</body>
</html>