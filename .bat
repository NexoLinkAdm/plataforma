@echo off
echo.
echo ===== Enviando código para o GitHub =====
cd /d C:\laragon\www\pagamento

git add .
git commit -m "atualização automática"
git push origin main

echo.
echo ===== Atualizando servidor na HostGator via SSH (porta 2222) =====

REM Caminho completo para plink.exe
set "PLINK_PATH=C:\Users\geova\Downloads\plink.exe"

REM Caminho para a chave privada
set "KEY_PATH=C:\laragon\www\pagamento\.ssh\id_ed25519"

REM Comando remoto a ser executado no servidor
set "REMOTE_CMD=cd public_html && git pull origin main && echo --- index.php atualizado --- && head -n 5 public/index.php"

REM === 1ª tentativa SSH ===
echo Tentando conexão SSH...
"%PLINK_PATH%" -i "%KEY_PATH%" -P 2222 geova855@108.179.252.230 "%REMOTE_CMD%"

IF %ERRORLEVEL% NEQ 0 (
    echo.
    echo *** Primeira tentativa falhou. Aguardando 5 segundos e tentando novamente...
    timeout /t 5 /nobreak > NUL

    REM === 2ª tentativa SSH ===
    echo Tentando novamente...
    "%PLINK_PATH%" -i "%KEY_PATH%" -P 2222 geova855@108.179.252.230 "%REMOTE_CMD%"

    IF %ERRORLEVEL% NEQ 0 (
        echo.
        echo ❌ Segunda tentativa falhou. Verifique sua conexão SSH ou chave privada.
        pause
        exit /b 1
    ) else (
        echo.
        echo ✅ Segunda tentativa bem-sucedida!
    )
) else (
    echo.
    echo ✅ Conexão SSH e atualização bem-sucedidas!
)

pause
exit
