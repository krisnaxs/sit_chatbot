@echo off
REM ============================================================
REM SIAM Agent - All-in-One Installer
REM Install ke C:\ProgramData\SIAM Agent\
REM ============================================================

setlocal EnableDelayedExpansion
title SIAM Agent Installer

set AGENT_DIR=C:\ProgramData\SIAM Agent
set TASK_NAME=SIAMAgent
set SCRIPT_DIR=%~dp0
set PS_SCRIPT=%AGENT_DIR%\siam-agent.ps1

cls
echo.
echo ============================================================
echo         SIAM Agent Installer v1.0.0
echo ============================================================
echo.
echo  Install folder: %AGENT_DIR%
echo.

REM ==== CEK ADMIN ====
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo  [X] Script ini HARUS dijalankan sebagai Administrator!
    echo.
    echo  Cara:
    echo    1. Klik kanan install.bat
    echo    2. Pilih "Run as administrator"
    echo.
    pause
    exit /b 1
)
echo  [OK] Running as Administrator

REM ==== CEK FILE ====
echo.
echo  [1/6] Cek file yang dibutuhkan...
if not exist "%SCRIPT_DIR%siam-agent.ps1" (
    echo  [X] siam-agent.ps1 tidak ditemukan
    echo  Path: %SCRIPT_DIR%siam-agent.ps1
    pause
    exit /b 1
)
if not exist "%SCRIPT_DIR%config.json" (
    echo  [X] config.json tidak ditemukan
    echo  Path: %SCRIPT_DIR%config.json
    pause
    exit /b 1
)
echo  [OK] Semua file lengkap

REM ==== BUAT FOLDER ====
echo.
echo  [2/6] Buat folder...
if not exist "%AGENT_DIR%" mkdir "%AGENT_DIR%"
if not exist "%AGENT_DIR%" (
    echo  [X] Gagal buat folder %AGENT_DIR%
    pause
    exit /b 1
)
echo  [OK] Folder siap

REM ==== COPY FILE ====
echo.
echo  [3/6] Copy file...
copy /Y "%SCRIPT_DIR%siam-agent.ps1" "%AGENT_DIR%\" >nul
if errorlevel 1 (
    echo  [X] Gagal copy siam-agent.ps1
    pause
    exit /b 1
)

if not exist "%AGENT_DIR%\config.json" (
    copy /Y "%SCRIPT_DIR%config.json" "%AGENT_DIR%\" >nul
    if errorlevel 1 (
        echo  [X] Gagal copy config.json
        pause
        exit /b 1
    )
    echo  [OK] Config baru dibuat
) else (
    echo  [-] Config lama dipertahankan (tidak ditimpa)
)

if exist "%SCRIPT_DIR%README.txt" (
    copy /Y "%SCRIPT_DIR%README.txt" "%AGENT_DIR%\" >nul
)

echo  [OK] File tercopy ke %AGENT_DIR%

REM ==== SET PERMISSION ====
echo.
echo  [4/6] Set permission...
icacls "%AGENT_DIR%" /grant "Users:(OI)(CI)F" /T /Q >nul 2>&1
echo  [OK] Users bisa baca/tulis

REM ==== DAFTARKAN TASK (Pakai PowerShell, lebih reliable) ====
echo.
echo  [5/6] Setup Windows Task...

REM Hapus task lama kalau ada
schtasks /Query /TN "%TASK_NAME%" >nul 2>&1
if %errorlevel% equ 0 (
    schtasks /End /TN "%TASK_NAME%" >nul 2>&1
    schtasks /Delete /TN "%TASK_NAME%" /F >nul 2>&1
    echo  [-] Task lama dihapus
)

REM Register task via PowerShell
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
  "$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File \"%PS_SCRIPT%\"' -WorkingDirectory '%AGENT_DIR%';" ^
  "$t1 = New-ScheduledTaskTrigger -AtStartup;" ^
  "$t2 = New-ScheduledTaskTrigger -AtLogOn;" ^
  "$p = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest;" ^
  "$s = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1) -ExecutionTimeLimit (New-TimeSpan -Hours 0) -MultipleInstances IgnoreNew;" ^
  "Register-ScheduledTask -TaskName '%TASK_NAME%' -Action $action -Trigger @($t1, $t2) -Principal $p -Settings $s -Description 'SIAM Asset Tracking Agent' -Force | Out-Null"

if errorlevel 1 (
    echo  [X] Gagal daftarkan task
    pause
    exit /b 1
)
echo  [OK] Task '%TASK_NAME%' terdaftar

REM ==== TEST & START ====
echo.
echo  [*] Test heartbeat pertama (tunggu 5-15 detik)...
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PS_SCRIPT%" -Once -ShowLog
echo.

echo  [*] Start task...
schtasks /Run /TN "%TASK_NAME%" >nul 2>&1
echo  [OK] Task berjalan

REM ==== SHORTCUTS ====
set STARTMENU=C:\ProgramData\Microsoft\Windows\Start Menu\Programs\SIAM Agent
if not exist "%STARTMENU%" mkdir "%STARTMENU%"

echo.
echo  [6/6] Buat shortcut Start Menu...

powershell.exe -NoProfile -Command ^
  "$ws = New-Object -COM WScript.Shell;" ^
  "$s = $ws.CreateShortcut('%STARTMENU%\Lihat Log.lnk'); $s.TargetPath = 'notepad.exe'; $s.Arguments = '\"%AGENT_DIR%\agent.log\"'; $s.Save();" ^
  "$s = $ws.CreateShortcut('%STARTMENU%\Edit Config.lnk'); $s.TargetPath = 'notepad.exe'; $s.Arguments = '\"%AGENT_DIR%\config.json\"'; $s.Save();" ^
  "$s = $ws.CreateShortcut('%STARTMENU%\Restart Agent.lnk'); $s.TargetPath = 'cmd.exe'; $s.Arguments = '/c schtasks /End /TN %TASK_NAME% && timeout /t 2 >nul && schtasks /Run /TN %TASK_NAME%'; $s.Save();" ^
  "$s = $ws.CreateShortcut('%STARTMENU%\Buka Folder.lnk'); $s.TargetPath = 'explorer.exe'; $s.Arguments = '\"%AGENT_DIR%\"'; $s.Save()"

echo  [OK] Shortcut dibuat

REM ==== SELESAI ====
echo.
echo ============================================================
echo         INSTALASI SELESAI!
echo ============================================================
echo.
echo  Detail:
echo    Folder:      %AGENT_DIR%
echo    Task:        %TASK_NAME%
echo    Log file:    %AGENT_DIR%\agent.log
echo    Config:      %AGENT_DIR%\config.json
echo.
echo  Akses cepat: Start Menu ^> SIAM Agent
echo.
echo  Perintah berguna:
echo    Cek log:      type "%AGENT_DIR%\agent.log"
echo    Stop agent:   schtasks /End /TN %TASK_NAME%
echo    Start agent:  schtasks /Run /TN %TASK_NAME%
echo.
pause
exit /b 0
