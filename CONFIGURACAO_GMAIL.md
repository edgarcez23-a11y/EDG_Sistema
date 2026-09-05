# ⚙️ Configuração de E-mail com Gmail

Este guia explica como configurar o sistema de envio de e-mails para usar uma conta Gmail real.

---

## 📋 O que você precisa

- Uma conta Gmail ativa (seu `email@gmail.com`)
- Acesso à sua conta no Google
- Uma "Senha de App" do Gmail (não é a senha da conta normal)

---

## 🔐 Passo 1: Ativar Verificação em Duas Etapas

1. Abra **https://myaccount.google.com/security**
2. Procure por "Verificação em duas etapas"
3. Clique em "Ativar" e siga as instruções
   - Você receberá um código via SMS ou app (Google Authenticator, Authy, etc.)

---

## 🔑 Passo 2: Gerar Senha de App do Gmail

1. Acesse **https://myaccount.google.com/apppasswords**
   - *(Nota: Este link só funciona se a verificação em duas etapas está ativada)*

2. Selecione:
   - **App**: `Mail`
   - **Dispositivo**: `Windows Computer` (ou o seu sistema)

3. Clique em **Gerar**

4. O Gmail mostrará uma senha de 16 caracteres. **Copie-a completa** (com espaços):
   ```
   abcd efgh ijkl mnop
   ```

---

## ✏️ Passo 3: Editar o Script de Inicialização

1. Abra a pasta do projeto:
   ```
   e:\Edmar\BD André\oficina_project\
   ```

2. Clique com botão direito em **`iniciar_oficina.bat`** e escolha **"Editar"**

3. Procure pelas linhas de configuração do Gmail (no topo do arquivo):
   ```batch
   set "SMTP_USER=seu_email@gmail.com"
   set "SMTP_PASS=sua_senha_de_app_gmail"
   ```

4. Substitua pelos seus dados reais:
   ```batch
   set "SMTP_USER=seu_email_real@gmail.com"
   set "SMTP_PASS=abcdefghijklmnop"
   ```
   
   ✋ **Exemplo real** (não use estes dados):
   ```batch
   set "SMTP_USER=joao.silva@gmail.com"
   set "SMTP_PASS=hxyz abcd efgh ijkl"
   ```

5. **Salve** o arquivo (Ctrl+S)

---

## 🚀 Passo 4: Iniciar o Sistema

1. Clique duas vezes em **`iniciar_oficina.bat`**
2. Uma janela de terminal abrirá e mostrará:
   ```
   [Gmail] SMTP_USER: seu_email@gmail.com
   [Gmail] SMTP_PASS: *** (configurada)
   ```
3. O navegador abrirá automaticamente no sistema

---

## 📧 Passo 5: Testar o Envio de E-mail

1. Navegue até **Serviços** ou **Orçamentos**
2. Preencha um formulário e tente enviar por e-mail
3. Se der sucesso, a mensagem será entregue ao e-mail informado

---

## ❌ Se Não Funcionar

**Erro: "credenciais SMTP do Gmail não configuradas"**
- Verifique se editou o arquivo `iniciar_oficina.bat` corretamente
- Certifique-se de que `SMTP_USER` e `SMTP_PASS` não estão vazios

**Erro: "Authentication failed"**
- Verifique se a **Verificação em Duas Etapas está ativada** na sua conta
- Verifique se usou a **senha de app** (não a senha da conta normal)
- Copie exatamente a senha fornecida pelo Google (com espaços)

**Erro: "Connection refused"**
- Verifique se seu firewall não bloqueia a porta 465
- Tente reiniciar o script batch

---

## 🔒 Dica de Segurança

- **Nunca compartilhe** o valor de `SMTP_PASS` com ninguém
- A "Senha de App" funciona apenas para este aplicativo
- Se vazar, você pode deletá-la em **https://myaccount.google.com/apppasswords**

---

## 📝 Resumo das Configurações

| Variável | Valor | Descrição |
|----------|-------|-----------|
| `SMTP_HOST` | `smtp.gmail.com` | Servidor Gmail (não mude) |
| `SMTP_PORT` | `465` | Porta segura (não mude) |
| `SMTP_SECURE` | `ssl` | Tipo de segurança (não mude) |
| `SMTP_USER` | `seu_email@gmail.com` | Seu e-mail do Gmail |
| `SMTP_PASS` | `senha de app` | Senha de 16 caracteres gerada pelo Google |

---

**Qualquer dúvida, verifique o arquivo de log de erros do servidor.**
