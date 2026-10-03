@echo off
REM ============================================================
REM SIAM Agent - All-in-One Installer v1.0.0
REM - Jalan di SEMUA user (SYSTEM + AtStartup)
REM - Auto-recovery kalau crash
REM - Permission aman
REM - Test heartbeat sebelum daftar task
REM ============================================================

setlocal EnableDelayedExpansion
title SIAM Agent Installer v1.0.0

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
echo  Mode:           SYSTEM (jalan di semua user)
echo  Auto-start:     Ya (sebelum login)
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
echo  [1/8] Cek file yang dibutuhkan...
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

REM ==== STOP TASK LAMA (kalau ada) ====
echo.
echo  [2/8] Stop task lama (kalau ada)...
schtasks /Query /TN "%TASK_NAME%" >nul 2>&1
if %errorlevel% equ 0 (
    schtasks /End /TN "%TASK_NAME%" >nul 2>&1
    timeout /t 2 /nobreak >nul

    REM Kill proses agent yang masih jalan
    powershell.exe -NoProfile -Command ^
      "Get-CimInstance Win32_Process -Filter \"Name='powershell.exe'\" | Where-Object { $_.CommandLine -like '*siam-agent*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" >nul 2>&1

    schtasks /Delete /TN "%TASK_NAME%" /F >nul 2>&1
    echo  [OK] Task lama dihapus
) else (
    echo  [-] Tidak ada task lama
)

REM ==== BUAT FOLDER ====
echo.
echo  [3/8] Buat folder...
if not exist "%AGENT_DIR%" mkdir "%AGENT_DIR%"
if not exist "%AGENT_DIR%" (
    echo  [X] Gagal buat folder %AGENT_DIR%
    pause
    exit /b 1
)
echo  [OK] Folder siap

REM ==== COPY FILE ====
echo.
echo  [4/8] Copy file...
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

REM ==== SET PERMISSION (AMAN) ====
echo.
echo  [5/8] Set permission (aman)...
icacls "%AGENT_DIR%" /inheritance:r >nul 2>&1
icacls "%AGENT_DIR%" /grant "SYSTEM:(OI)(CI)F" /T /Q >nul 2>&1
icacls "%AGENT_DIR%" /grant "Administrators:(OI)(CI)F" /T /Q >nul 2>&1
icacls "%AGENT_DIR%" /grant "Users:(OI)(CI)RX" /T /Q >nul 2>&1

REM Config & log: user boleh baca
if exist "%AGENT_DIR%\config.json" (
    icacls "%AGENT_DIR%\config.json" /grant "Users:R" /Q >nul 2>&1
)
if exist "%AGENT_DIR%\agent.log" (
    icacls "%AGENT_DIR%\agent.log" /grant "Users:R" /Q >nul 2>&1
)

echo  [OK] Permission di-set:
echo      SYSTEM       = Full Control
echo      Admins       = Full Control
echo      Users        = Read + Execute
echo      Config ^& Log = Read only

REM ==== TEST HEARTBEAT DULU (sebelum daftar task) ====
echo.
echo  [6/8] Test heartbeat (tunggu 5-15 detik)...
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PS_SCRIPT%" -Once -ShowLog
echo.
echo  Interpretasi hasil di atas:
echo    [OK] "Heartbeat OK"      = sukses, siap jalan
echo    [!]  "Asset BELUM"       = perlu approve di SIAM (agent tetap jalan)
echo    [X]  "Registrasi gagal"  = cek server_url di config.json
echo.

REM ==== DAFTARKAN TASK ====
echo  [7/8] Setup Windows Task...
echo.

REM Buat script PowerShell temporary untuk registrasi task
set PS_REG=%TEMP%\siam_register_task.ps1
(
    echo $ErrorActionPreference = 'Stop'
    echo.
    echo $action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument '-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File "%PS_SCRIPT%"' -WorkingDirectory '%AGENT_DIR%'
    echo.
    echo # Trigger utama: AtStartup (jalan sebelum login)
    echo $trigger = New-ScheduledTaskTrigger -AtStartup
    echo.
    echo # Tambah repetition tiap 15 menit sebagai recovery kalau agent crash
    echo $rep = New-ScheduledTaskTrigger -Once -At ^(Get-Date^) -RepetitionInterval ^(New-TimeSpan -Minutes 15^)
    echo $trigger.Repetition = $rep.Repetition
    echo.
    echo # Principal SYSTEM: jalan tanpa peduli user login
    echo $principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
    echo.
    echo # Settings: auto restart, no time limit, jangan dobel
    echo $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RestartCount 999 -RestartInterval ^(New-TimeSpan -Minutes 1^) -ExecutionTimeLimit ^(New-TimeSpan -Hours 0^) -MultipleInstances IgnoreNew
    echo.
    echo Register-ScheduledTask -TaskName '%TASK_NAME%' -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description 'SIAM Asset Tracking Agent - runs as SYSTEM, all users' -Force ^| Out-Null
    echo.
    echo Write-Host '  [OK] Task registered' -ForegroundColor Green
) > "%PS_REG%"

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PS_REG%"
del "%PS_REG%" >nul 2>&1

if errorlevel 1 (
    echo  [X] Gagal daftarkan task
    pause
    exit /b 1
)
echo  [OK] Task terdaftar:
echo      Trigger: AtStartup + recovery 15 menit
echo      User:    SYSTEM (semua user)
echo      Restart: 999x, interval 1 menit

REM ==== START TASK ====
echo.
echo  [*] Start task sekarang...
timeout /t 2 /nobreak >nul
powershell.exe -NoProfile -Command "Start-ScheduledTask -TaskName '%TASK_NAME%' -ErrorAction SilentlyContinue" >nul 2>&1
timeout /t 3 /nobreak >nul

powershell.exe -NoProfile -Command ^
  "$t = Get-ScheduledTask -TaskName '%TASK_NAME%' -ErrorAction SilentlyContinue; if ($t) { Write-Host '  Status:' $t.State -ForegroundColor Green } else { Write-Host '  WARNING: Task tidak ditemukan' -ForegroundColor Red }"

REM ==== SHORTCUTS ====
set STARTMENU=C:\ProgramData\Microsoft\Windows\Start Menu\Programs\SIAM Agent
if not exist "%STARTMENU%" mkdir "%STARTMENU%"

echo.
echo  [8/8] Buat shortcut Start Menu...

set PS_SHORTCUT=%TEMP%\siam_shortcut.ps1
(
    echo $ErrorActionPreference = 'Stop'
    echo $ws = New-Object -COM WScript.Shell
    echo.
    echo $s = $ws.CreateShortcut^('%STARTMENU%\Lihat Log.lnk'^)
    echo $s.TargetPath = 'notepad.exe'
    echo $s.Arguments = '"%AGENT_DIR%\agent.log"'
    echo $s.Save^(^)
    echo.
    echo $s = $ws.CreateShortcut^('%STARTMENU%\Edit Config.lnk'^)
    echo $s.TargetPath = 'notepad.exe'
    echo $s.Arguments = '"%AGENT_DIR%\config.json"'
    echo $s.Save^(^)
    echo.
    echo # Restart butuh admin - pakai runas supaya muncul UAC
    echo $s = $ws.CreateShortcut^('%STARTMENU%\Restart Agent.lnk'^)
    echo $s.TargetPath = 'powershell.exe'
    echo $s.Arguments = '-NoProfile -Command "Start-Process schtasks -ArgumentList ''/End /TN %TASK_NAME%'' -Verb RunAs -Wait; Start-Sleep 2; Start-Process schtasks -ArgumentList ''/Run /TN %TASK_NAME%'' -Verb RunAs -Wait"'
    echo $s.Save^(^)
    echo.
    echo $s = $ws.CreateShortcut^('%STARTMENU%\Buka Folder.lnk'^)
    echo $s.TargetPath = 'explorer.exe'
    echo $s.Arguments = '"%AGENT_DIR%"'
    echo $s.Save^(^)
) > "%PS_SHORTCUT%"

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%PS_SHORTCUT%"
del "%PS_SHORTCUT%" >nul 2>&1

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
echo    Mode:        SYSTEM (jalan di semua user, sebelum login)
echo    Log:         %AGENT_DIR%\agent.log
echo    Config:      %AGENT_DIR%\config.json
echo.
echo  Agent akan otomatis:
echo    - Jalan sejak Windows startup (sebelum login)
echo    - Jalan di SEMUA user (Administrator, User A, dll)
echo    - Auto-restart kalau crash
echo    - Recovery tiap 15 menit
echo.
echo  Akses cepat: Start Menu ^> SIAM Agent
echo.
echo  Perintah berguna:
echo    Cek log:      type "%AGENT_DIR%\agent.log"
echo    Stop agent:   schtasks /End /TN %TASK_NAME%
echo    Start agent:  schtasks /Run /TN %TASK_NAME%
echo.
echo  Uninstall:    jalankan uninstall.bat
echo.
pause
exit /b 0
