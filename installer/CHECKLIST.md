# 📋 Checklist: Criar Instalador NSIS - Passo a Passo

## ✅ PASSO 1: Instalar NSIS

- [ ] Acesse: https://nsis.sourceforge.io/Download
- [ ] Baixe a versão mais recente (ex: `nsis-3.x-setup.exe`)
- [ ] Execute o arquivo `.exe`
- [ ] Clique em "Next" nas telas
- [ ] Deixe o diretório padrão: `C:\Program Files (x86)\NSIS`
- [ ] Clique em "Install"
- [ ] Aguarde a instalação
- [ ] Clique em "Finish"

**✓ NSIS Instalado!**

---

## ✅ PASSO 2: Verificar Estrutura de Pastas

Sua pasta deve estar assim:

```
oficina_project/
├── installer/                      ← Você está aqui
│   ├── oficina-installer.nsi       ✓ Deve existir
│   ├── compile.bat                 ✓ Deve existir
│   ├── GUIA_NSIS.md                ✓ Deve existir
│   └── README.md                   ✓ Deve existir
│
├── assets/                         ✓ Deve existir
├── inc/                            ✓ Deve existir
├── public/                         ✓ Deve existir
├── vendor/                         ✓ Deve existir
├── iniciar_oficina.bat             ✓ Deve existir
├── composer.json                   ✓ Deve existir
├── create_db.sql                   ✓ Deve existir
└── oficina.sqlite                  ✓ Deve existir
```

Checklist:
- [ ] Pasta `installer/` existe
- [ ] Arquivo `oficina-installer.nsi` existe
- [ ] Arquivo `compile.bat` existe
- [ ] Arquivo `iniciar_oficina.bat` existe (na raiz)
- [ ] Pasta `public/` existe
- [ ] Pasta `inc/` existe
- [ ] Pasta `vendor/` existe
- [ ] Pasta `assets/` existe

**✓ Estrutura OK!**

---

## ✅ PASSO 3: Compilar o Instalador

### Opção A: Forma Mais Fácil (Recomendado)

1. [ ] Abra a pasta `installer/`
2. [ ] Duplo clique em `compile.bat`
3. [ ] Uma janela preta vai abrir
4. [ ] Aguarde a mensagem: `[✓] SUCESSO!`
5. [ ] Pressione qualquer tecla para fechar

### Opção B: Via Terminal

1. [ ] Abra o PowerShell ou CMD
2. [ ] Digite: `cd C:\Caminho\para\oficina_project\installer`
3. [ ] Digite: `compile.bat`
4. [ ] Aguarde a mensagem de sucesso

### Opção C: Direto com NSIS

1. [ ] Abra a pasta `C:\Program Files (x86)\NSIS`
2. [ ] Encontre `makensis.exe`
3. [ ] Arraste o arquivo `oficina-installer.nsi` sobre `makensis.exe`
4. [ ] Aguarde a compilação

**✓ Compilação Iniciada!**

---

## ✅ PASSO 4: Verificar o Resultado

Após a compilação:

1. [ ] Volte para a pasta `oficina_project/` (acima de `installer/`)
2. [ ] Procure por um arquivo chamado: `Oficina_Inteligente_Installer.exe`
3. [ ] Se existe, compilou com sucesso! ✓
4. [ ] Se não existe, leia a mensagem de erro e revise os passos

**Tamanho esperado:** 10-50 MB (normal!)

**✓ Instalador Criado!**

---

## ✅ PASSO 5: Testar o Instalador

### Teste em seu PC (Recomendado)

1. [ ] Duplo clique em `Oficina_Inteligente_Installer.exe`
2. [ ] Uma janela do "Assistente de Instalação" abre
3. [ ] Clique em "Next" (Próximo)
4. [ ] Escolha pasta de instalação (deixe padrão)
5. [ ] Clique em "Install" (Instalar)
6. [ ] Aguarde a instalação
7. [ ] Clique em "Finish" (Concluir)
8. [ ] Procure pela `Oficina Inteligente` na Área de Trabalho
9. [ ] Duplo clique no atalho
10. [ ] O sistema deve abrir em seu navegador

**✓ Teste Completo!**

---

## ✅ PASSO 6: Distribuir o Instalador

Seu arquivo `Oficina_Inteligente_Installer.exe` agora pode ser:

### 📧 Enviado por Email
- [ ] Anexe o arquivo
- [ ] Envie para seus clientes
- [ ] Instruções: "Duplo clique e siga o assistente"

### 💾 Copiado para Pendrive
- [ ] Conecte pendrive
- [ ] Copie `Oficina_Inteligente_Installer.exe`
- [ ] Entregue aos clientes

### 🌐 Publicado em Website
- [ ] Copie para seu servidor web
- [ ] Crie link de download
- [ ] Compartilhe o link

### ☁️ Compartilhado na Nuvem
- [ ] Acesse Google Drive / OneDrive
- [ ] Suba o arquivo
- [ ] Gere link compartilhável
- [ ] Envie para clientes

**✓ Pronto para Distribuição!**

---

## 🎯 Instruções para Seus Clientes

Envie este texto simples para cada pessoa:

> **Oficina Inteligente - Instruções de Instalação**
>
> 1. Execute o arquivo: `Oficina_Inteligente_Installer.exe`
> 2. Clique em "Próximo" em todas as telas
> 3. Aguarde a instalação terminar
> 4. Clique em "Concluir"
> 5. Procure o atalho "Oficina Inteligente" na Área de Trabalho
> 6. Duplo clique para abrir
> 7. Faça login (crie uma conta na primeira vez)
> 8. Pronto! Sistema está pronto para usar

---

## ⚠️ Se Algo Deu Errado

### ❌ Erro: "NSIS não encontrado"
- [ ] Reinstale NSIS desde: https://nsis.sourceforge.io/Download
- [ ] Deixe a pasta padrão: `C:\Program Files (x86)\NSIS`
- [ ] Reinicie o computador
- [ ] Tente compilar novamente

### ❌ Erro: "Arquivo .nsi não encontrado"
- [ ] Verifique se `oficina-installer.nsi` existe em `installer/`
- [ ] Se não, copie do backup ou recrie-o
- [ ] Execute `compile.bat` novamente

### ❌ Erro: "Caminhos dos arquivos inválidos"
- [ ] Verifique se `public/`, `inc/`, `vendor/`, `assets/` existem
- [ ] Verifique se estão na pasta certa
- [ ] Edite `oficina-installer.nsi` e corrija os caminhos

### ❌ Instalador não funciona na máquina de clientes
- [ ] Verifique se PHP está instalado no cliente
- [ ] Se não, envie link: https://www.php.net/downloads
- [ ] Ou recomende: https://www.apachefriends.org (XAMPP)
- [ ] Teste em um PC sem NSIS instalado

### ❌ Outras dúvidas
- [ ] Leia o arquivo: `GUIA_NSIS.md`
- [ ] Consulte: https://nsis.sourceforge.io/Docs

---

## 📊 Checklist Final

Antes de distribuir, verifique:

- [ ] Arquivo `.exe` foi criado
- [ ] Arquivo `.exe` tem mais de 1 MB
- [ ] Testou a instalação em seu PC
- [ ] Atalho aparece na Área de Trabalho
- [ ] Sistema abre ao clicar no atalho
- [ ] Login funciona
- [ ] Banco de dados funciona

---

## 🎉 PRONTO!

Seu sistema agora está:
- ✅ Profissional
- ✅ Fácil de instalar
- ✅ Pronto para distribuir
- ✅ Funcional em qualquer PC Windows

**Parabéns! 🚀**

Você tem um instalador profissional para sua Oficina Inteligente!

---

## 📞 Dúvidas?

- **Sobre NSIS:** https://nsis.sourceforge.io
- **Sobre o Projeto:** Leia `README.md` e `README_EXECUTAVEL.md`
- **Sobre Instalação:** Leia `GUIA_NSIS.md`

**Sucesso! 💪**
