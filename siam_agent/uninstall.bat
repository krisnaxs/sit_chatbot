@echo off
REM ============================================================
REM SIAM Agent - Uninstaller
REM ============================================================

setlocal
title SIAM Agent Uninstaller

set AGENT_DIR=C:\ProgramData\SIAM Agent
set TASK_NAME=SIAMAgent
set STARTMENU=C:\ProgramData\Microsoft\Windows\Start Menu\Programs\SIAM Agent

cls
echo.
echo ============================================================
echo         SIAM Agent Uninstaller
echo ============================================================
echo.

net session >nul 2>&1
if %errorlevel% neq 0 (
    echo  [X] Harus dijalankan sebagai Administrator!
    echo.
    echo  Klik kanan uninstall.bat ^> Run as administrator
    echo.
    pause
    exit /b 1
)

echo  Yakin ingin uninstall SIAM Agent?
choice /C YN /M "Lanjutkan (Y/N)"
if errorlevel 2 exit /b 0

echo.
echo  [1/4] Stop task...
schtasks /End /TN "%TASK_NAME%" >nul 2>&1
echo  [OK] Task dihentikan

echo.
echo  [2/4] Hapus task...
schtasks /Delete /TN "%TASK_NAME%" /F >nul 2>&1
echo  [OK] Task dihapus

echo.
echo  [3/4] Hapus shortcut Start Menu...
if exist "%STARTMENU%" (
    rmdir /S /Q "%STARTMENU%" >nul 2>&1
    echo  [OK] Shortcut dihapus
) else (
    echo  [-] Tidak ada shortcut
)

echo.
echo  [4/4] Hapus folder agent...
echo.
echo  Simpan config & log untuk reinstall nanti?
choice /C YN /M "Simpan data (Y/N)"

if errorlevel 1 (
    REM Simpan config & log
    if exist "%AGENT_DIR%\config.json" (
        copy /Y "%AGENT_DIR%\config.json" "%TEMP%\siam-config-backup.json" >nul
    )
    if exist "%AGENT_DIR%\agent.log" (
        copy /Y "%AGENT_DIR%\agent.log" "%TEMP%\siam-log-backup.log" >nul
    )
    rmdir /S /Q "%AGENT_DIR%" >nul 2>&1
    echo  [OK] Folder dihapus (backup di %TEMP%)
) else (
    rmdir /S /Q "%AGENT_DIR%" >nul 2>&1
    echo  [OK] Folder dihapus bersih
)

echo.
echo ============================================================
echo         UNINSTALL SELESAI!
echo ============================================================
echo.
pause
exit /b 0
