# 🔐 Sistema de Autenticação - Oficina Inteligente

Este documento descreve o novo sistema de login e autenticação implementado na aplicação.

---

## 📋 Componentes do Sistema

### 1. **Tabela de Usuários** (`inc/db.php`)
A tabela `usuarios` armazena:
- `id_usuario`: ID único do usuário
- `email`: Email único (login)
- `senha`: Senha criptografada com `password_hash()`
- `nome`: Nome do usuário
- `ativo`: Status (1 = ativo, 0 = inativo)
- `data_criacao`: Data de criação

**Criada automaticamente** no SQLite ao iniciar o sistema pela primeira vez.

### 2. **Módulo de Autenticação** (`inc/auth.php`)
Funções disponíveis:

| Função | Descrição |
|--------|-----------|
| `is_logged_in()` | Verifica se usuário está logado |
| `require_login()` | Redireciona para login se não autenticado |
| `fazer_login($email, $senha)` | Realiza autenticação |
| `fazer_registro($email, $senha, $nome)` | Cria novo usuário |
| `fazer_logout()` | Encerra sessão |
| `get_usuario_logado()` | Retorna dados do usuário |

### 3. **Página de Login** (`public/login.php`)
Tela com dois formulários:
- **Login**: Para usuários existentes
- **Registro**: Para criar nova conta

**Recursos**:
- Toggle entre formulários de login/registro
- Validação de campos
- Mensagens de erro/sucesso
- Design responsivo com Bootstrap

### 4. **Páginas Protegidas**
Todas as páginas principais agora exigem autenticação:
- `clientes.php`
- `fornecedores.php`
- `manutencoes.php`
- `pecas.php`
- `solicitacoes.php`
- `orcamentos.php`
- `servicos.php`
- `relatorios.php`

Se um usuário não autenticado tenta acessar, é redirecionado para `login.php`.

### 5. **Página de Logout** (`public/logout.php`)
Destrói a sessão e redireciona para login.

---

## 🚀 Como Usar

### Primeiro Acesso (Criar Conta)

1. Abra http://localhost:8000/public/login.php
2. Clique em **"Crie uma agora"**
3. Preencha os dados:
   - **Nome Completo**: Seu nome
   - **Email**: Seu email (será o login)
   - **Senha**: Mínimo 6 caracteres
   - **Confirmar Senha**: Repita a mesma senha
4. Clique em **"Criar Conta"**

### Login

1. Abra http://localhost:8000/public/login.php
2. Insira:
   - **Email**: Seu email de cadastro
   - **Senha**: Sua senha
3. Clique em **"Entrar"**
4. Será redirecionado para a página inicial (`index.php`)

### Logout

1. Clique no botão **"Sair"** (canto superior direito)
2. Será redirecionado para a página de login

---

## 🔒 Segurança

### Boas Práticas Implementadas

✅ **Senhas Criptografadas**
- Usando `password_hash()` com `PASSWORD_DEFAULT` (bcrypt)
- Verificação com `password_verify()`

✅ **Validação de Email**
- Usa `filter_var()` com `FILTER_VALIDATE_EMAIL`

✅ **Email Único**
- Impede registro com email duplicado

✅ **Sessões Seguras**
- Uso de `$_SESSION` do PHP
- Destruição completa ao logout

✅ **Redirecionamentos Automáticos**
- Usuários não autenticados não conseguem acessar páginas protegidas

### Recomendações Adicionais (Futuro)

⚠️ Implementar em produção:
- HTTPS/SSL obrigatório
- Rate limiting para login (limite tentativas)
- Proteção CSRF (tokens)
- 2FA (autenticação de dois fatores)
- Logs de acesso e auditoria

---

## 📊 Fluxo de Autenticação

```
┌─────────────────────────────────────────────────┐
│ Usuário acessa /public/index.php                │
└──────────────────────┬──────────────────────────┘
                       │
                   require_login()
                       │
                ┌──────┴──────┐
                │             │
         Autenticado?   NÃO   │
                │             │
               SIM            │
                │      ┌──────▼───────────┐
                │      │ Redireciona para │
                │      │ /public/login.php │
                │      └──────────────────┘
                │
         ┌──────▼──────────┐
         │ Continua/Exibe  │
         │  Página (HTML)  │
         └─────────────────┘
```

---

## 🐛 Possíveis Erros

### "Email ou senha incorretos"
- Verificar se email está correto
- Verificar se senha está correta (case-sensitive)
- Confirmar que a conta foi criada (tentar registrar de novo)

### "Email já cadastrado"
- O email já existe no sistema
- Tente fazer login ou use outro email

### "As senhas não correspondem"
- A confirmação de senha não bateu com a senha
- Verifique se ambas estão iguais

### Redirecionado para login constantemente
- A sessão pode ter expirado
- Cookie de sessão pode estar desativado no navegador
- Tente limpar cookies e fazer login novamente

---

## 📁 Estrutura de Arquivos

```
oficina_project/
├── inc/
│   ├── auth.php          ← Funções de autenticação
│   ├── db.php            ← Conexão e tabelas (incl. usuarios)
│   └── enviar_email.php
├── public/
│   ├── login.php         ← Página de login/registro
│   ├── logout.php        ← Saída (destrui sessão)
│   ├── index.php         ← Página principal (protegida)
│   ├── clientes.php      ← (protegida)
│   ├── fornecedores.php  ← (protegida)
│   ├── manutencoes.php   ← (protegida)
│   ├── pecas.php         ← (protegida)
│   ├── solicitacoes.php  ← (protegida)
│   ├── orcamentos.php    ← (protegida)
│   ├── servicos.php      ← (protegida)
│   └── relatorios.php    ← (protegida)
└── ...
```

---

## ✅ Checklist de Implementação

- [x] Tabela de usuários criada no banco
- [x] Função `fazer_login()` implementada
- [x] Função `fazer_registro()` implementada
- [x] Função `require_login()` implementada
- [x] Página `login.php` criada
- [x] Página `logout.php` criada
- [x] `index.php` protegida
- [x] Todas as páginas protegidas
- [x] Navbar com nome do usuário
- [x] Botão de logout
- [x] Validação de senha (mínimo 6 caracteres)
- [x] Validação de email
- [x] Proteção contra email duplicado
- [x] Senhas criptografadas com bcrypt

---

## 🎯 Próximos Passos (Opcional)

1. **2FA (Autenticação de Dois Fatores)**
   - SMS ou email de verificação

2. **Recuperação de Senha**
   - Email com link de reset

3. **Perfis de Usuário (Admin, Técnico, Cliente)**
   - Controle de acesso por papel

4. **Auditoria**
   - Log de quem fez o quê e quando

5. **Integração OAuth**
   - Login com Google, GitHub, etc.

---

**Desenvolvido com ❤️ para Oficina Inteligente**
