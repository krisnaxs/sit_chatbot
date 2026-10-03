@echo off
REM ============================================================
REM SIAM Agent - Uninstaller v1.0.0
REM ============================================================

setlocal EnableDelayedExpansion
title SIAM Agent Uninstaller v1.0.0

set AGENT_DIR=C:\ProgramData\SIAM Agent
set TASK_NAME=SIAMAgent
set STARTMENU=C:\ProgramData\Microsoft\Windows\Start Menu\Programs\SIAM Agent

cls
echo.
echo ============================================================
echo         SIAM Agent Uninstaller v1.0.0
echo ============================================================
echo.

REM ==== CEK ADMIN ====
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo  [X] Harus dijalankan sebagai Administrator!
    echo.
    echo  Klik kanan uninstall.bat ^> Run as administrator
    echo.
    pause
    exit /b 1
)
echo  [OK] Running as Administrator
echo.

echo  Yakin ingin uninstall SIAM Agent?
choice /C YN /M "Lanjutkan (Y/N)"
if errorlevel 2 exit /b 0

REM ==== STOP TASK ====
echo.
echo  [1/5] Stop task...
schtasks /End /TN "%TASK_NAME%" >nul 2>&1
timeout /t 2 /nobreak >nul
echo  [OK] Task dihentikan

REM ==== KILL PROSES ====
echo.
echo  [2/5] Hentikan proses agent...
powershell.exe -NoProfile -Command ^
  "Get-CimInstance Win32_Process -Filter \"Name='powershell.exe'\" | Where-Object { $_.CommandLine -like '*siam-agent*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue; Write-Host '  Stopped PID:' $_.ProcessId }" 2>nul
echo  [OK] Proses dihentikan

REM ==== HAPUS TASK ====
echo.
echo  [3/5] Hapus task...
schtasks /Delete /TN "%TASK_NAME%" /F >nul 2>&1
echo  [OK] Task dihapus

REM ==== HAPUS SHORTCUT ====
echo.
echo  [4/5] Hapus shortcut Start Menu...
if exist "%STARTMENU%" (
    rmdir /S /Q "%STARTMENU%" >nul 2>&1
    echo  [OK] Shortcut dihapus
) else (
    echo  [-] Tidak ada shortcut
)

REM ==== HAPUS FOLDER ====
echo.
echo  [5/5] Hapus folder agent...
echo.
choice /C YN /M "  Simpan config ^& log untuk reinstall nanti"
if errorlevel 2 (
    REM Hapus bersih
    rmdir /S /Q "%AGENT_DIR%" >nul 2>&1
    echo  [OK] Folder dihapus bersih
) else (
    REM Backup config & log dulu
    if exist "%AGENT_DIR%\config.json" (
        copy /Y "%AGENT_DIR%\config.json" "%TEMP%\siam-config-backup.json" >nul 2>&1
        echo  [OK] Config di-backup ke %TEMP%\siam-config-backup.json
    )
    if exist "%AGENT_DIR%\agent.log" (
        copy /Y "%AGENT_DIR%\agent.log" "%TEMP%\siam-log-backup.log" >nul 2>&1
        echo  [OK] Log di-backup ke %TEMP%\siam-log-backup.log
    )
    rmdir /S /Q "%AGENT_DIR%" >nul 2>&1
    echo  [OK] Folder dihapus (backup di %TEMP%)
)

echo.
echo ============================================================
echo         UNINSTALL SELESAI!
echo ============================================================
echo.
pause
exit /b 0
