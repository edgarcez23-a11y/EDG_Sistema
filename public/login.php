<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Oficina Inteligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: rgba(255,255,255,0.8);
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-weight: 500;
            background-color: #667eea;
            border-color: #667eea;
        }
        .btn-login:hover {
            background-color: #5568d3;
            border-color: #5568d3;
        }
        .toggle-form-link {
            text-align: center;
            margin-top: 1rem;
        }
        .toggle-form-link a {
            color: #667eea;
            text-decoration: none;
            cursor: pointer;
        }
        .toggle-form-link a:hover {
            text-decoration: underline;
        }
        .alert {
            margin-bottom: 1.5rem;
        }
        .form-divider {
            text-align: center;
            margin: 1.5rem 0;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card bg-white">
            <div class="login-header">
                <h1>Oficina Inteligente</h1>
                <p id="form-title">Faça login em sua conta</p>
            </div>

            <?php
            require_once __DIR__ . '/../inc/db.php';
            require_once __DIR__ . '/../inc/auth.php';

            $mensagem = '';
            $tipo_alerta = 'danger';

            // Verifica se já está logado
            if (is_logged_in()) {
                header('Location: index.php');
                exit;
            }

            // Processa formulário de login
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'login') {
                $email = $_POST['email'] ?? '';
                $senha = $_POST['senha'] ?? '';

                $resultado = fazer_login($email, $senha);
                if ($resultado === true) {
                    header('Location: index.php');
                    exit;
                } else {
                    $mensagem = $resultado;
                    $tipo_alerta = 'danger';
                }
            }

            // Processa formulário de registro
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'registro') {
                $nome = $_POST['nome'] ?? '';
                $email = $_POST['email_reg'] ?? '';
                $senha = $_POST['senha_reg'] ?? '';
                $confirmar_senha = $_POST['confirmar_senha'] ?? '';

                if ($senha !== $confirmar_senha) {
                    $mensagem = 'As senhas não correspondem.';
                    $tipo_alerta = 'danger';
                } else {
                    $resultado = fazer_registro($email, $senha, $nome);
                    if ($resultado === true) {
                        $mensagem = 'Cadastro realizado com sucesso! Faça login agora.';
                        $tipo_alerta = 'success';
                    } else {
                        $mensagem = $resultado;
                        $tipo_alerta = 'danger';
                    }
                }
            }

            if ($mensagem) {
                echo "<div class=\"alert alert-{$tipo_alerta} alert-dismissible fade show\" role=\"alert\">" .
                     htmlspecialchars($mensagem) .
                     "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>" .
                     "</div>";
            }
            ?>

            <!-- Formulário de Login -->
            <form id="login-form" method="POST" action="">
                <input type="hidden" name="acao" value="login">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Sua senha" required>
                </div>

                <button type="submit" class="btn btn-primary btn-login">Entrar</button>

                <div class="toggle-form-link">
                    Não tem conta? <a onclick="toggleForms()">Crie uma agora</a>
                </div>
            </form>

            <!-- Formulário de Registro -->
            <form id="registro-form" method="POST" action="" style="display: none;">
                <input type="hidden" name="acao" value="registro">

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Seu nome" required>
                </div>

                <div class="mb-3">
                    <label for="email_reg" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email_reg" name="email_reg" placeholder="seu@email.com" required>
                </div>

                <div class="mb-3">
                    <label for="senha_reg" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha_reg" name="senha_reg" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" required>
                </div>

                <button type="submit" class="btn btn-success btn-login">Criar Conta</button>

                <div class="toggle-form-link">
                    Já tem conta? <a onclick="toggleForms()">Faça login</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const registroForm = document.getElementById('registro-form');
            const formTitle = document.getElementById('form-title');

            loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
            registroForm.style.display = registroForm.style.display === 'none' ? 'block' : 'none';

            if (loginForm.style.display === 'none') {
                formTitle.textContent = 'Crie sua conta';
            } else {
                formTitle.textContent = 'Faça login em sua conta';
            }
        }
    </script>
</body>
</html>
