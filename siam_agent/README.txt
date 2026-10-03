============================================================
SIAM AGENT — PETUNJUK INSTALASI (v1.0.0)
============================================================

CARA INSTALL (1 langkah):

  1. Klik kanan pada file "install.bat"
  2. Pilih "Run as administrator"
  3. Tunggu 15-30 detik
  4. Selesai!

Agent akan otomatis berjalan:
  - Setiap kali Windows menyala (sebelum login)
  - Di SEMUA user (Administrator, User A, User B, dll)
  - Auto-restart kalau crash
  - Tidak perlu login dulu

============================================================
LOKASI FILE
============================================================

  C:\ProgramData\SIAM Agent\

    config.json     <- Pengaturan (jangan diubah)
    agent.log       <- Log aktivitas
    siam-agent.ps1  <- Script utama
    README.txt      <- File ini

CATATAN: Folder ProgramData tersembunyi.
Buka: Win+R -> ketik "C:\ProgramData\SIAM Agent" -> Enter

============================================================
AKSES CEPAT
============================================================

  Start Menu -> SIAM Agent ->
    Lihat Log         - buka file log
    Edit Config       - buka config.json
    Restart Agent     - restart agent (butuh admin/UAC)
    Buka Folder       - buka folder agent

============================================================
CARA CEK AGENT BERJALAN
============================================================

  1. Start Menu -> SIAM Agent -> "Lihat Log"

  2. Cari baris seperti:
     [2026-10-03 14:30:00] [INFO] Heartbeat OK - asset: LAP-HR-001

  3. Untuk cek dari Task Scheduler:
     Buka: Win+R -> ketik "taskschd.msc" -> Enter
     Cari task: SIAMAgent
     Status harus: "Running"

============================================================
TROUBLESHOOTING
============================================================

  Q: Error "Run as administrator"?
  A: Klik kanan install.bat -> Run as administrator

  Q: Agent tidak muncul di dashboard SIAM?
  A: Buka log. Kalau ada "Asset BELUM terdaftar (404)"
     -> hubungi admin IT untuk daftarkan laptop di SIAM.

  Q: Agent tidak jalan di user lain?
  A: Cek Task Scheduler (taskschd.msc) -> task "SIAMAgent"
     - Trigger harus: At startup
     - Run as: SYSTEM
     Kalau salah, uninstall lalu install ulang.

  Q: Laptop ganti WiFi?
  A: Otomatis terdeteksi. Tidak perlu setting apapun.

  Q: Uninstall?
  A: Klik kanan "uninstall.bat" -> Run as administrator

  Q: Cek agent dari PC lain?
  A: Buka SIAM -> menu Agent Registry -> cari hostname laptop

============================================================
KONTAK
============================================================
  IT Helpdesk: it@perusahaan.com
