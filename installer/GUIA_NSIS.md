# 📦 Guia Completo: Criar Instalador NSIS para Oficina Inteligente

## 🎯 O que é NSIS?
**NSIS** (Nullsoft Scriptable Install System) é um programa profissional e gratuito para criar instaladores Windows (.exe). Ele cria instaladores compactos, rápidos e profissionais.

---

## 📋 Pré-requisitos

### Você vai precisar de:
1. **NSIS Compilador** - baixe em: https://nsis.sourceforge.io/Download
2. **Script NSIS** - já incluído em: `installer/oficina-installer.nsi`
3. **Arquivos do projeto** - mesma pasta onde está o script

---

## 🚀 Passo a Passo: Instalação e Compilação

### 1️⃣ Instalar NSIS no Windows

#### Opção A: Download Manual
1. Acesse https://nsis.sourceforge.io/Download
2. Baixe a versão mais recente (ex: `nsis-3.x-setup.exe`)
3. Execute o instalador
4. Escolha o diretório (padrão: `C:\Program Files (x86)\NSIS`)
5. Complete a instalação

#### Opção B: Instalação via PowerShell (Admin)
```powershell
# Copiar e colar no PowerShell como Admin
choco install nsis -y
```
ou manualmente:
```powershell
$nsisUrl = "https://sourceforge.net/projects/nsis/files/NSIS%203/3.10/nsis-3.10-setup.exe"
$nsisPath = "$env:TEMP\nsis-setup.exe"
Invoke-WebRequest -Uri $nsisUrl -OutFile $nsisPath
& $nsisPath
```

---

### 2️⃣ Preparar a Estrutura de Pastas

Organize seus arquivos assim:

```
oficina_project/
├── installer/
│   ├── oficina-installer.nsi          (script NSIS)
│   └── compile.bat                    (batch para compilar)
├── assets/
├── inc/
├── public/
├── vendor/
├── iniciar_oficina.bat
├── composer.json
├── create_db.sql
├── oficina.sqlite
└── outros arquivos...
```

Se não tiver a pasta `installer`, crie:
```bash
mkdir installer
cd installer
```

---

### 3️⃣ Colocar o Script NSIS na Pasta

O arquivo `oficina-installer.nsi` já está em:
```
oficina_project/installer/oficina-installer.nsi
```

---

### 4️⃣ Compilar o Instalador

#### Opção A: Clicar Diretamente no Script NSIS

1. Abra `C:\Program Files (x86)\NSIS`
2. Encontre o arquivo `makensis.exe`
3. Crie um atalho dele na sua pasta `installer/`
4. Arraste o arquivo `oficina-installer.nsi` sobre o `makensis.exe`

#### Opção B: Usar o Menu Contextual (Recomendado)

1. Instale a integração com o menu:
   - Abra `NSIS`
   - Vá em: `Menu Iniciar > NSIS > Edit NSIS Script`
   - Selecione seu arquivo `.nsi`
   - Clique em `Compile NSI Scripts`

#### Opção C: Batch Automático (Mais Prático)

Crie um arquivo `compile.bat` na pasta `installer/`:

```batch
@echo off
REM Oficina Inteligente - Compilador NSIS
REM ======================================

set NSIS_PATH=C:\Program Files (x86)\NSIS\makensis.exe

if not exist "%NSIS_PATH%" (
    echo ERRO: NSIS não foi encontrado em %NSIS_PATH%
    echo Instale NSIS primeiro: https://nsis.sourceforge.io/Download
    pause
    exit /b 1
)

echo Compilando o instalador...
echo.

"%NSIS_PATH%" /V4 oficina-installer.nsi

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✓ Sucesso! Instalador criado: Oficina_Inteligente_Installer.exe
    echo.
    echo O arquivo está na pasta do projeto (um nível acima)
    pause
) else (
    echo.
    echo ✗ Erro ao compilar o script NSIS
    pause
    exit /b 1
)
```

Depois, execute:
```bash
cd installer
compile.bat
```

#### Opção D: Linha de Comando PowerShell

```powershell
# Navegue até a pasta installer
cd "C:\Caminho\para\oficina_project\installer"

# Compile o script
& "C:\Program Files (x86)\NSIS\makensis.exe" oficina-installer.nsi
```

---

## 📦 Resultado Final

Após compilar com sucesso, você terá:
```
oficina_project/
├── Oficina_Inteligente_Installer.exe  ← 🎉 Seu instalador pronto!
```

Este arquivo `.exe` é portável e pode ser:
- ✅ Enviado por email
- ✅ Copiado em pendrive
- ✅ Publicado em um servidor de downloads
- ✅ Distribuído para qualquer Windows (7 ou superior)

---

## 🔧 Customizações Opcionais

### Modificar Ícone do Instalador

Edite a linha no `oficina-installer.nsi`:

```nsi
OutFile "..\Oficina_Inteligente_Installer.exe"
```

Para adicionar um ícone:
```nsi
!define MUI_ICON "path\to\seu_icon.ico"
!define MUI_UNICON "path\to\seu_icon.ico"
```

### Alterar Diretório de Instalação Padrão

Procure esta linha:
```nsi
InstallDir "$PROGRAMFILES\OficinaInteligente"
```

E mude para algo como:
```nsi
InstallDir "$PROGRAMFILES\Minha Empresa\Oficina"
```

### Adicionar Mais Arquivos

Procure a seção `File` e adicione:
```nsi
SetOutPath "$INSTDIR\minhas_docs"
File /r "..\minhas_docs\*.*"
```

---

## 🎯 Testando o Instalador

1. Execute: `Oficina_Inteligente_Installer.exe`
2. Siga os passos do assistente
3. O programa será instalado em: `C:\Program Files\OficinaInteligente`
4. Procure o atalho na **área de trabalho** ou no **Menu Iniciar**
5. Clique duas vezes para abrir a Oficina Inteligente

---

## ❓ Solução de Problemas

### ❌ "NSIS não encontrado"
✅ Reinstale NSIS desde https://nsis.sourceforge.io/Download

### ❌ "Erro ao compilar .nsi"
✅ Verifique se o caminho dos arquivos está correto no script
✅ Use caminhos relativos: `"..\assets\*.*"` ao invés de caminhos absolutos

### ❌ "O instalador não copia os arquivos"
✅ Certifique-se que os arquivos existem antes de compilar
✅ Ajuste os caminhos `File` no script NSIS

### ❌ "Erro ao desinstalar"
✅ Feche todas as janelas da aplicação primeiro
✅ Execute desinstalar como Administrador

### ❌ "Arquivo .nsi tem erros de sintaxe"
✅ Abra o arquivo em um editor de texto
✅ Procure por linhas com caracteres especiais errados
✅ Certifique-se que não há aspas faltando

---

## 📚 Recursos Adicionais

- **Documentação NSIS**: https://nsis.sourceforge.io/Docs
- **Tutorial Completo**: https://nsis.sourceforge.io/Scripting_Language
- **Exemplos NSIS**: https://nsis.sourceforge.io/Examples

---

## 📝 Resumo Rápido

```bash
# 1. Instale NSIS
# Acesse: https://nsis.sourceforge.io/Download

# 2. Copie o script para a pasta installer/
# Arquivo: oficina-installer.nsi

# 3. Compile (escolha uma opção):
# A) Clique direito > Edit with NSIS > Compile
# B) Arraste .nsi sobre makensis.exe
# C) Execute: compile.bat
# D) Linha de comando: makensis.exe oficina-installer.nsi

# 4. Pronto! 
# Seu instalador está em: Oficina_Inteligente_Installer.exe
```

---

## ✨ Pronto para Distribuir!

Seu instalador profissional está pronto para ser usado. Você pode:
1. Enviar por email
2. Publicar em um site de downloads
3. Colocar em um servidor FTP
4. Distribuir em pendrive

Cada usuário só precisa:
1. Executar `Oficina_Inteligente_Installer.exe`
2. Seguir o assistente
3. Clique duas vezes no atalho da área de trabalho
4. Sistema pronto para usar! 🎉
