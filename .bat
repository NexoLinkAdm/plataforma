@echo off
echo.
echo ===== Enviando código para o GitHub =====
cd /d C:\laragon\www\pagamento
git add .
git commit -m "atualização automática"
git push

echo.
echo ===== Atualizando servidor na HostGator via SSH (porta 2222) =====

REM Caminho completo para plink.exe se não estiver em PATH
set PLINK_PATH="C:\Users\geova\Downloads\plink.exe"

REM Caminho completo para a chave privada (sem .pub)
set KEY_PATH="C:\laragon\www\pagamento\.ssh\id_ed25519"

%PLINK_PATH% -i %KEY_PATH% -P 2222 geova855@108.179.252.230 "cd public_html && git pull"

pause
