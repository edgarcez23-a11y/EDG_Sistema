@echo off
REM Oficina Inteligente - Compilador NSIS Automático
REM ================================================

setlocal enabledelayedexpansion
chcp 65001 >nul 2>&1

echo.
echo ╔════════════════════════════════════════════════════╗
echo ║  OFICINA INTELIGENTE - Gerador de Instalador NSIS  ║
echo ╚════════════════════════════════════════════════════╝
echo.

REM Detecta a pasta do NSIS
set "NSIS_PATH="

REM Tenta localizações comuns do NSIS
if exist "C:\Program Files (x86)\NSIS\makensis.exe" (
    set "NSIS_PATH=C:\Program Files (x86)\NSIS\makensis.exe"
    goto found_nsis
)

if exist "C:\Program Files\NSIS\makensis.exe" (
    set "NSIS_PATH=C:\Program Files\NSIS\makensis.exe"
    goto found_nsis
)

REM Tenta encontrar via PATH
where makensis.exe >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    for /f "delims=" %%i in ('where makensis.exe') do set "NSIS_PATH=%%i"
    goto found_nsis
)

REM NSIS não foi encontrado
echo [✗] ERRO: NSIS não foi encontrado no computador!
echo.
echo Opções para resolver:
echo   1. Instale NSIS: https://nsis.sourceforge.io/Download
echo   2. Se instalou, reinicie o terminal ou computador
echo   3. Verificar: C:\Program Files (x86)\NSIS\makensis.exe
echo.
pause
exit /b 1

:found_nsis
echo [✓] NSIS encontrado em:
echo     !NSIS_PATH!
echo.

REM Verifica se o script .nsi existe
if not exist "oficina-installer.nsi" (
    echo [✗] ERRO: arquivo 'oficina-installer.nsi' não encontrado!
    echo     Coloque o script na mesma pasta deste arquivo.
    echo.
    pause
    exit /b 1
)

echo [*] Compilando o instalador...
echo     Script: oficina-installer.nsi
echo.

REM Compila com detalhes
"!NSIS_PATH!" /V4 oficina-installer.nsi

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ╔════════════════════════════════════════════════════╗
    echo ║                    [✓] SUCESSO!                    ║
    echo ╚════════════════════════════════════════════════════╝
    echo.
    echo Instalador criado com sucesso!
    echo.
    echo Arquivo: ..\Oficina_Inteligente_Installer.exe
    echo Tamanho: (verifique a pasta do projeto)
    echo.
    echo Próximos passos:
    echo   1. Copie o .exe para um pendrive ou servidor
    echo   2. Envie para seus clientes/usuários
    echo   3. Eles executam e seguem o assistente
    echo.
    pause
    exit /b 0
) else (
    echo.
    echo ╔════════════════════════════════════════════════════╗
    echo ║                    [✗] ERRO!                       ║
    echo ╚════════════════════════════════════════════════════╝
    echo.
    echo Erro ao compilar o script NSIS (código: %ERRORLEVEL%)
    echo.
    echo Verificar:
    echo   1. Sintaxe do arquivo oficina-installer.nsi
    echo   2. Caminhos dos arquivos no script
    echo   3. Codificação do arquivo (UTF-8 sem BOM)
    echo.
    echo Documentação: https://nsis.sourceforge.io/Docs
    echo.
    pause
    exit /b 1
)
