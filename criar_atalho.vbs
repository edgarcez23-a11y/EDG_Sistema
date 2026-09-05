Dim objShell, objFSO, strPath, strBatch, strIcon, strShortcut
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Obter diretório do script
strPath = objFSO.GetParentFolderName(WScript.ScriptFullName)
strBatch = strPath & "\iniciar_oficina.bat"
strIcon = strPath & "\oficina_icon3.ico"
strShortcut = strPath & "\Iniciar Oficina.lnk"

' Criar atalho
Set objLink = objShell.CreateShortCut(strShortcut)
objLink.TargetPath = strBatch
objLink.WorkingDirectory = strPath
objLink.IconLocation = strIcon
objLink.WindowStyle = 1
objLink.Save

WScript.Echo "✓ Atalho criado com sucesso!"
WScript.Echo "Arquivo: " & strShortcut
WScript.Echo ""
WScript.Echo "Agora use o arquivo 'Iniciar Oficina.lnk' para iniciar o sistema"
