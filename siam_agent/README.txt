============================================================
SIAM AGENT — PETUNJUK INSTALASI
============================================================

CARA INSTALL (1 langkah):

  1. Klik kanan pada file "install.bat"
  2. Pilih "Run as administrator"
  3. Tunggu 15-30 detik
  4. Selesai!

Agent akan otomatis berjalan setiap kali laptop menyala.

============================================================
LOKASI FILE
============================================================

  C:\ProgramData\SIAM Agent\

    config.json     ← Pengaturan (jangan diubah)
    agent.log       ← Log aktivitas
    siam-agent.ps1  ← Script utama
    README.txt      ← File ini

CATATAN: Folder ProgramData tersembunyi.
Buka: Win+R → ketik "C:\ProgramData\SIAM Agent" → Enter

============================================================
AKSES CEPAT
============================================================

  Start Menu → SIAM Agent →
    📄 Lihat Log         - buka file log
    ⚙️ Edit Config       - buka config.json
    🔄 Restart Agent     - restart agent
    📁 Buka Folder       - buka folder agent

============================================================
CARA CEK AGENT BERJALAN
============================================================

  Start Menu → SIAM Agent → "Lihat Log"

  Kalau muncul baris seperti:
    [2026-10-02 14:30:00] [INFO] ✓ Heartbeat OK - asset: LAP-HR-001

  berarti agent SUKSES.

============================================================
TROUBLESHOOTING
============================================================

  Q: Error "Run as administrator"?
  A: Klik kanan install.bat → Run as administrator

  Q: Agent tidak muncul di dashboard SIAM?
  A: Buka log. Kalau ada "404 asset_not_found" → hubungi admin IT
     untuk mendaftarkan laptop ini di SIAM.

  Q: Laptop ganti WiFi?
  A: Otomatis terdeteksi. Tidak perlu setting apapun.

  Q: Uninstall?
  A: Klik kanan "uninstall.bat" → Run as administrator

============================================================
KONTAK
============================================================
  IT Helpdesk: it@perusahaan.com
