@echo off
chcp 65001 >nul
REM Recria o banco simpleweb do zero: create 01 e insert 01, nesta ordem. Para no primeiro erro.
REM ATENCAO: apaga TODAS as tabelas e dados do banco simpleweb local.
set "DIR=%~dp0"
set "LOG=%DIR%rodar_scripts.log"
set "MYSQL="
for /d %%d in ("C:\wamp64\bin\mysql\mysql*") do set "MYSQL=%%d\bin\mysql.exe"
if not exist "%MYSQL%" ( echo ERRO: mysql.exe nao encontrado > "%LOG%" & goto fim )
echo Inicio: %date% %time% > "%LOG%"
"%MYSQL%" -u root -e "DROP DATABASE IF EXISTS simpleweb; CREATE DATABASE simpleweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" >> "%LOG%" 2>&1 || goto erro
for %%f in ("create 01 - estrutura do sistema.sql" "insert 01 - base inicial do sistema.sql") do (
  echo Rodando %%~f ...
  echo === %%~f === >> "%LOG%"
  "%MYSQL%" -u root --default-character-set=utf8mb4 simpleweb < "%DIR%%%~f" >> "%LOG%" 2>&1 || goto erro
  echo OK >> "%LOG%"
)
echo Concluido: %date% %time% >> "%LOG%"
echo Banco simpleweb recriado com sucesso. Veja rodar_scripts.log
goto fim
:erro
echo ERRO ao rodar os scripts. Veja rodar_scripts.log
:fim
pause
