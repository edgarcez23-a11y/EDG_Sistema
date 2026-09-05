@echo off
REM Criar atalho para iniciar_oficina.bat com ícone customizado

setlocal enabledelayedexpansion

REM Obter caminho atual
cd /d "%~dp0"
set "BATCH_FILE=%~dp0iniciar_oficina.bat"
set "ICO_FILE=%~dp0oficina_icon.ico"
set "SHORTCUT_PATH=%~dp0Iniciar Oficina.lnk"

REM Criar atalho usando PowerShell
echo Criando atalho com ícone customizado...
echo.

powershell -NoProfile -Command "^
\$WshShell = New-Object -ComObject WScript.Shell; ^
\$Shortcut = \$WshShell.CreateShortcut('%SHORTCUT_PATH%'); ^
\$Shortcut.TargetPath = '%BATCH_FILE%'; ^
\$Shortcut.WorkingDirectory = '%~dp0'; ^
\$Shortcut.IconLocation = '%ICO_FILE%'; ^
\$Shortcut.WindowStyle = 1; ^
\$Shortcut.Save(); ^
Write-Host '✓ Atalho criado com sucesso!' -ForegroundColor Green; ^
Write-Host 'Arquivo: %SHORTCUT_PATH%' -ForegroundColor Cyan" 

echo.
echo [✓] Pronto! Use o arquivo 'Iniciar Oficina.lnk' para iniciar o sistema
echo.
pause
