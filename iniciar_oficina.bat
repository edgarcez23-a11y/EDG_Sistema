@echo off
REM Oficina Inteligente - Inicializador para Windows
REM ================================================
setlocal enabledelayedexpansion
cd /d "%~dp0"
chcp 65001 >nul 2>&1

echo.
echo ╔═══════════════════════════════════════════╗
echo ║   OFICINA INTELIGENTE - Sistema de Gestão ║
echo ║              v1.0.0                        ║
echo ╚═══════════════════════════════════════════╝
echo.

REM Detecta o diretório de instalação
if exist "%~dp0php\php.exe" (
    set "PHP_PATH=%~dp0php\php.exe"
    echo [✓] PHP embutido detectado
    goto start_server
)

REM Procura PHP em localizações comuns
if exist "C:\xampp\php\php.exe" (
    set "PHP_PATH=C:\xampp\php\php.exe"
    echo [✓] XAMPP PHP detectado
    goto start_server
)

if exist "C:\wamp64\bin\php\php.exe" (
    set "PHP_PATH=C:\wamp64\bin\php\php.exe"
    echo [✓] WAMP PHP detectado
    goto start_server
)

REM Procura PHP no PATH do sistema
where php.exe >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    for /f "delims=" %%i in ('where php.exe 2^>nul') do set "PHP_PATH=%%i"
    echo [✓] PHP do sistema detectado em: !PHP_PATH!
    goto start_server
)

echo [✗] ERRO: PHP não foi encontrado!
echo.
echo Opções para resolver:
echo   1. Instale PHP: https://www.php.net/downloads
echo   2. Instale XAMPP: https://www.apachefriends.org/pt_BR/index.html
echo   3. Adicione PHP ao PATH do Windows
echo.
pause
exit /b 1

:start_server
set "PORT=8000"
netstat -ano | findstr /R /C:":8000 " >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    set "PORT=8080"
    echo [!] Porta 8000 em uso. Tentando a porta 8080...
    netstat -ano | findstr /R /C:":8080 " >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo [✗] Nenhuma porta disponível para iniciar o servidor.
        echo [!] Libere a porta 8000 ou 8080 e tente novamente.
        pause
        exit /b 1
    )
)

set "APP_URL=http://127.0.0.1:!PORT!/public/login.php"

echo.
echo [*] Iniciando servidor PHP na porta !PORT!...
echo [*] Acesse: !APP_URL!
echo.
echo O sistema será aberto automaticamente no navegador.
echo.

REM Inicia o servidor em uma janela separada para não travar o processo do batch
start "Oficina Inteligente" cmd /c ""!PHP_PATH!" -S 127.0.0.1:!PORT! -t "%~dp0""

REM Aguarda o servidor responder antes de abrir o navegador
set "SERVER_READY=0"
for /L %%I in (1,1,20) do (
    powershell -NoProfile -Command "$r = Invoke-WebRequest -Uri '!APP_URL!' -UseBasicParsing -TimeoutSec 3 -ErrorAction SilentlyContinue; if ($r) { exit 0 } exit 1" >nul 2>&1
    if not errorlevel 1 (
        set "SERVER_READY=1"
        goto abrir_navegador
    )
    ping -n 1 127.0.0.1 >nul 2>&1
)

echo.
echo [✗] ERRO: O servidor PHP não respondeu dentro do tempo esperado.
echo [!] Verifique se a porta !PORT! está livre e se o PHP está funcionando.
echo.
pause
exit /b 1

:abrir_navegador
start "" "!APP_URL!"
echo [✓] Servidor iniciado com sucesso.
echo.
endlocal
