<#
.SYNOPSIS
    SIAM Agent v1.0.0 - Windows Asset Tracker
.DESCRIPTION
    Kirim heartbeat device ke server SIAM tiap X menit.
    Auto-register via Serial Number BIOS.
    Auto-detect lokasi via Windows Location API.
.NOTES
    Author: Tim IT
    Version: 1.1.0
#>

[CmdletBinding()]
param(
    [string]$ConfigPath = $null,
    [switch]$Once,          # Kirim 1x lalu keluar (untuk testing)
    [switch]$ShowLog        # Tampilkan log ke console
)

$ErrorActionPreference = 'Stop'

# ============================================================
# PATH SETUP
# ============================================================
$script:AgentDir = $PSScriptRoot
if (-not $script:AgentDir) {
    $script:AgentDir = Split-Path -Parent $MyInvocation.MyCommand.Path
}

$script:LogFile = Join-Path $script:AgentDir 'agent.log'
$script:DefaultConfig = Join-Path $script:AgentDir 'config.json'

if (-not $ConfigPath) {
    $ConfigPath = $script:DefaultConfig
}

# Flag global untuk WinRT (load sekali saja)
$script:WinRTLoaded = $false
$script:asTaskGeneric = $null

# ============================================================
# LOGGING
# ============================================================
function Write-Log {
    param(
        [string]$Message,
        [ValidateSet('INFO', 'WARN', 'ERROR')]
        [string]$Level = 'INFO'
    )

    $timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    $line = "[$timestamp] [$Level] $Message"

    try {
        Add-Content -Path $script:LogFile -Value $line -Encoding UTF8
    } catch { }

    if ($VerbosePreference -eq 'Continue' -or $ShowLog) {
        switch ($Level) {
            'INFO'  { Write-Host $line -ForegroundColor Gray }
            'WARN'  { Write-Host $line -ForegroundColor Yellow }
            'ERROR' { Write-Host $line -ForegroundColor Red }
        }
    }
}

# ============================================================
# CONFIG
# ============================================================
function Get-Config {
    if (-not (Test-Path $ConfigPath)) {
        throw "Config file tidak ditemukan: $ConfigPath"
    }

    $cfg = Get-Content $ConfigPath -Raw | ConvertFrom-Json

    if (-not $cfg.server_url) { throw "config.json: 'server_url' wajib diisi" }

    if (-not $cfg.interval) {
        $cfg | Add-Member -NotePropertyName interval -NotePropertyValue 300 -Force
    }

    return $cfg
}

function Save-Config {
    param([object]$Config)
    $Config | ConvertTo-Json -Depth 5 | Set-Content -Path $ConfigPath -Encoding UTF8
}

# ============================================================
# DETEKSI INFO DEVICE
# ============================================================
function Get-Hostname {
    return $env:COMPUTERNAME
}

function Get-SerialNumber {
    try {
        $sn = (Get-CimInstance Win32_BIOS -ErrorAction SilentlyContinue).SerialNumber
        if ($sn) {
            $sn = $sn.Trim()
            $invalid = @(
                'To Be Filled By O.E.M.',
                'Default string',
                'None',
                'System Serial Number',
                'Not Applicable',
                '0',
                ''
            )
            if ($sn -and $invalid -notcontains $sn) {
                return $sn
            }
        }
    } catch {}
    return $null
}

function Get-IPAddress {
    try {
        $ip = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
            Where-Object {
                $_.IPAddress -notlike '127.*' -and
                $_.IPAddress -notlike '169.254.*'
            } |
            Sort-Object -Property InterfaceIndex |
            Select-Object -First 1 -ExpandProperty IPAddress
        return $ip
    } catch {
        return $null
    }
}

function Get-ExternalIP {
    try {
        $ip = (Invoke-RestMethod -Uri "https://api.ipify.org?format=json" -TimeoutSec 5).ip
        return $ip
    } catch {
        return $null
    }
}

function Get-MACAddress {
    try {
        $mac = (Get-NetAdapter -Physical -ErrorAction SilentlyContinue |
            Where-Object { $_.Status -eq 'Up' } |
            Select-Object -First 1).MacAddress
        if ($mac) {
            return ($mac -replace '-', ':').ToUpper()
        }
    } catch {}
    return $null
}

# ============================================================
# FUNGSI GET-WIFIINFO — Support "BSSID" dan "AP BSSID"
# ============================================================
function Get-WiFiInfo {
    $result = @{ SSID = $null; BSSID = $null }
    try {
        $output = netsh wlan show interfaces 2>$null
        if (-not $output) { return $result }

        foreach ($line in $output) {
            $t = $line.Trim()

            # SSID (bukan AP BSSID, bukan BSSID)
            if ($t -match '^SSID\s*:\s*(.+)$') {
                $result.SSID = $matches[1].Trim()
            }
            # AP BSSID atau BSSID (dua-duanya diterima)
            elseif ($t -match '^(AP\s+)?BSSID\s*:\s*(.+)$') {
                $result.BSSID = $matches[2].Trim().ToUpper()
            }
        }
    } catch {}
    return $result
}

# ============================================================
# FUNGSI GET-WINDOWSLOCATION — Windows Location API
# ============================================================
function Get-WindowsLocation {
    try {
        # Load WinRT (cached)
        if (-not $script:WinRTLoaded) {
            Add-Type -AssemblyName System.Runtime.WindowsRuntime -ErrorAction SilentlyContinue

            $script:asTaskGeneric = ([System.WindowsRuntimeSystemExtensions].GetMethods() |
                Where-Object {
                    $_.Name -eq 'AsTask' -and
                    $_.GetParameters().Count -eq 1 -and
                    $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1'
                })[0]

            [Windows.Devices.Geolocation.Geolocator, Windows.Devices.Geolocation, ContentType = WindowsRuntime] | Out-Null
            [Windows.Devices.Geolocation.Geoposition, Windows.Devices.Geolocation, ContentType = WindowsRuntime] | Out-Null
            [Windows.Devices.Geolocation.GeolocationAccessStatus, Windows.Devices.Geolocation, ContentType = WindowsRuntime] | Out-Null

            $script:WinRTLoaded = $true
        }

        # Helper: Await untuk WinRT
        $awaitFunc = {
            param($WinRtTask, $ResultType)
            $asTask = $script:asTaskGeneric.MakeGenericMethod($ResultType)
            $netTask = $asTask.Invoke($null, @($WinRtTask))
            $netTask.Wait(-1) | Out-Null
            $netTask.Result
        }

        # Cek izin
        $accessStatus = & $awaitFunc ([Windows.Devices.Geolocation.Geolocator]::RequestAccessAsync()) ([Windows.Devices.Geolocation.GeolocationAccessStatus])

        if ($accessStatus.ToString() -ne 'Allowed') {
            return $null
        }

        # Ambil posisi
        $geolocator = New-Object Windows.Devices.Geolocation.Geolocator
        $geolocator.DesiredAccuracy = [Windows.Devices.Geolocation.PositionAccuracy]::High

        $position = & $awaitFunc ($geolocator.GetGeopositionAsync()) ([Windows.Devices.Geolocation.Geoposition])

        if (-not $position -or -not $position.Coordinate) {
            return $null
        }

        $coord = $position.Coordinate

        return @{
            lat      = [double]$coord.Point.Position.Latitude
            lng      = [double]$coord.Point.Position.Longitude
            accuracy = [double]$coord.Accuracy
        }
    } catch {
        return $null
    }
}

function Get-LoggedUser {
    try {
        $user = (Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue).UserName
        if ($user) {
            return ($user -split '\\')[-1]
        }
    } catch {}
    return $env:USERNAME
}

function Get-UptimeHours {
    try {
        $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
        $uptime = (Get-Date) - $os.LastBootUpTime
        return [int]$uptime.TotalHours
    } catch {
        return $null
    }
}

function Get-CPUtemperature {
    try {
        $temp = Get-CimInstance -Namespace "root/wmi" -ClassName MSAcpi_ThermalZoneTemperature -ErrorAction SilentlyContinue
        if ($temp) {
            $celsius = [math]::Round(($temp[0].CurrentTemperature / 10) - 273.15, 1)
            if ($celsius -gt 0 -and $celsius -lt 150) {
                return $celsius
            }
        }
    } catch {}
    return $null
}

function Get-OSInfo {
    try {
        $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
        return "$($os.Caption) $($os.Version)"
    } catch {
        return "$($env:OS)"
    }
}

# ============================================================
# BUILD PAYLOAD
# ============================================================
function Build-Payload {
    $wifi = Get-WiFiInfo
    $ip   = Get-IPAddress
    $externalIp = Get-ExternalIP

    # 🆕 Ambil lokasi dari Windows Location API
    $location = Get-WindowsLocation

    $subnet = $null
    if ($ip) {
        $parts = $ip -split '\.'
        if ($parts.Count -eq 4) {
            $subnet = "$($parts[0]).$($parts[1]).$($parts[2]).0/24"
        }
    }

    $payload = [ordered]@{
        hostname        = Get-Hostname
        serial_number   = Get-SerialNumber
        ip              = $ip
        external_ip     = $externalIp
        mac_address     = Get-MACAddress
        wifi_ssid       = $wifi.SSID
        wifi_bssid      = $wifi.BSSID
        subnet          = $subnet
        logged_user     = Get-LoggedUser
        uptime_hours    = Get-UptimeHours
        cpu_temp        = Get-CPUtemperature
        agent_version   = '1.1.0'
        os              = Get-OSInfo

        # 🆕 Koordinat dari Windows Location API
        latitude         = if ($location) { $location.lat } else { $null }
        longitude        = if ($location) { $location.lng } else { $null }
        location_accuracy = if ($location) { $location.accuracy } else { $null }
        location_source  = if ($location) { 'windows_api' } else { $null }
    }

    $clean = [ordered]@{}
    foreach ($k in $payload.Keys) {
        $v = $payload[$k]
        if ($null -ne $v -and $v -ne '') {
            $clean[$k] = $v
        }
    }

    return $clean
}

# ============================================================
# REGISTER
# ============================================================
function Register-Agent {
    param([object]$Config)

    $serial = Get-SerialNumber
    if (-not $serial) {
        Write-Log "Serial BIOS tidak terdeteksi. Skip register." 'ERROR'
        return $null
    }

    Write-Log "Auto-register... SN: $serial" 'INFO'

    $payload = Build-Payload
    $url = "$($Config.server_url.TrimEnd('/'))/api/agent/register"

    try {
        $response = Invoke-RestMethod -Uri $url -Method Post `
            -Body ($payload | ConvertTo-Json -Depth 5 -Compress) `
            -ContentType 'application/json' `
            -TimeoutSec 15 `
            -ErrorAction Stop

        if ($response.ok -and $response.token) {
            Write-Log "OK Registrasi - asset: $($response.asset_code)" 'INFO'

            if ($Config.PSObject.Properties['token']) {
                $Config.token = $response.token
            } else {
                $Config | Add-Member -NotePropertyName token -NotePropertyValue $response.token -Force
            }

            Save-Config -Config $Config
            Write-Log "Token tersimpan di config.json" 'INFO'

            return $response.token
        }
    } catch {
        $statusCode = $null
        if ($_.Exception.Response) {
            $statusCode = [int]$_.Exception.Response.StatusCode
        }

        $body = ""
        if ($_.Exception.Response) {
            try {
                $stream = $_.Exception.Response.GetResponseStream()
                $reader = New-Object System.IO.StreamReader($stream)
                $body = $reader.ReadToEnd()
            } catch {}
        }

        switch ($statusCode) {
            404 {
                Write-Log "Asset BELUM terdaftar di SIAM (404)." 'WARN'
                Write-Log "  Hostname: $(Get-Hostname)" 'WARN'
                Write-Log "  Serial:   $serial" 'WARN'
                Write-Log "  Admin sudah menerima antrian review." 'WARN'
            }
            403 {
                Write-Log "Device di-reject oleh admin (403)." 'ERROR'
                Write-Log "  Reason: $body" 'ERROR'
            }
            default {
                Write-Log "Registrasi gagal: $($_.Exception.Message)" 'ERROR'
                if ($body) {
                    Write-Log "  Response: $body" 'ERROR'
                }
            }
        }
    }

    return $null
}

# ============================================================
# HEARTBEAT
# ============================================================
function Send-Heartbeat {
    param([object]$Config)

    if (-not $Config.token) {
        Write-Log "Token belum ada. Skip heartbeat." 'WARN'
        return $false
    }

    $payload = Build-Payload
    $url = "$($Config.server_url.TrimEnd('/'))/api/agent/heartbeat"

    $headers = @{
        'Authorization' = "Bearer $($Config.token)"
        'Accept'        = 'application/json'
    }

    try {
        $response = Invoke-RestMethod -Uri $url -Method Post `
            -Body ($payload | ConvertTo-Json -Depth 5 -Compress) `
            -ContentType 'application/json' `
            -Headers $headers `
            -TimeoutSec 15 `
            -ErrorAction Stop

        if ($response.ok) {
            $locInfo = ""
            if ($response.lat -and $response.lng) {
                $locInfo = " | Lokasi: $($response.lat),$($response.lng)"
            } elseif ($response.location_id) {
                $locInfo = " | Lokasi ID: $($response.location_id)"
            }

            Write-Log "Heartbeat OK - asset: $($response.asset_code) | IP: $($payload.ip) | WiFi: $(if($payload.wifi_ssid){$payload.wifi_ssid}else{'-'})$locInfo" 'INFO'
            return $true
        }
    } catch {
        $statusCode = $null
        if ($_.Exception.Response) {
            $statusCode = [int]$_.Exception.Response.StatusCode
        }

        switch ($statusCode) {
            401 {
                Write-Log "Token invalid (401). Hapus token & coba register ulang." 'WARN'
                if ($Config.PSObject.Properties['token']) {
                    $Config.PSObject.Properties.Remove('token')
                    Save-Config -Config $Config
                }
            }
            404 {
                Write-Log "Asset tidak ditemukan (404)." 'ERROR'
            }
            default {
                Write-Log "Heartbeat gagal: $($_.Exception.Message)" 'ERROR'
            }
        }
    }

    return $false
}

# ============================================================
# MAIN
# ============================================================
function Start-Agent {
    try {
        $config = Get-Config
    } catch {
        Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }

    Write-Log "=================================================="
    Write-Log "SIAM Agent v1.1.0 (PowerShell)"
    Write-Log "Server:    $($config.server_url)"
    Write-Log "Interval:  $($config.interval) detik"
    Write-Log "Hostname:  $(Get-Hostname)"
    Write-Log "Serial:    $(if(Get-SerialNumber){Get-SerialNumber}else{'(tidak terdeteksi)'})"
    Write-Log "IP:        $(Get-IPAddress)"
    Write-Log "WiFi:      $(if((Get-WiFiInfo).SSID){(Get-WiFiInfo).SSID}else{'(tidak connect)'})"
    Write-Log "BSSID:     $(if((Get-WiFiInfo).BSSID){(Get-WiFiInfo).BSSID}else{'(tidak terdeteksi)'})"

    # 🆕 Log lokasi dari Windows Location API
    $startupLoc = Get-WindowsLocation
    if ($startupLoc) {
        Write-Log "Lokasi:    $($startupLoc.lat), $($startupLoc.lng) (±$([math]::Round($startupLoc.accuracy))m)"
    } else {
        Write-Log "Lokasi:    (tidak tersedia)"
    }

    Write-Log "=================================================="

    if (-not $config.token) {
        $token = Register-Agent -Config $config
        if ($token) {
            $config.token = $token
        }
    }

    if ($config.token) {
        Send-Heartbeat -Config $config | Out-Null
    }

    if ($Once) {
        Write-Log "Mode -Once: selesai." 'INFO'
        return
    }

    $interval = [int]$config.interval
    Write-Log "Loop tiap $interval detik... (Ctrl+C untuk stop)" 'INFO'

    while ($true) {
        Start-Sleep -Seconds $interval

        if (-not $config.token) {
            $token = Register-Agent -Config $config
            if ($token) {
                $config.token = $token
            } else {
                continue
            }
        }

        Send-Heartbeat -Config $config | Out-Null
    }
}

# ============================================================
# ENTRY POINT
# ============================================================
Start-Agent
