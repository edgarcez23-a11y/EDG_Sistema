; Oficina Inteligente - Instalador NSIS
; ============================================

!include "MUI2.nsh"
!include "FileFunc.nsh"
!include "x64.nsh"

; --- Definições Básicas ---
Name "Oficina Inteligente v1.0"
OutFile "..\Oficina_Inteligente_Installer.exe"
InstallDir "$PROGRAMFILES\OficinaInteligente"
InstallDirRegKey HKCU "Software\OficinaInteligente" "InstallLocation"

; --- Configurações do Instalador ---
SetCompressor /SOLID lzma
RequestExecutionLevel user
ShowInstDetails show
ShowUninstDetails show

; --- Interface do Instalador (MUI2) ---
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

!insertmacro MUI_LANGUAGE "Portuguese"
!insertmacro MUI_LANGUAGE "English"

; --- Variáveis Globais ---
Var phpExePath
Var phpFound
Var startMenuFolder

; --- Seção de Instalação ---
Section "Instalação Principal"
  SetOutPath "$INSTDIR"
  
  ; Verifica se é Windows 64-bit
  ${If} ${RunningX64}
    DetailPrint "Sistema: Windows 64-bit detectado"
  ${Else}
    DetailPrint "Sistema: Windows 32-bit detectado"
  ${EndIf}
  
  ; Copia os arquivos principais
  DetailPrint "Copiando arquivos da aplicação..."
  
  ; Copia arquivos da raiz
  File "..\AUTENTICACAO.md"
  File "..\CONFIGURACAO_GMAIL.md"
  File "..\README_EXECUTAVEL.md"
  File "..\composer.json"
  File "..\composer.lock"
  File "..\create_db.sql"
  File "..\iniciar_oficina.bat"
  File "..\localhost.py"
  File "..\oficina.sqlite"
  
  ; Copia pastas
  SetOutPath "$INSTDIR\assets"
  File /r "..\assets\*.*"
  
  SetOutPath "$INSTDIR\inc"
  File /r "..\inc\*.*"
  
  SetOutPath "$INSTDIR\public"
  File /r "..\public\*.*"
  
  SetOutPath "$INSTDIR\vendor"
  File /r "..\vendor\*.*"
  
  SetOutPath "$INSTDIR\.vscode"
  File /r "..\vscode\*.*"
  
  ; Copia o PHP embutido (opcional, apenas se existir)
  IfFileExists "..\php-8.5.9-src\*.*" 0 +2
    SetOutPath "$INSTDIR\php"
    File /r "..\php-8.5.9-src\*.*"
  
  DetailPrint "Arquivos copiados com sucesso"
  
  ; Salva o caminho de instalação no registro
  WriteRegStr HKCU "Software\OficinaInteligente" "InstallLocation" "$INSTDIR"
  WriteRegStr HKCU "Software\OficinaInteligente" "Version" "1.0"
  
  ; Cria arquivo de desinstalação
  WriteUninstaller "$INSTDIR\Uninstall.exe"
  
  ; Cria entrada no registro para desinstalar
  WriteRegStr HKCU "Software\Microsoft\Windows\CurrentVersion\Uninstall\OficinaInteligente" \
                   "DisplayName" "Oficina Inteligente"
  WriteRegStr HKCU "Software\Microsoft\Windows\CurrentVersion\Uninstall\OficinaInteligente" \
                   "DisplayVersion" "1.0"
  WriteRegStr HKCU "Software\Microsoft\Windows\CurrentVersion\Uninstall\OficinaInteligente" \
                   "Publisher" "Sistema de Oficina"
  WriteRegStr HKCU "Software\Microsoft\Windows\CurrentVersion\Uninstall\OficinaInteligente" \
                   "UninstallString" "$INSTDIR\Uninstall.exe"
  WriteRegStr HKCU "Software\Microsoft\Windows\CurrentVersion\Uninstall\OficinaInteligente" \
                   "InstallLocation" "$INSTDIR"
  
SectionEnd

; --- Seção de Atalhos ---
Section "Criar Atalhos"
  CreateDirectory "$SMPROGRAMS\Oficina Inteligente"
  
  ; Atalho no menu iniciar
  CreateShortcut "$SMPROGRAMS\Oficina Inteligente\Oficina Inteligente.lnk" \
                 "$INSTDIR\iniciar_oficina.bat" \
                 "" \
                 "$INSTDIR\iniciar_oficina.bat" \
                 0
  
  CreateShortcut "$SMPROGRAMS\Oficina Inteligente\Desinstalar.lnk" \
                 "$INSTDIR\Uninstall.exe" \
                 "" \
                 "$INSTDIR\Uninstall.exe" \
                 0
  
  ; Atalho na área de trabalho
  CreateShortcut "$DESKTOP\Oficina Inteligente.lnk" \
                 "$INSTDIR\iniciar_oficina.bat" \
                 "" \
                 "$INSTDIR\iniciar_oficina.bat" \
                 0
  
  DetailPrint "Atalhos criados com sucesso"
SectionEnd

; --- Verificação de Dependências ---
Section "Verificar Dependências"
  DetailPrint "Verificando PHP instalado..."
  
  ; Procura o PHP no PATH
  ReadEnvStr $0 "PATH"
  ${StrReplace} $1 $0 ";" "$\n"
  
  ; Tenta encontrar php.exe em localizações comuns
  IfFileExists "$ProgramFiles\PHP\php.exe" 0 +2
    StrCpy $phpFound "1"
  
  IfFileExists "C:\xampp\php\php.exe" 0 +2
    StrCpy $phpFound "1"
  
  IfFileExists "$INSTDIR\php\php.exe" 0 +2
    StrCpy $phpFound "1"
  
  ${If} $phpFound != "1"
    MessageBox MB_OK "PHP não foi detectado. Você precisará instalar PHP ou XAMPP.$\n$\nBaixe em: https://www.php.net/downloads"
  ${Else}
    DetailPrint "PHP encontrado!"
  ${EndIf}
SectionEnd

; --- Seção de Desinstalação ---
Section "Uninstall"
  DetailPrint "Removendo arquivos..."
  
  ; Remove os arquivos e pastas
  RMDir /r "$INSTDIR"
  
  ; Remove atalhos
  RMDir /r "$SMPROGRAMS\Oficina Inteligente"
  Delete "$DESKTOP\Oficina Inteligente.lnk"
  
  ; Remove do registro
  DeleteRegKey HKCU "Software\OficinaInteligente"
  DeleteRegKey HKCU "Software\Microsoft\Windows\CurrentVersion\Uninstall\OficinaInteligente"
  
  DetailPrint "Desinstalação concluída"
SectionEnd

; --- Função de Finalização ---
Function .onInstSuccess
  MessageBox MB_OK "Oficina Inteligente foi instalada com sucesso!$\n$\nProcure pelo atalho na área de trabalho ou no Menu Iniciar."
FunctionEnd

Function un.onUninstSuccess
  MessageBox MB_OK "Oficina Inteligente foi desinstalada com sucesso!"
FunctionEnd
