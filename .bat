@echo off
echo.
echo ===== Enviando código para o GitHub =====
cd /d C:\laragon\www\pagamento

git remote set-url origin https://github.com/NexoLinkAdm/plataforma.git
git add .
git commit -m "atualização automática"
git push origin main

echo.
echo ===== Atualizando servidor na HostGator via SSH (porta 2222) =====

REM Caminho completo para plink.exe
set "PLINK_PATH=C:\Users\geova\Downloads\plink.exe"

REM Caminho para a chave privada
set "KEY_PATH=C:\laragon\www\pagamento\.ssh\id_ed25519"

REM Comando remoto
set "REMOTE_CMD=cd public_html/public && git fetch origin main && git reset --hard origin/main"

REM Função para executar SSH com retry se falhar
:SSH_DEPLOY
echo Tentando conectar ao servidor via SSH...
"%PLINK_PATH%" -i "%KEY_PATH%" -P 2222 geova855@108.179.252.230 "%REMOTE_CMD%"

IF %ERRORLEVEL% NEQ 0 (
    echo.
    echo *** Falha ao conectar via SSH. Tentando novamente em 5 segundos...
    timeout /t 5 /nobreak > NUL
    echo Tentativa 2 de conexão...
    "%PLINK_PATH%" -i "%KEY_PATH%" -P 2222 geova855@108.179.252.230 "%REMOTE_CMD%"
    
    IF %ERRORLEVEL% NEQ 0 (
        echo.
        echo *** Segunda tentativa falhou. Verifique sua conexão, chave SSH ou permissões.
        pause
        exit /b 1
    ) else (
        echo.
        echo Segunda tentativa de SSH bem-sucedida.
    )
) else (
    echo.
    echo Conexão SSH bem-sucedida.
)

pause
exit
