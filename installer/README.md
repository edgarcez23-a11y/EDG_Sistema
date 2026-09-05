# 🚀 Oficina Inteligente - Instalador NSIS

## 📦 Arquivos para Instalação

Esta pasta contém os arquivos necessários para criar um **instalador profissional** para o sistema Oficina Inteligente.

### Arquivos Inclusos:

| Arquivo | Descrição |
|---------|-----------|
| `oficina-installer.nsi` | Script NSIS (define como o instalador funciona) |
| `compile.bat` | Batch para compilar o instalador automaticamente |
| `GUIA_NSIS.md` | Guia completo com passo a passo detalhado |
| `README.md` | Este arquivo |

---

## ⚡ Quick Start (Inicio Rápido)

### Passo 1: Instale NSIS

Baixe e instale em: https://nsis.sourceforge.io/Download

Ao instalar, escolha a instalação **padrão** (deixe os diretórios como estão).

### Passo 2: Compile o Instalador

Execute o arquivo `compile.bat`:

```bash
duplo clique em compile.bat
```

Ou via terminal:
```bash
cd installer
compile.bat
```

### Passo 3: Seu Instalador Está Pronto!

Procure pelo arquivo:
```
Oficina_Inteligente_Installer.exe
```

Este arquivo estará na pasta **acima** de `installer/` (na raiz do projeto).

---

## 📋 O que o Instalador Faz?

✅ Copia todos os arquivos para `Program Files\OficinaInteligente`  
✅ Cria atalho na **Área de Trabalho**  
✅ Cria entrada no **Menu Iniciar**  
✅ Verifica se PHP está instalado  
✅ Permite desinstalação completa (remover programa)  

---

## 🎯 Para Seus Clientes/Usuários

Cada pessoa que receber o arquivo `Oficina_Inteligente_Installer.exe`:

1. **Duplo clique** no arquivo `.exe`
2. Siga o assistente (clique em "Próximo" e "Instalar")
3. Procure pela **Oficina Inteligente** na Área de Trabalho
4. **Duplo clique** no atalho para iniciar
5. Sistema pronto para usar!

---

## 🔍 Se Algo Não Funcionar

### ❌ "NSIS não encontrado"
- Reinstale NSIS desde https://nsis.sourceforge.io/Download
- Escolha instalação padrão em `C:\Program Files (x86)\NSIS`

### ❌ "Erro ao compilar"
- Verifique se todos os arquivos do projeto existem
- Leia o arquivo `GUIA_NSIS.md` para solução de problemas

### ❌ "Arquivo exe é muito grande"
- Normal! O instalador inclui todos os arquivos do projeto
- Você pode compactá-lo mais depois

---

## 📚 Documentação Completa

Para um guia **passo a passo detalhado**, leia:
```
installer/GUIA_NSIS.md
```

Este arquivo contém:
- Instalação passo a passo do NSIS
- 4 formas diferentes de compilar
- Customizações opcionais
- Solução de problemas completa

---

## 🎨 Personalizações

Para modificar o instalador (nome, ícone, texto):

1. Abra `oficina-installer.nsi` em um editor de texto
2. Localize as seções de configuração no topo
3. Modifique conforme necessário
4. Recompile com `compile.bat`

**Exemplo de mudanças comuns:**

```nsi
; Mudar nome da aplicação
Name "Minha Oficina v2.0"

; Mudar diretório de instalação
InstallDir "$PROGRAMFILES\Minha Oficina"

; Mudar nome do arquivo exe
OutFile "..\Minha_Oficina_Installer.exe"
```

---

## 📤 Distribuindo o Instalador

Após criar o arquivo `.exe`, você pode:

### 📧 Email
- Envie direto como anexo
- Tamanho típico: 10-50 MB

### 💾 Pendrive
- Copie o `.exe` para um pendrive
- Entregue aos clientes

### 🌐 Website
- Publique em seu servidor de downloads
- Disponibilize link de download

### ☁️ Google Drive / OneDrive
- Compartilhe na nuvem
- Gere link de acesso público

---

## ✨ Próximas Etapas

Após a instalação pelos usuários:

1. **Acessar o Sistema**
   - Clique no atalho da Área de Trabalho
   - Será aberto em: `http://127.0.0.1:8000/public/login.php`

2. **Primeiro Login**
   - Email: (cadastrado durante registro)
   - Senha: (sua senha)

3. **Configurar Banco de Dados** (se necessário)
   - O SQLite será usado por padrão
   - Arquivo: `oficina.sqlite` (criado automaticamente)

4. **Opcional: Configurar Email**
   - Edite: `iniciar_oficina.bat`
   - Adicione suas credenciais Gmail
   - Leia: `CONFIGURACAO_GMAIL.md`

---

## 🆘 Suporte

Dúvidas sobre NSIS?
- Documentação: https://nsis.sourceforge.io/Docs
- Tutorial: https://nsis.sourceforge.io/Simple_Step_by_Step_Guide

Dúvidas sobre o Sistema?
- Leia: `README.md` (raiz do projeto)
- Leia: `README_EXECUTAVEL.md`

---

## 📄 Licença

Este instalador NSIS é fornecido como está. Sinta-se livre para modificar conforme necessário.

---

## 🎉 Pronto!

Seu sistema agora está profissional e pronto para distribuição!

Qualquer dúvida, leia o arquivo `GUIA_NSIS.md` para informações mais detalhadas.

**Boa sorte! 🚀**
