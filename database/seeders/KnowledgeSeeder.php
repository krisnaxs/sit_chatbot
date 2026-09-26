<?php
// database/seeders/KnowledgeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('knowledge')->truncate();

        $data = array_merge(
            $this->sapaan(),
            $this->aplikasiSIT(),
            $this->aplikasiPLN(),
            $this->sistemOperasi(),
            $this->hardware(),
            $this->software(),
            $this->jaringan(),
            $this->internet(),
            $this->keamanan(),
            $this->database(),
            $this->pemrograman(),
            $this->microsoftOffice(),
            $this->email(),
            $this->printer(),
            $this->penyimpanan(),
            $this->cloud(),
            $this->masalahUmum(),
            $this->faqSIT(),
            $this->plnUmum(),
            $this->troubleshooting(),
            $this->siamAset(),
            $this->siamTransaksi(),
            $this->siamKonsumable(),
        );

        // Insert batched (500 per batch) → aman untuk 5000+ rows
        $chunks = array_chunk($data, 500);

        $id = 1;
        foreach ($chunks as $chunk) {
            $rows = [];
            foreach ($chunk as $item) {
                $rows[] = [
                    'id' => $id++,
                    'kata_kunci' => $item[0],
                    'jawaban' => $item[1],
                ];
            }
            DB::table('knowledge')->insert($rows);
        }

        $this->command->info("✅ " . count($data) . " knowledge berhasil di-seed.");
    }

    // ============================================================
    // 1. SAPAAN & PERCAKAPAN (~60)
    // ============================================================
    private function sapaan(): array
    {
        return [
            ['halo', 'Halo! 👋 Ada yang bisa saya bantu?'],
            ['hai', 'Hai! 👋 Senang bertemu dengan Anda.'],
            ['hello', 'Hello! 👋 Ada yang bisa saya bantu?'],
            ['hi', 'Hi! Ada yang bisa saya bantu?'],
            ['selamat pagi', 'Selamat pagi! 🌅 Ada yang bisa saya bantu?'],
            ['selamat siang', 'Selamat siang! ☀️ Ada yang bisa saya bantu?'],
            ['selamat sore', 'Selamat sore! 🌇 Ada yang bisa saya bantu?'],
            ['selamat malam', 'Selamat malam! 🌙 Ada yang bisa saya bantu?'],
            ['assalamualaikum', 'Waalaikumsalam! 🙏 Ada yang bisa saya bantu?'],
            ['pagi', 'Selamat pagi! 🌅 Ada yang bisa saya bantu?'],
            ['siang', 'Selamat siang! ☀️ Ada yang bisa saya bantu?'],
            ['sore', 'Selamat sore! 🌇 Ada yang bisa saya bantu?'],
            ['malam', 'Selamat malam! 🌙 Ada yang bisa saya bantu?'],
            ['apa kabar', 'Saya baik! 😊 Terima kasih sudah bertanya. Ada yang bisa saya bantu?'],
            ['kamu siapa', 'Saya SIS Assistant, chatbot virtual PLN UBP Suralaya.'],
            ['siapa kamu', 'Saya SIS Assistant, chatbot virtual Buatan SIS UBP SURALAYA.'],
            ['nama kamu', 'Nama saya SIS Assistant.'],
            ['kamu bot', 'Ya, saya adalah chatbot AI yang siap membantu Anda 24/7.'],
            ['kamu manusia', 'Bukan, saya chatbot virtual.'],
            ['bot', 'Ya, saya SIS Assistant — chatbot virtual PLN UBP Suralaya.'],
            ['bisa apa', 'Saya bisa membantu Anda mencari informasi tentang aplikasi, layanan IT, dan hal-hal terkait SIT UBP Suralaya.'],
            ['bisa bantu apa', 'Saya bisa membantu: mencari aplikasi, info login, troubleshooting dasar, dan berbagai pertanyaan IT umum.'],
            ['apa yang bisa kamu lakukan', 'Saya bisa membantu Anda mencari aplikasi, menjawab pertanyaan IT umum, dan memberikan panduan dasar.'],
            ['terima kasih', 'Sama-sama! 😊 Senang bisa membantu.'],
            ['makasih', 'Sama-sama! 😊'],
            ['thanks', 'You\'re welcome! 😊'],
            ['thank you', 'My pleasure! 😊'],
            ['oke', 'Baik! Ada yang lain yang bisa saya bantu?'],
            ['ok', 'Siap! Ada lagi yang bisa saya bantu?'],
            ['sip', 'Siap! 👍'],
            ['baik', 'Baik! Ada yang lain?'],
            ['mantap', 'Terima kasih! 😊'],
            ['bye', 'Sampai jumpa! 👋'],
            ['selamat tinggal', 'Selamat tinggal! 👋 Semoga harimu menyenangkan.'],
            ['sampai jumpa', 'Sampai jumpa lagi! 👋'],
            ['ya', 'Baik, ada yang bisa saya bantu lagi?'],
            ['tidak', 'Baik, kalau ada yang butuh bantuan, tanya saja ya!'],
            ['nggak', 'Oke, kalau butuh bantuan lagi, tinggal tanya!'],
            ['gapapa', 'Baik, semoga harimu lancar! 😊'],
            ['tolong', 'Tentu! Apa yang bisa saya bantu?'],
            ['bantu saya', 'Siap! Apa yang bisa saya bantu?'],
            ['mohon bantuan', 'Tentu, silakan jelaskan apa yang Anda butuhkan.'],
            ['bingung', 'Apa yang membuat Anda bingung? Saya akan bantu jelaskan.'],
            ['error', 'Apa error yang muncul? Coba jelaskan lebih detail.'],
            ['tidak tahu', 'Tidak masalah, saya akan bantu. Apa yang ingin Anda ketahui?'],
            ['test', 'Test berhasil! ✅ Sistem berjalan normal.'],
            ['tes', 'Tes berhasil! ✅ Sistem berjalan normal.'],
            ['ping', 'Pong! 🏓 Sistem aktif dan berjalan normal.'],
            ['hi bot', 'Hai! 👋 Ada yang bisa saya bantu?'],
        ];
    }

    // ============================================================
    // 2. APLIKASI SIT (UBP Suralaya) (~30)
    // ============================================================
    private function aplikasiSIT(): array
    {
        return [
            ['firewall', 'FIREWALL adalah sistem keamanan jaringan yang memantau dan mengontrol lalu lintas data.'],
            ['apa itu firewall', 'Firewall adalah sistem keamanan yang memantau dan mengontrol lalu lintas jaringan berdasarkan aturan keamanan.'],
            ['helpdesk', 'Helpdesk PLN adalah layanan dukungan IT untuk karyawan PLN.'],
            ['apa itu helpdesk', 'Helpdesk adalah layanan dukungan teknis untuk membantu karyawan menyelesaikan masalah IT.'],
            ['maximo', 'Maximo adalah aplikasi Enterprise Asset Management (EAM) yang digunakan PLN untuk mengelola aset.'],
            ['maximo prod', 'Maximo Prod adalah environment produksi aplikasi Maximo untuk operasional harian.'],
            ['maximo qa', 'Maximo QA adalah environment Quality Assurance untuk testing Maximo.'],
            ['erp', 'ERP (Enterprise Resource Planning) adalah sistem terintegrasi untuk mengelola sumber daya perusahaan.'],
            ['erp prod', 'ERP Prod adalah environment produksi ERP PLN.'],
            ['erp prod digunakan untuk apa', 'ERP Prod digunakan untuk mengelola keuangan, SDM, dan operasional perusahaan secara terintegrasi.'],
            ['erpvw', 'ERPVW adalah aplikasi ERP versi view/reporting.'],
            ['pivision', 'PI Vision adalah aplikasi visualisasi data operasional PLTU.'],
            ['pi vision', 'PI Vision adalah aplikasi visualisasi data real-time dari sistem PI System.'],
            ['hxms', 'HXMS adalah aplikasi manajemen SDM PLN.'],
            ['office 365', 'Microsoft Office 365 adalah layanan produktivitas berbasis cloud dari Microsoft.'],
            ['email office 365', 'Email Office 365 adalah layanan email perusahaan berbasis cloud Microsoft.'],
            ['irma', 'IRMA (Integrated Risk Management) adalah aplikasi manajemen risiko terintegrasi PLN.'],
            ['erm', 'ERM (Enterprise Risk Management) adalah sistem manajemen risiko perusahaan.'],
            ['pronia', 'PRONIA adalah aplikasi internal PLN.'],
            ['ams korporat', 'AMS Korporat adalah aplikasi Asset Management System tingkat korporat PLN.'],
            ['iam', 'IAM (Identity and Access Management) adalah sistem untuk mengelola identitas dan akses karyawan.'],
            ['iam digunakan untuk apa', 'IAM digunakan untuk login single sign-on (SSO) ke berbagai aplikasi PLN.'],
            ['cos', 'COS (Compliance Online System) adalah sistem kepatuhan online PLN.'],
            ['prodin', 'PRODIN adalah aplikasi produksi informasi PLN.'],
            ['knowledge center', 'Knowledge Center (KC) adalah portal berbagi pengetahuan PLN.'],
            ['kc', 'KC (Knowledge Center) adalah portal berbagi pengetahuan PLN.'],
            ['ipicofr', 'IPICOFR adalah aplikasi internal PLN.'],
            ['absensi', 'Absensi adalah aplikasi untuk mencatat kehadiran karyawan.'],
            ['ip-ims', 'IP-IMS adalah sistem manajemen informasi PLN Indonesia Power.'],
            ['outage management', 'Outage Management adalah aplikasi untuk mengelola pemadaman/pemeliharaan pembangkit.'],
            ['pro gcg', 'Pro GCG adalah aplikasi Good Corporate Governance PLN.'],
            ['assesment maturity', 'Assesment Maturity adalah aplikasi untuk menilai kematangan sistem/proses.'],
            ['simbakar', 'SIMBAKAR adalah aplikasi internal UBP Suralaya.'],
            ['tableau', 'Tableau adalah aplikasi Business Intelligence untuk visualisasi data.'],
            ['icms', 'ICMS adalah Integrated Contractor Management System.'],
            ['dispatch reoc', 'Dispatch REOC adalah aplikasi untuk dispatch operasional REOC.'],
            ['hdks', 'HDKS adalah aplikasi internal PLN.'],
            ['pln web', 'PLN Web adalah portal resmi PLN di pln.co.id.'],
            ['nergi', 'NERGI adalah aplikasi internal PLN.'],
            ['batubara', 'BATUBARA adalah aplikasi monitoring batubara.'],
            ['portal pln', 'PORTAL adalah portal internal PLN.'],
            ['si-ujo', 'SI-UJO adalah aplikasi internal PLN.'],
            ['snipe', 'SNIPE adalah aplikasi manajemen aset IT.'],
            ['fortinet admin', 'FORTINET ADMIN adalah panel admin untuk perangkat Fortinet.'],
            ['zabbix', 'Zabbix adalah aplikasi monitoring infrastruktur IT.'],
            ['kaspersky', 'Kaspersky adalah software antivirus untuk perlindungan endpoint.'],
            ['noc icon', 'NOC ICON+ adalah portal monitoring jaringan ICON+.'],
            ['riverbed', 'Riverbed adalah perangkat optimasi jaringan WAN.'],
            ['file korporat', 'FILE KORPORAT adalah kumpulan link file penting PLN Indonesia Power.'],
            ['report helpdesk', 'REPORT HELPDESK adalah portal laporan tiket helpdesk.'],
            ['ecp', 'ECP (Email Corporate Portal) adalah portal email perusahaan.'],
            ['wlc', 'WLC (Wireless LAN Controller) adalah pengendali jaringan WiFi.'],
            ['prtg', 'PRTG adalah aplikasi monitoring jaringan.'],
            ['unifi', 'UniFi adalah sistem pengelolaan jaringan WiFi dari Ubiquiti.'],
            ['simulasi interaktif turbin control oil', 'Simulasi Interaktif Turbin Control Oil adalah media pembelajaran interaktif sistem kontrol oli turbin.'],
            ['ppls', 'PPLS adalah aplikasi internal UBP Suralaya.'],
            ['prodsla', 'PRODSLA adalah aplikasi internal UBP Suralaya.'],
            ['liquid', 'LIQUID adalah aplikasi internal PLN Indonesia Power.'],
            ['orafin', 'ORAFIN adalah aplikasi keuangan berbasis Oracle.'],
            ['ftp', 'FTP (File Transfer Protocol) adalah protokol transfer file antar komputer.'],
            ['innovation', 'Innovation adalah portal inovasi PLN Indonesia Power.'],
            ['citrix', 'Citrix adalah solusi virtual desktop untuk akses aplikasi dari mana saja.'],
            ['link ams', 'Link AMS adalah kumpulan link penting terkait Asset Management System.'],
            ['sicomo', 'SICOMO adalah aplikasi internal UBP Suralaya.'],
            ['intranet sla', 'Intranet SLA adalah portal internal UBP Suralaya di SharePoint.'],
            ['loto', 'LOTO (Lockout Tagout) adalah prosedur keselamatan kerja.'],
            ['ea', 'EA (Enterprise Architecture) adalah aplikasi perencanaan arsitektur perusahaan.'],
            ['prolak ip', 'PROLAK IP adalah aplikasi internal PLN Indonesia Power.'],
            ['website ip', 'Website IP adalah situs resmi PLN Indonesia Power.'],
            ['dirjab', 'DIRJAB adalah sistem informasi jabatan PLN.'],
            ['ipku', 'IPKU adalah aplikasi keuangan internal PLN Indonesia Power.'],
            ['digimonx', 'DigimonX adalah aplikasi monitoring aset.'],
            ['diamond', 'Diamond adalah aplikasi manajemen aset.'],
            ['pro inventory', 'Pro Inventory adalah aplikasi manajemen inventaris.'],
            ['ip academy', 'IP Academy adalah portal pembelajaran PLN Indonesia Power.'],
            ['drive ip', 'DRIVE IP adalah layanan penyimpanan file berbasis cloud PLN Indonesia Power.'],
            ['nearmiss', 'NEARMISS adalah aplikasi pelaporan kejadian hampir celaka (safety).'],
            ['appgan', 'APPGAN adalah aplikasi internal PLN Indonesia Power.'],
            ['eppt', 'EPPT adalah aplikasi internal PLN Indonesia Power.'],
        ];
    }

    // ============================================================
    // 3. APLIKASI PLN (Korporat) (~20)
    // ============================================================
    private function aplikasiPLN(): array
    {
        return [
            ['pln mobile', 'PLN Mobile adalah aplikasi layanan pelanggan PLN untuk pembelian token, pembayaran tagihan, dll.'],
            ['apa itu pln mobile', 'PLN Mobile adalah aplikasi resmi PLN untuk layanan pelanggan seperti pembelian token listrik dan pembayaran tagihan.'],
            ['pln co id', 'pln.co.id adalah situs web resmi PT PLN (Persero).'],
            ['webmail pln', 'Webmail PLN adalah layanan email berbasis web untuk karyawan PLN.'],
        ];
    }

    // ============================================================
    // 4. SISTEM OPERASI (~30)
    // ============================================================
    private function sistemOperasi(): array
    {
        return [
            ['windows', 'Windows adalah sistem operasi komputer yang dikembangkan oleh Microsoft.'],
            ['apa itu windows', 'Windows adalah sistem operasi buatan Microsoft untuk komputer dan laptop.'],
            ['windows 10', 'Windows 10 adalah versi Windows yang dirilis pada 2015.'],
            ['windows 11', 'Windows 11 adalah versi terbaru Windows yang dirilis pada 2021.'],
            ['linux', 'Linux adalah sistem operasi open-source berbasis Unix.'],
            ['apa itu linux', 'Linux adalah sistem operasi open-source yang banyak digunakan di server dan komputer.'],
            ['macos', 'macOS adalah sistem operasi komputer Apple Mac.'],
            ['ubuntu', 'Ubuntu adalah distribusi Linux populer berbasis Debian.'],
            ['debian', 'Debian adalah distribusi Linux yang stabil dan open-source.'],
            ['centos', 'CentOS adalah distribusi Linux berbasis RHEL untuk server.'],
            ['red hat', 'Red Hat Enterprise Linux adalah distribusi Linux komersial untuk enterprise.'],
            ['android', 'Android adalah sistem operasi mobile berbasis Linux yang dikembangkan Google.'],
            ['apa itu android', 'Android adalah sistem operasi berbasis Linux untuk smartphone dan tablet.'],
            ['android digunakan untuk apa', 'Android digunakan sebagai sistem operasi untuk menjalankan aplikasi dan mengelola berbagai fungsi pada smartphone.'],
            ['kegunaan android', 'Android digunakan sebagai sistem operasi smartphone untuk menjalankan aplikasi, mengelola perangkat, dan menyediakan berbagai fitur bagi pengguna.'],
            ['android itu apa', 'Android adalah sistem operasi berbasis Linux yang banyak digunakan pada smartphone dan perangkat lainnya.'],
            ['jelaskan android', 'Android adalah sistem operasi yang dikembangkan untuk perangkat seperti smartphone dan tablet.'],
            ['ios', 'iOS adalah sistem operasi mobile Apple untuk iPhone dan iPad.'],
            ['apa itu ios', 'iOS adalah sistem operasi mobile buatan Apple untuk iPhone dan iPad.'],
            ['firmware', 'Firmware adalah software dasar yang tertanam pada hardware untuk mengendalikan fungsinya.'],
            ['kernel', 'Kernel adalah inti dari sistem operasi yang mengelola sumber daya hardware.'],
            ['bios', 'BIOS adalah firmware dasar yang menginisialisasi hardware saat komputer dinyalakan.'],
            ['uefi', 'UEFI adalah pengganti modern BIOS untuk booting komputer.'],
            ['driver', 'Driver adalah software yang memungkinkan OS berkomunikasi dengan hardware.'],
            ['boot', 'Boot adalah proses menyalakan komputer hingga sistem operasi siap digunakan.'],
            ['reboot', 'Reboot adalah proses restart ulang komputer.'],
        ];
    }

    // ============================================================
    // 5. HARDWARE (~40)
    // ============================================================
    private function hardware(): array
    {
        return [
            ['cpu', 'CPU (Central Processing Unit) adalah otak komputer yang memproses instruksi.'],
            ['processor', 'Processor adalah komponen utama komputer yang menjalankan instruksi program.'],
            ['ram', 'RAM (Random Access Memory) adalah memori sementara untuk menjalankan program.'],
            ['apa itu ram', 'RAM adalah memori volatil yang menyimpan data sementara saat komputer menyala.'],
            ['rom', 'ROM (Read Only Memory) adalah memori permanen yang datanya tidak hilang saat mati listrik.'],
            ['motherboard', 'Motherboard adalah papan sirkuit utama yang menghubungkan semua komponen komputer.'],
            ['vga', 'VGA (Video Graphics Array) adalah kartu grafis untuk menampilkan gambar di monitor.'],
            ['gpu', 'GPU (Graphics Processing Unit) adalah prosesor khusus untuk grafis.'],
            ['psu', 'PSU (Power Supply Unit) adalah komponen penyedia daya listrik untuk komputer.'],
            ['hdd', 'HDD (Hard Disk Drive) adalah media penyimpanan magnetik tradisional.'],
            ['ssd', 'SSD (Solid State Drive) adalah media penyimpanan berbasis flash yang lebih cepat dari HDD.'],
            ['apa itu ssd', 'SSD adalah penyimpanan berbasis chip flash yang lebih cepat, senyap, dan tahan guncangan dibanding HDD.'],
            ['monitor', 'Monitor adalah perangkat output visual untuk menampilkan gambar dari komputer.'],
            ['keyboard', 'Keyboard adalah perangkat input untuk mengetik teks dan perintah.'],
            ['mouse', 'Mouse adalah perangkat input untuk menggerakkan kursor dan mengklik.'],
            ['printer', 'Printer adalah perangkat output untuk mencetak dokumen ke kertas.'],
            ['scanner', 'Scanner adalah perangkat input untuk mendigitalkan dokumen fisik.'],
            ['proyektor', 'Proyektor adalah perangkat untuk menampilkan gambar ke layar besar.'],
            ['speaker', 'Speaker adalah perangkat output suara.'],
            ['headset', 'Headset adalah perangkat audio yang terdiri dari headphone dan mikrofon.'],
            ['webcam', 'Webcam adalah kamera yang terhubung ke komputer untuk video call/streaming.'],
            ['ups', 'UPS (Uninterruptible Power Supply) adalah perangkat backup daya listrik.'],
            ['router', 'Router adalah perangkat jaringan yang mengarahkan lalu lintas data antar jaringan.'],
            ['switch', 'Switch adalah perangkat jaringan yang menghubungkan banyak perangkat dalam satu LAN.'],
            ['hub', 'Hub adalah perangkat jaringan sederhana yang meneruskan data ke semua port.'],
            ['modem', 'Modem adalah perangkat yang mengubah sinyal digital ke analog dan sebaliknya.'],
            ['access point', 'Access Point adalah perangkat untuk menyediakan koneksi WiFi.'],
            ['nic', 'NIC (Network Interface Card) adalah kartu jaringan untuk menghubungkan komputer ke jaringan.'],
            ['lan card', 'LAN Card adalah kartu jaringan untuk koneksi kabel ethernet.'],
            ['wifi adapter', 'WiFi adapter adalah perangkat untuk koneksi ke jaringan nirkabel.'],
            ['flashdisk', 'Flashdisk adalah media penyimpanan portabel berbasis flash memory.'],
            ['hardisk eksternal', 'Hardisk eksternal adalah media penyimpanan portabel berkapasitas besar.'],
            ['memory card', 'Memory card adalah kartu penyimpanan kecil untuk kamera, HP, dll.'],
            ['cd', 'CD (Compact Disc) adalah media penyimpanan optik dengan kapasitas ~700 MB.'],
            ['dvd', 'DVD (Digital Versatile Disc) adalah media penyimpanan optik dengan kapasitas ~4.7 GB.'],
            ['bluray', 'Blu-ray adalah media penyimpanan optik dengan kapasitas hingga 100 GB.'],
        ];
    }

    // ============================================================
    // 6. SOFTWARE (~30)
    // ============================================================
    private function software(): array
    {
        return [
            ['software', 'Software adalah program atau aplikasi yang berjalan di komputer.'],
            ['apa itu software', 'Software adalah kumpulan instruksi/program yang menjalankan fungsi tertentu di komputer.'],
            ['hardware', 'Hardware adalah komponen fisik komputer yang bisa disentuh.'],
            ['apa itu hardware', 'Hardware adalah bagian fisik dari komputer seperti CPU, RAM, dan motherboard.'],
            ['operating system', 'Operating System (OS) adalah software dasar yang mengelola hardware dan software aplikasi.'],
            ['sistem operasi', 'Sistem operasi adalah software yang mengelola sumber daya komputer dan menjalankan aplikasi.'],
            ['aplikasi', 'Aplikasi adalah program yang dirancang untuk membantu pengguna melakukan tugas tertentu.'],
            ['install', 'Install adalah proses memasang software/aplikasi ke komputer.'],
            ['uninstall', 'Uninstall adalah proses menghapus software/aplikasi dari komputer.'],
            ['update', 'Update adalah proses memperbarui software ke versi terbaru.'],
            ['upgrade', 'Upgrade adalah proses meningkatkan versi software/hardware.'],
            ['downgrade', 'Downgrade adalah proses menurunkan versi software/hardware.'],
            ['install ulang', 'Install ulang adalah proses memasang kembali software dari awal.'],
            ['patch', 'Patch adalah perbaikan kecil untuk mengatasi bug atau celah keamanan.'],
            ['bug', 'Bug adalah kesalahan atau cacat dalam software yang menyebabkan perilaku tidak diinginkan.'],
            ['error', 'Error adalah pesan kesalahan yang muncul saat software/hardware bermasalah.'],
            ['crash', 'Crash adalah kondisi saat aplikasi/sistem berhenti bekerja secara tiba-tiba.'],
            ['hang', 'Hang adalah kondisi saat komputer/aplikasi berhenti merespons.'],
            ['freeze', 'Freeze adalah kondisi saat layar atau aplikasi membeku.'],
            ['bootable', 'Bootable adalah media (flashdisk/DVD) yang bisa digunakan untuk booting komputer.'],
            ['iso', 'ISO adalah file image disk yang bisa dibakar ke CD/DVD atau USB.'],
            ['exe', 'EXE (Executable) adalah file program yang bisa dijalankan di Windows.'],
            ['dll', 'DLL (Dynamic Link Library) adalah file library yang digunakan aplikasi Windows.'],
            ['open source', 'Open source adalah software yang source code-nya terbuka untuk publik.'],
            ['freeware', 'Freeware adalah software gratis yang bisa digunakan tanpa biaya.'],
            ['shareware', 'Shareware adalah software gratis untuk dicoba, berbayar untuk lanjut.'],
            ['trial', 'Trial adalah versi software yang bisa dicoba dalam periode terbatas.'],
            ['lisensi', 'Lisensi adalah hak legal untuk menggunakan software.'],
            ['serial number', 'Serial number adalah kode unik untuk aktivasi software.'],
            ['product key', 'Product key adalah kode aktivasi software berlisensi.'],
            ['aktivasi', 'Aktivasi adalah proses mengaktifkan software dengan kode lisensi.'],
        ];
    }

    // ============================================================
    // 7. JARINGAN (~40)
    // ============================================================
    private function jaringan(): array
    {
        return [
            ['jaringan', 'Jaringan komputer adalah kumpulan komputer yang terhubung untuk berbagi data dan sumber daya.'],
            ['apa itu jaringan', 'Jaringan komputer adalah sistem yang menghubungkan dua atau lebih komputer untuk berkomunikasi dan berbagi sumber daya.'],
            ['lan', 'LAN (Local Area Network) adalah jaringan lokal dengan cakupan kecil seperti kantor.'],
            ['wan', 'WAN (Wide Area Network) adalah jaringan luas yang mencakup wilayah geografis besar.'],
            ['man', 'MAN (Metropolitan Area Network) adalah jaringan dengan cakupan satu kota.'],
            ['vpn', 'VPN (Virtual Private Network) adalah jaringan pribadi virtual untuk koneksi aman.'],
            ['apa itu vpn', 'VPN adalah teknologi yang membuat koneksi internet Anda terenkripsi dan aman.'],
            ['ip', 'IP (Internet Protocol) adalah alamat unik untuk setiap perangkat di jaringan.'],
            ['ip address', 'IP Address adalah alamat numerik yang mengidentifikasi perangkat di jaringan.'],
            ['ipv4', 'IPv4 adalah versi IP dengan format 32-bit (contoh: 192.168.1.1).'],
            ['ipv6', 'IPv6 adalah versi IP dengan format 128-bit, pengganti IPv4.'],
            ['subnet mask', 'Subnet mask adalah angka yang menentukan pembagian jaringan dan host.'],
            ['gateway', 'Gateway adalah pintu keluar jaringan ke jaringan lain (biasanya internet).'],
            ['dns', 'DNS (Domain Name System) adalah sistem yang menerjemahkan nama domain ke IP address.'],
            ['apa itu dns', 'DNS adalah sistem yang mengubah nama domain (seperti google.com) menjadi alamat IP.'],
            ['dhcp', 'DHCP (Dynamic Host Configuration Protocol) adalah protokol pemberian IP otomatis.'],
            ['tcp', 'TCP (Transmission Control Protocol) adalah protokol koneksi yang andal.'],
            ['udp', 'UDP (User Datagram Protocol) adalah protokol koneksi cepat tanpa jaminan.'],
            ['http', 'HTTP (HyperText Transfer Protocol) adalah protokol untuk mengakses halaman web.'],
            ['https', 'HTTPS adalah versi aman dari HTTP dengan enkripsi SSL/TLS.'],
            ['ftp', 'FTP (File Transfer Protocol) adalah protokol untuk transfer file.'],
            ['ssh', 'SSH (Secure Shell) adalah protokol akses remote yang aman.'],
            ['telnet', 'Telnet adalah protokol akses remote tanpa enkripsi (tidak aman).'],
            ['ping', 'Ping adalah perintah untuk menguji koneksi ke host lain.'],
            ['traceroute', 'Traceroute adalah perintah untuk melihat jalur paket ke host tujuan.'],
            ['bandwidth', 'Bandwidth adalah kapasitas maksimum transfer data per satuan waktu.'],
            ['latency', 'Latency adalah waktu tunda pengiriman data.'],
            ['packet', 'Packet adalah unit data yang dikirim melalui jaringan.'],
            ['firewall', 'Firewall adalah sistem yang memantau dan mengontrol lalu lintas jaringan.'],
            ['proxy', 'Proxy adalah server perantara antara client dan server.'],
            ['nat', 'NAT (Network Address Translation) adalah teknik menerjemahkan IP privat ke publik.'],
            ['wifi', 'WiFi adalah teknologi jaringan nirkabel berbasis standar IEEE 802.11.'],
            ['ethernet', 'Ethernet adalah standar jaringan kabel yang paling umum.'],
            ['kabel utp', 'Kabel UTP (Unshielded Twisted Pair) adalah kabel jaringan yang paling umum.'],
            ['kabel stp', 'Kabel STP (Shielded Twisted Pair) adalah kabel jaringan dengan pelindung.'],
            ['fiber optik', 'Fiber optik adalah kabel jaringan yang menggunakan cahaya sebagai media transmisi.'],
        ];
    }

    // ============================================================
    // 8. INTERNET (~30)
    // ============================================================
    private function internet(): array
    {
        return [
            ['internet', 'Internet adalah jaringan global yang menghubungkan miliaran perangkat di seluruh dunia.'],
            ['apa itu internet', 'Internet adalah jaringan global yang menghubungkan berbagai perangkat di seluruh dunia sehingga pengguna dapat bertukar informasi dan mengakses berbagai layanan.'],
            ['internet itu apa', 'Internet adalah jaringan global yang menghubungkan berbagai perangkat di seluruh dunia sehingga pengguna dapat bertukar informasi dan mengakses berbagai layanan.'],
            ['internet digunakan untuk apa', 'Internet digunakan untuk berkomunikasi, mencari informasi, mengakses layanan online, berbagi data, dan melakukan berbagai aktivitas digital.'],
            ['kegunaan internet', 'Internet berguna untuk mencari informasi, berkomunikasi, belajar, bekerja, berbagi data, dan mengakses berbagai layanan online.'],
            ['fungsi internet', 'Internet berfungsi untuk menghubungkan berbagai perangkat dan memungkinkan pengguna bertukar informasi, berkomunikasi, serta mengakses berbagai layanan.'],
            ['www', 'WWW (World Wide Web) adalah sistem informasi yang dapat diakses melalui internet.'],
            ['browser', 'Browser adalah aplikasi untuk mengakses halaman web di internet.'],
            ['chrome', 'Google Chrome adalah browser web buatan Google.'],
            ['firefox', 'Mozilla Firefox adalah browser web open-source.'],
            ['edge', 'Microsoft Edge adalah browser web buatan Microsoft.'],
            ['safari', 'Safari adalah browser web buatan Apple.'],
            ['opera', 'Opera adalah browser web dengan fitur VPN bawaan.'],
            ['url', 'URL (Uniform Resource Locator) adalah alamat halaman web di internet.'],
            ['domain', 'Domain adalah nama unik untuk mengidentifikasi situs web.'],
            ['hosting', 'Hosting adalah layanan penyimpanan website di server yang terhubung internet.'],
            ['server', 'Server adalah komputer yang menyediakan layanan untuk client di jaringan.'],
            ['client', 'Client adalah komputer/perangkat yang mengakses layanan dari server.'],
            ['download', 'Download adalah proses mengunduh file dari internet ke perangkat.'],
            ['upload', 'Upload adalah proses mengunggah file dari perangkat ke internet.'],
            ['streaming', 'Streaming adalah proses memutar konten multimedia secara langsung tanpa mengunduh.'],
            ['cloud', 'Cloud adalah layanan komputasi berbasis internet yang menyediakan penyimpanan dan aplikasi.'],
            ['cookie', 'Cookie adalah file kecil yang disimpan browser untuk mengingat preferensi situs.'],
            ['cache', 'Cache adalah penyimpanan sementara data untuk mempercepat akses.'],
            ['vpn', 'VPN (Virtual Private Network) adalah jaringan pribadi virtual untuk koneksi aman.'],
            ['tor', 'TOR adalah jaringan anonim untuk menjelajah internet secara privat.'],
        ];
    }

    // ============================================================
    // 9. KEAMANAN (~30)
    // ============================================================
    private function keamanan(): array
    {
        return [
            ['antivirus', 'Antivirus membantu mendeteksi dan melindungi perangkat dari aplikasi atau software berbahaya.'],
            ['apa itu antivirus', 'Antivirus adalah software yang mendeteksi, mencegah, dan menghapus virus/malware.'],
            ['fungsi antivirus', 'Fungsi antivirus adalah membantu mendeteksi, mencegah, dan menghapus software berbahaya.'],
            ['malware', 'Malware adalah software berbahaya yang dirancang untuk merusak atau mencuri data.'],
            ['virus', 'Virus komputer adalah program jahat yang dapat menyebar dan merusak sistem.'],
            ['worm', 'Worm adalah malware yang menyebar sendiri melalui jaringan.'],
            ['trojan', 'Trojan adalah malware yang menyamar sebagai software legit.'],
            ['ransomware', 'Ransomware adalah malware yang mengenkripsi data dan meminta tebusan.'],
            ['spyware', 'Spyware adalah malware yang memata-matai aktivitas pengguna.'],
            ['adware', 'Adware adalah software yang menampilkan iklan secara paksa.'],
            ['phishing', 'Phishing adalah upaya menipu pengguna untuk menyerahkan data pribadi.'],
            ['apa itu phishing', 'Phishing adalah serangan sosial yang memancing korban untuk memberikan data sensitif melalui situs/email palsu.'],
            ['password', 'Password adalah kunci rahasia untuk mengakses akun atau sistem.'],
            ['password kuat', 'Password kuat minimal 8 karakter, kombinasi huruf besar-kecil, angka, dan simbol.'],
            ['buat password kuat', 'Gunakan kombinasi huruf besar-kecil, angka, simbol, minimal 12 karakter, dan jangan pakai kata umum.'],
            ['two factor authentication', 'Two Factor Authentication (2FA) adalah verifikasi dua langkah untuk keamanan ekstra.'],
            ['2fa', '2FA (Two Factor Authentication) adalah verifikasi dua langkah untuk keamanan ekstra.'],
            ['enkripsi', 'Enkripsi adalah proses mengubah data menjadi kode yang tidak bisa dibaca tanpa kunci.'],
            ['dekripsi', 'Dekripsi adalah proses mengembalikan data terenkripsi ke bentuk asli.'],
            ['ssl', 'SSL (Secure Sockets Layer) adalah protokol enkripsi untuk koneksi aman.'],
            ['tls', 'TLS (Transport Layer Security) adalah pengganti modern SSL.'],
            ['sertifikat digital', 'Sertifikat digital adalah dokumen elektronik untuk memverifikasi identitas.'],
            ['firewall', 'Firewall adalah sistem keamanan yang memantau dan mengontrol lalu lintas jaringan.'],
            ['brute force', 'Brute force adalah serangan mencoba semua kombinasi password.'],
            ['ddos', 'DDoS (Distributed Denial of Service) adalah serangan yang membanjiri server hingga lumpuh.'],
            ['sql injection', 'SQL Injection adalah serangan menyisipkan kode SQL berbahaya ke input aplikasi.'],
            ['xss', 'XSS (Cross Site Scripting) adalah serangan menyisipkan script jahat ke halaman web.'],
            ['csfr', 'CSRF (Cross Site Request Forgery) adalah serangan memaksa pengguna menjalankan aksi tidak diinginkan.'],
            ['backup', 'Backup adalah proses menyalin data untuk mencegah kehilangan.'],
            ['restore', 'Restore adalah proses mengembalikan data dari backup.'],
        ];
    }

    // ============================================================
    // 10. DATABASE (~25)
    // ============================================================
    private function database(): array
    {
        return [
            ['database', 'Database adalah kumpulan data terstruktur yang disimpan secara elektronik.'],
            ['apa itu database', 'Database adalah sistem penyimpanan data terorganisir yang memudahkan pengambilan dan pengelolaan.'],
            ['dbms', 'DBMS (Database Management System) adalah software untuk mengelola database.'],
            ['mysql', 'MySQL adalah sistem manajemen database open-source yang populer.'],
            ['apa itu mysql', 'MySQL adalah DBMS relasional open-source yang banyak digunakan untuk aplikasi web.'],
            ['postgresql', 'PostgreSQL adalah DBMS relasional open-source yang canggih.'],
            ['oracle', 'Oracle Database adalah DBMS komersial dari Oracle Corporation.'],
            ['sql server', 'Microsoft SQL Server adalah DBMS dari Microsoft.'],
            ['mariadb', 'MariaDB adalah fork dari MySQL yang open-source.'],
            ['sqlite', 'SQLite adalah database ringan berbasis file, cocok untuk aplikasi kecil.'],
            ['mongodb', 'MongoDB adalah database NoSQL berbasis dokumen.'],
            ['redis', 'Redis adalah database in-memory untuk caching dan real-time apps.'],
            ['sql', 'SQL (Structured Query Language) adalah bahasa untuk mengelola database relasional.'],
            ['query', 'Query adalah perintah untuk mengambil atau mengubah data di database.'],
            ['select', 'SELECT adalah perintah SQL untuk mengambil data.'],
            ['insert', 'INSERT adalah perintah SQL untuk menambah data.'],
            ['update', 'UPDATE adalah perintah SQL untuk mengubah data.'],
            ['delete', 'DELETE adalah perintah SQL untuk menghapus data.'],
            ['join', 'JOIN adalah operasi SQL untuk menggabungkan dua tabel atau lebih.'],
            ['primary key', 'Primary key adalah kolom unik pengidentifikasi setiap baris di tabel.'],
            ['foreign key', 'Foreign key adalah kolom yang merujuk ke primary key tabel lain.'],
            ['index', 'Index adalah struktur data untuk mempercepat pencarian di database.'],
            ['normalisasi', 'Normalisasi adalah proses mengorganisir data untuk mengurangi redundansi.'],
            ['relasi', 'Relasi adalah hubungan antar tabel dalam database relasional.'],
            ['backup database', 'Backup database adalah proses menyalin database untuk mencegah kehilangan data.'],
        ];
    }

    // ============================================================
    // 11. PEMROGRAMAN (~30)
    // ============================================================
    private function pemrograman(): array
    {
        return [
            ['php', 'PHP adalah bahasa pemrograman yang banyak digunakan untuk membuat aplikasi web di sisi server.'],
            ['apa itu php', 'PHP (Hypertext Preprocessor) adalah bahasa scripting server-side untuk pengembangan web.'],
            ['javascript', 'JavaScript adalah bahasa pemrograman yang digunakan untuk membuat halaman web menjadi interaktif.'],
            ['apa itu javascript', 'JavaScript adalah bahasa pemrograman untuk web yang berjalan di browser dan server.'],
            ['python', 'Python adalah bahasa pemrograman serbaguna yang mudah dipelajari.'],
            ['java', 'Java adalah bahasa pemrograman berorientasi objek yang populer.'],
            ['c++', 'C++ adalah bahasa pemrograman tingkat tinggi untuk aplikasi performa tinggi.'],
            ['c#', 'C# (C Sharp) adalah bahasa pemrograman dari Microsoft untuk .NET.'],
            ['golang', 'Go (Golang) adalah bahasa pemrograman dari Google untuk aplikasi modern.'],
            ['rust', 'Rust adalah bahasa pemrograman sistem yang aman dan cepat.'],
            ['kotlin', 'Kotlin adalah bahasa pemrograman modern untuk Android.'],
            ['swift', 'Swift adalah bahasa pemrograman untuk iOS dan macOS.'],
            ['html', 'HTML (HyperText Markup Language) adalah bahasa markup untuk struktur halaman web.'],
            ['css', 'CSS (Cascading Style Sheets) adalah bahasa untuk mengatur tampilan halaman web.'],
            ['framework', 'Framework adalah kerangka kerja yang menyediakan struktur untuk pengembangan software.'],
            ['laravel', 'Laravel adalah framework PHP modern untuk pengembangan web.'],
            ['apa itu laravel', 'Laravel adalah framework PHP open-source dengan sintaks elegan untuk pengembangan web.'],
            ['react', 'React adalah library JavaScript untuk membangun UI.'],
            ['vue', 'Vue.js adalah framework JavaScript progresif untuk UI.'],
            ['angular', 'Angular adalah framework JavaScript dari Google untuk aplikasi web.'],
            ['nodejs', 'Node.js adalah runtime JavaScript di sisi server.'],
            ['api', 'API (Application Programming Interface) adalah antarmuka untuk komunikasi antar software.'],
            ['apa itu api', 'API adalah seperangkat aturan yang memungkinkan dua aplikasi berkomunikasi.'],
            ['rest api', 'REST API adalah API yang mengikuti gaya arsitektur REST.'],
            ['json', 'JSON (JavaScript Object Notation) adalah format data ringan untuk pertukaran data.'],
            ['xml', 'XML (eXtensible Markup Language) adalah bahasa markup untuk berbagi data.'],
            ['git', 'Git adalah sistem kontrol versi terdistribusi.'],
            ['github', 'GitHub adalah platform hosting kode berbasis Git.'],
            ['repository', 'Repository adalah tempat penyimpanan kode proyek di Git.'],
            ['commit', 'Commit adalah tindakan menyimpan perubahan di Git.'],
        ];
    }

    // ============================================================
    // 12. MICROSOFT OFFICE (~30)
    // ============================================================
    private function microsoftOffice(): array
    {
        return [
            ['microsoft office', 'Microsoft Office adalah paket aplikasi produktivitas dari Microsoft.'],
            ['office', 'Microsoft Office adalah paket aplikasi perkantoran seperti Word, Excel, PowerPoint.'],
            ['word', 'Microsoft Word adalah aplikasi pengolah kata dari Microsoft.'],
            ['microsoft word', 'Microsoft Word adalah aplikasi pengolah kata untuk membuat dokumen.'],
            ['excel', 'Microsoft Excel adalah aplikasi spreadsheet untuk mengelola data dan perhitungan.'],
            ['microsoft excel', 'Microsoft Excel adalah aplikasi spreadsheet dari Microsoft.'],
            ['powerpoint', 'Microsoft PowerPoint adalah aplikasi presentasi dari Microsoft.'],
            ['microsoft powerpoint', 'Microsoft PowerPoint adalah aplikasi untuk membuat presentasi.'],
            ['outlook', 'Microsoft Outlook adalah aplikasi email dan kalender dari Microsoft.'],
            ['teams', 'Microsoft Teams adalah aplikasi kolaborasi tim dan meeting online.'],
            ['onenote', 'Microsoft OneNote adalah aplikasi catatan digital.'],
            ['sharepoint', 'SharePoint adalah platform kolaborasi dan manajemen dokumen dari Microsoft.'],
            ['onedrive', 'OneDrive adalah layanan penyimpanan cloud dari Microsoft.'],
            ['rumus excel', 'Rumus Excel adalah formula untuk melakukan perhitungan di Excel (contoh: SUM, AVERAGE, IF).'],
            ['sum', 'SUM adalah rumus Excel untuk menjumlahkan angka.'],
            ['average', 'AVERAGE adalah rumus Excel untuk menghitung rata-rata.'],
            ['vlookup', 'VLOOKUP adalah rumus Excel untuk mencari data di tabel secara vertikal.'],
            ['hlookup', 'HLOOKUP adalah rumus Excel untuk mencari data di tabel secara horizontal.'],
            ['if', 'IF adalah rumus Excel untuk percabangan logika.'],
            ['pivot table', 'Pivot Table adalah fitur Excel untuk merangkum dan menganalisis data.'],
            ['chart excel', 'Chart di Excel adalah grafik untuk visualisasi data.'],
            ['macro', 'Macro adalah script otomatis di Office untuk tugas berulang.'],
        ];
    }

    // ============================================================
    // 13. EMAIL (~20)
    // ============================================================
    private function email(): array
    {
        return [
            ['email', 'Email adalah surat elektronik untuk berkirim pesan melalui internet.'],
            ['apa itu email', 'Email adalah singkatan dari electronic mail — pesan digital yang dikirim melalui jaringan internet.'],
            ['smtp', 'SMTP (Simple Mail Transfer Protocol) adalah protokol untuk mengirim email.'],
            ['imap', 'IMAP (Internet Message Access Protocol) adalah protokol untuk mengakses email dari server.'],
            ['pop3', 'POP3 (Post Office Protocol 3) adalah protokol untuk mengunduh email ke perangkat.'],
            ['spam', 'Spam adalah email sampah yang dikirim massal tanpa diminta.'],
            ['inbox', 'Inbox adalah kotak masuk untuk email yang diterima.'],
            ['outbox', 'Outbox adalah kotak keluar untuk email yang akan dikirim.'],
            ['cc', 'CC (Carbon Copy) adalah fitur untuk mengirim salinan email ke penerima lain.'],
            ['bcc', 'BCC (Blind Carbon Copy) adalah CC tersembunyi yang tidak terlihat penerima lain.'],
            ['attachment', 'Attachment adalah file yang dilampirkan pada email.'],
            ['gmail', 'Gmail adalah layanan email dari Google.'],
            ['outlook', 'Microsoft Outlook adalah aplikasi email dan kalender dari Microsoft.'],
        ];
    }

    // ============================================================
    // 14. PRINTER (~15)
    // ============================================================
    private function printer(): array
    {
        return [
            ['printer', 'Printer adalah perangkat output untuk mencetak dokumen ke kertas.'],
            ['printer inkjet', 'Printer inkjet adalah printer yang menggunakan tinta cair.'],
            ['printer laser', 'Printer laser adalah printer yang menggunakan toner dan laser.'],
            ['printer thermal', 'Printer thermal adalah printer yang menggunakan panas untuk mencetak.'],
            ['toner', 'Toner adalah bubuk tinta untuk printer laser.'],
            ['tinta printer', 'Tinta printer adalah cairan berwarna untuk printer inkjet.'],
            ['cartridge', 'Cartridge adalah tabung tinta/toner printer.'],
            ['printer tidak bisa print', 'Coba cek: kabel USB, driver terinstall, tinta/toner tersedia, printer online, dan queue kosong.'],
            ['printer offline', 'Kalau printer offline, cek koneksi (USB/WiFi), restart printer, dan set as default printer.'],
            ['share printer', 'Share printer bisa via Control Panel > Devices and Printers > Share this printer.'],
            ['driver printer', 'Driver printer adalah software yang memungkinkan komputer berkomunikasi dengan printer.'],
            ['scan', 'Scan adalah proses mendigitalkan dokumen fisik menggunakan scanner.'],
            ['fotokopi', 'Fotokopi adalah proses menggandakan dokumen dengan mesin fotokopi.'],
        ];
    }

    // ============================================================
    // 15. PENYIMPANAN (~15)
    // ============================================================
    private function penyimpanan(): array
    {
        return [
            ['storage', 'Storage adalah media penyimpanan data digital.'],
            ['penyimpanan', 'Penyimpanan adalah tempat menyimpan data digital.'],
            ['kapasitas penyimpanan', 'Kapasitas penyimpanan diukur dalam byte (KB, MB, GB, TB).'],
            ['gb', 'GB (Gigabyte) adalah satuan kapasitas penyimpanan, 1 GB = 1024 MB.'],
            ['mb', 'MB (Megabyte) adalah satuan kapasitas penyimpanan, 1 MB = 1024 KB.'],
            ['tb', 'TB (Terabyte) adalah satuan kapasitas penyimpanan, 1 TB = 1024 GB.'],
            ['nas', 'NAS (Network Attached Storage) adalah penyimpanan yang terhubung ke jaringan.'],
            ['san', 'SAN (Storage Area Network) adalah jaringan penyimpanan khusus enterprise.'],
            ['raid', 'RAID (Redundant Array of Independent Disks) adalah teknik menggabungkan beberapa disk untuk performa/redundansi.'],
            ['raid 0', 'RAID 0 adalah kombinasi disk untuk performa (tanpa redundansi).'],
            ['raid 1', 'RAID 1 adalah mirroring disk untuk redundansi.'],
            ['raid 5', 'RAID 5 adalah kombinasi disk dengan parity untuk redundansi + performa.'],
            ['raid 10', 'RAID 10 adalah kombinasi RAID 0 + RAID 1.'],
        ];
    }

    // ============================================================
    // 16. CLOUD (~15)
    // ============================================================
    private function cloud(): array
    {
        return [
            ['cloud computing', 'Cloud computing adalah penggunaan sumber daya komputasi melalui internet.'],
            ['google drive', 'Google Drive adalah layanan penyimpanan cloud dari Google.'],
            ['dropbox', 'Dropbox adalah layanan penyimpanan cloud.'],
            ['onedrive', 'OneDrive adalah layanan penyimpanan cloud dari Microsoft.'],
            ['icloud', 'iCloud adalah layanan cloud dari Apple.'],
            ['aws', 'AWS (Amazon Web Services) adalah layanan cloud dari Amazon.'],
            ['azure', 'Microsoft Azure adalah layanan cloud dari Microsoft.'],
            ['gcp', 'Google Cloud Platform adalah layanan cloud dari Google.'],
            ['vps', 'VPS (Virtual Private Server) adalah server virtual dari layanan cloud.'],
            ['docker', 'Docker adalah platform containerization untuk aplikasi.'],
            ['kubernetes', 'Kubernetes adalah sistem orkestrasi container.'],
            ['container', 'Container adalah paket software yang berisi aplikasi dan dependensinya.'],
            ['serverless', 'Serverless adalah model cloud di mana penyedia mengelola server sepenuhnya.'],
        ];
    }

    // ============================================================
    // 17. MASALAH UMUM (~20)
    // ============================================================
    private function masalahUmum(): array
    {
        return [
            ['komputer lemot', 'Kalau komputer lemot, coba: tutup aplikasi tidak perlu, bersihkan file temporary, scan virus, restart, atau tambah RAM.'],
            ['komputer lambat', 'Komputer lambat bisa karena RAM penuh, banyak startup, atau virus. Coba restart dan scan antivirus.'],
            ['komputer tidak menyala', 'Cek: kabel power, tombol power, PSU, dan indikator lampu. Kalau tetap mati, hubungi IT Support.'],
            ['komputer restart sendiri', 'Bisa karena overheat, PSU lemah, atau RAM rusak. Cek suhu CPU dan pastikan fan berputar.'],
            ['komputer blue screen', 'Blue screen (BSOD) biasanya karena driver bermasalah, RAM rusak, atau hardware error. Coba restart ke safe mode.'],
            ['wifi tidak connect', 'Coba: restart router, lupakan jaringan dan connect ulang, cek password, atau pindah lebih dekat ke router.'],
            ['internet lambat', 'Cek: siapa yang pakai bandwidth, restart router, test speed di speedtest.net, atau hubungi ISP.'],
            ['email tidak bisa kirim', 'Cek: koneksi internet, ukuran attachment (maks 25MB), alamat email penerima, dan folder outbox.'],
            ['lupa password', 'Klik "Lupa Password" di halaman login atau hubungi IT Support untuk reset.'],
            ['password salah', 'Cek Caps Lock, keyboard language, dan pastikan tidak ada spasi. Kalau lupa, reset password.'],
            ['monitor tidak menampilkan gambar', 'Cek kabel VGA/HDMI, power monitor, dan pastikan monitor menyala.'],
            ['mouse tidak berfungsi', 'Cek kabel USB, ganti port USB, atau ganti baterai (kalau wireless).'],
            ['keyboard tidak berfungsi', 'Cek kabel USB, ganti port, atau restart komputer.'],
            ['file tidak bisa dibuka', 'Pastikan software pendukung terinstall. Kalau file corrupt, coba restore dari backup.'],
            ['file hilang', 'Cek Recycle Bin, cari di folder lain, atau restore dari backup.'],
            ['usb tidak terbaca', 'Coba port lain, cek Disk Management, atau test di komputer lain.'],
            ['virus komputer', 'Scan dengan antivirus, jangan buka attachment mencurigakan, dan hindari download dari sumber tidak resmi.'],
            ['baterai laptop cepat habis', 'Kurangi brightness, tutup aplikasi tidak perlu, matikan WiFi/BT jika tidak dipakai, atau ganti baterai.'],
            ['fan laptop berisik', 'Bersihkan debu di fan, gunakan cooling pad, atau ganti fan jika sudah rusak.'],
            ['layar laptop bergaris', 'Bisa karena kabel fleksibel layar atau LCD rusak. Hubungi teknisi.'],
            ['tidak bisa install aplikasi', 'Cek hak akses admin, ruang disk, dan pastikan file installer tidak corrupt.'],
        ];
    }

    // ============================================================
    // 18. FAQ SIT (~20)
    // ============================================================
    private function faqSIT(): array
    {
        return [
            ['apa itu sit', 'SIT (Suralaya Information Center) adalah portal informasi aplikasi untuk UBP Suralaya.'],
            ['sit', 'SIT adalah singkatan dari Suralaya Information Center — portal informasi aplikasi UBP Suralaya.'],
            ['apa itu ubp suralaya', 'UBP Suralaya adalah Unit Bisnis Pembangkitan Suralaya, salah satu unit PLN Indonesia Power.'],
            ['ubp suralaya', 'UBP Suralaya adalah unit pembangkit listrik tenaga uap (PLTU) di Suralaya, Banten.'],
            ['pln indonesia power', 'PLN Indonesia Power adalah anak perusahaan PLN yang bergerak di bidang pembangkitan listrik.'],
            ['cara login', 'Cara login: buka halaman login, masukkan email & password, lalu klik Login.'],
            ['cara login ke portal', 'Buka portal SIT, klik ikon gear di header, masukkan email & password, klik Login.'],
            ['cara akses aplikasi', 'Buka portal SIT, cari aplikasi yang diinginkan, klik kartu aplikasi untuk membukanya.'],
            ['cara reset password', 'Klik "Lupa Password" di halaman login, atau hubungi IT Support.'],
            ['lupa password akun', 'Hubungi IT Support atau admin untuk reset password akun Anda.'],
            ['hubungi it support', 'IT Support bisa dihubungi via Helpdesk atau email ke it.support@pln.co.id.'],
            ['kontak it support', 'Hubungi IT Support via Helpdesk PLN atau email ke it.support@pln.co.id.'],
            ['cara ajukan tiket', 'Buka Helpdesk, klik "Buat Tiket", isi form, dan submit.'],
            ['waktu operasional it support', 'IT Support tersedia Senin-Jumat 08:00-17:00 WIB.'],
            ['apa itu chatbot ini', 'Chatbot ini adalah SIS Assistant — asisten virtual untuk informasi aplikasi dan IT di UBP Suralaya.'],
            ['cara pakai chatbot ini', 'Cukup ketik pertanyaan Anda di kolom chat, dan saya akan menjawab sebisa mungkin.'],
        ];
    }

    // ============================================================
    // 19. PLN UMUM (~15)
    // ============================================================
    private function plnUmum(): array
    {
        return [
            ['pln', 'PLN (Perusahaan Listrik Negara) adalah perusahaan listrik milik negara Indonesia.'],
            ['apa itu pln', 'PLN adalah Badan Usaha Milik Negara (BUMN) yang bergerak di bidang penyediaan tenaga listrik.'],
            ['pln adalah', 'PLN adalah perusahaan listrik negara Indonesia yang menyediakan layanan kelistrikan.'],
            ['pltu', 'PLTU (Pembangkit Listrik Tenaga Uap) adalah pembangkit yang menggunakan uap untuk memutar turbin.'],
            ['plta', 'PLTA (Pembangkit Listrik Tenaga Air) adalah pembangkit yang menggunakan air.'],
            ['pltg', 'PLTG (Pembangkit Listrik Tenaga Gas) adalah pembangkit yang menggunakan gas.'],
            ['pembangkit listrik', 'Pembangkit listrik adalah fasilitas yang mengubah energi menjadi listrik.'],
            ['kelistrikan', 'Kelistrikan adalah sistem yang menghasilkan, mentransmisikan, dan mendistribusikan listrik.'],
            ['token listrik', 'Token listrik adalah kode 20 digit untuk mengisi ulang pulsa listrik prabayar.'],
            ['tagihan listrik', 'Tagihan listrik adalah biaya pemakaian listrik yang harus dibayar setiap bulan.'],
            ['pln mobile', 'PLN Mobile adalah aplikasi resmi PLN untuk layanan pelanggan.'],
            ['daya listrik', 'Daya listrik diukur dalam Watt (W) atau kilowatt (kW).'],
            ['kwh', 'kWh (kilowatt-hour) adalah satuan energi listrik yang biasa dipakai di tagihan.'],
        ];
    }

    // ============================================================
    // 20. TROUBLESHOOTING (~20)
    // ============================================================
    private function troubleshooting(): array
    {
        return [
            ['cara restart komputer', 'Klik Start > Power > Restart, atau tekan Ctrl+Alt+Del pilih Restart.'],
            ['cara cek ip', 'Buka Command Prompt, ketik ipconfig, lihat IPv4 Address.'],
            ['cara cek koneksi internet', 'Buka Command Prompt, ketik ping 8.8.8.8. Kalau ada reply, internet OK.'],
            ['cara clear cache browser', 'Tekan Ctrl+Shift+Del di browser, pilih Cache, klik Clear.'],
            ['cara screenshot', 'Tekan Windows + Shift + S untuk screenshot sebagian, atau PrintScreen untuk seluruh layar.'],
            ['cara cek spesifikasi komputer', 'Tekan Windows + Pause/Break, atau buka Settings > About.'],
            ['cara task manager', 'Tekan Ctrl+Shift+Esc untuk membuka Task Manager.'],
            ['cara buka command prompt', 'Tekan Windows + R, ketik cmd, tekan Enter.'],
            ['cara cek disk space', 'Buka File Explorer > This PC, lihat kapasitas drive.'],
            ['cara update windows', 'Buka Settings > Update & Security > Windows Update > Check for updates.'],
            ['cara install aplikasi', 'Download installer, jalankan .exe, ikuti wizard instalasi.'],
            ['cara uninstall aplikasi', 'Buka Control Panel > Programs and Features, pilih aplikasi, klik Uninstall.'],
            ['cara copy paste', 'Ctrl+C untuk copy, Ctrl+V untuk paste, Ctrl+X untuk cut.'],
            ['cara save file', 'Ctrl+S untuk save, atau File > Save As untuk simpan dengan nama baru.'],
            ['cara print dokumen', 'Ctrl+P untuk print, pilih printer, klik Print.'],
            ['cara connect wifi', 'Klik ikon WiFi di taskbar, pilih jaringan, masukkan password.'],
            ['cara cek antivirus', 'Buka antivirus (Kaspersky, Windows Defender, dll), klik Scan.'],
            ['cara backup data', 'Copy folder penting ke eksternal drive atau cloud storage.'],
            ['cara kompres file', 'Klik kanan file/folder > Send to > Compressed (zipped) folder.'],
            ['cara ekstrak zip', 'Klik kanan file .zip > Extract All, pilih lokasi, klik Extract.'],
        ];
    }

    // ============================================================
    // 21. SIAM — MANAJEMEN ASET (~50)
    // ============================================================
    private function siamAset(): array
    {
        return [
            // ===== Konsep Dasar =====
            ['apa itu siam', 'SIAM (Sistem Informasi Aset Manajemen) adalah aplikasi untuk mengelola aset IT perusahaan — mulai dari pendataan, serah terima, peminjaman, perbaikan, hingga monitoring hak kepemilikan (milik/sewa).'],
            ['siam', 'SIAM adalah Sistem Informasi Aset Manajemen — aplikasi pengelolaan aset IT perusahaan.'],
            ['fungsi siam', 'SIAM berfungsi untuk mendata, melacak, dan mengelola aset IT: siapa pemegangnya, di mana lokasinya, status, hak kepemilikan, dan riwayat perawatan.'],
            ['kegunaan siam', 'SIAM berguna untuk pelacakan aset, laporan kepemilikan (milik/sewa), monitoring status, perencanaan perawatan, dan audit aset IT.'],

            // ===== Dashboard =====
            ['cara lihat dashboard siam', 'Buka menu Dashboard SIAM di sidebar. Di sana ada grafik Ringkasan Aset, Perbandingan Hak Milik vs Sewa, Breakdown per Kategori & Model, Status Aset, dan Tren Perbaikan.'],
            ['dashboard siam', 'Dashboard SIAM adalah halaman utama yang menampilkan ringkasan & statistik aset IT: total, status, hak kepemilikan, kategori, brand, model, tren perbaikan, dll.'],
            ['cara filter dashboard', 'Di Dashboard SIAM, ada 5 dropdown filter: Kategori, Brand, Model, Tahun Pembelian, dan Hak Kepemilikan. Pilih salah satu (atau lebih) — semua grafik & tabel otomatis ter-filter.'],
            ['reset filter dashboard', 'Kalau mau reset filter, klik tombol "Reset Filter" berwarna merah di kanan atas panel filter.'],

            // ===== Hak Kepemilikan =====
            ['apa itu hak milik', 'Hak Milik (owned) artinya aset dibeli sendiri oleh perusahaan. Nilainya tercatat sebagai nilai aset perusahaan.'],
            ['apa itu sewa', 'Sewa (leased) artinya aset disewa dari vendor. Ada biaya bulanan, kontrak, dan masa berlaku yang harus dipantau.'],
            ['perbedaan hak milik dan sewa', "**Hak Milik (owned)**: aset dibeli sendiri, ada nilai pembelian, tidak ada biaya bulanan.\n**Sewa (leased)**: aset disewa vendor, ada biaya bulanan, ada kontrak & masa berlaku."],
            ['apa itu ownership', 'Ownership / hak kepemilikan menentukan apakah aset itu milik perusahaan (owned) atau sewa (leased). Berpengaruh ke biaya (pembelian vs bulanan).'],
            ['apa itu owned', 'Owned = hak milik. Aset dibeli sendiri, tercatat di nilai aset perusahaan.'],
            ['apa itu leased', 'Leased = sewa. Aset disewa dari vendor, ada biaya bulanan dan kontrak.'],
            ['bedanya owned dan leased', '**Owned** = milik sendiri (bayar sekali). **Leased** = sewa (bayar bulanan, ada kontrak).'],

            // ===== Status Aset =====
            ['status aset apa saja', 'Status aset: **Tersedia** (available), **Dipakai** (in_use), **Dipinjam** (loaned), **Perbaikan** (maintenance), **Pensiun** (retired), **Hilang** (lost).'],
            ['apa itu status tersedia', 'Status "Tersedia" (available) artinya aset siap dipakai, tidak sedang dipegang siapa pun.'],
            ['apa itu status dipakai', 'Status "Dipakai" (in_use) artinya aset sedang dipegang/digunakan user tertentu.'],
            ['apa itu status dipinjam', 'Status "Dipinjam" (loaned) artinya aset sedang dipinjam untuk jangka waktu tertentu.'],
            ['apa itu status perbaikan', 'Status "Perbaikan" (maintenance) artinya aset sedang dalam perawatan/servis.'],
            ['apa itu status pensiun', 'Status "Pensiun" (retired) artinya aset sudah tidak dipakai lagi.'],
            ['apa itu status hilang', 'Status "Hilang" (lost) artinya aset tidak ditemukan / hilang.'],
            ['apa itu available', 'Available = tersedia. Aset siap dipakai, tidak sedang dipegang siapa pun.'],
            ['apa itu in use', 'In Use = dipakai. Aset sedang digunakan user tertentu.'],
            ['apa itu maintenance', 'Maintenance = perbaikan. Aset sedang di-servis atau dalam perawatan.'],
            ['apa itu retired', 'Retired = pensiun. Aset sudah tidak digunakan lagi.'],

            // ===== Kategori/Brand/Model =====
            ['apa itu kategori aset', 'Kategori aset adalah pengelompokan aset berdasarkan jenisnya — misal Laptop, PC Desktop, Printer, Monitor.'],
            ['apa itu brand aset', 'Brand adalah merek aset — misal Lenovo, HP, Epson, Logitech.'],
            ['apa itu model aset', 'Model adalah tipe spesifik dari brand — misal Lenovo ThinkPad T14, HP ProDesk 400 G9.'],
            ['apa itu asset code', 'Asset Code adalah kode unik aset (contoh: AST-2026-0001) untuk identifikasi internal.'],
            ['apa itu serial number', 'Serial Number (SN) adalah nomor seri dari pabrik — unik per unit.'],
            ['apa itu hostname', 'Hostname adalah nama jaringan komputer (contoh: NB-T14-001), dipakai untuk identifikasi di jaringan.'],

            // ===== Nilai Aset =====
            ['apa itu nilai aset', 'Nilai aset adalah total harga pembelian aset hak milik. Untuk aset sewa, yang dicatat adalah biaya bulanan.'],
            ['apa itu biaya sewa', 'Biaya sewa adalah pengeluaran bulanan untuk aset yang di-sewa dari vendor (contoh: Rp 500.000/bulan).'],
            ['apa itu kontrak sewa', 'Kontrak sewa adalah perjanjian antara perusahaan dengan vendor sewa — mencakup periode, biaya, dan ketentuan.'],

            // ===== Garansi & Vendor =====
            ['apa itu garansi', 'Garansi adalah jaminan dari vendor/pabrikan bahwa aset akan diperbaiki gratis jika ada kerusakan dalam periode tertentu.'],
            ['apa itu vendor', 'Vendor adalah pihak ketiga yang menyediakan aset (jual/sewa) atau jasa perbaikan.'],
            ['jenis vendor', 'Vendor di SIAM ada 3 jenis: **sewa** (penyedia sewa), **pembelian** (penjual), dan **both** (keduanya).'],

            // ===== Aksi =====
            ['cara tambah aset', 'Buka menu Aset > Tambah Aset. Isi data aset (SN, brand, model, kategori, hak kepemilikan, dll), lalu simpan.'],
            ['cara edit aset', 'Buka detail aset, klik tombol Edit. Ubah data yang perlu, lalu simpan.'],
            ['cara hapus aset', 'Buka detail aset, klik tombol Hapus. Konfirmasi penghapusan.'],
            ['cara export aset', 'Di halaman daftar aset, klik tombol **Export Excel** atau **Export PDF**. Filter yang aktif akan ikut ter-export.'],
            ['cara cari aset', 'Di halaman daftar aset, ketik di kolom pencarian: SN, brand, model, hostname, atau nama user pemegang.'],
        ];
    }

    // ============================================================
    // 22. SIAM — TRANSAKSI (Serah Terima, Pinjam, Perbaikan) (~30)
    // ============================================================
    private function siamTransaksi(): array
    {
        return [
            // ===== Serah Terima (Assignment) =====
            ['apa itu serah terima', 'Serah terima (assignment) adalah proses pemindahan aset dari IT ke user tertentu. Dicatat siapa penerima, kapan, kondisi, dan lokasi.'],
            ['apa itu assignment', 'Assignment (serah terima) adalah proses aset diserahkan ke user untuk dipakai.'],
            ['apa itu bast', 'BAST (Berita Acara Serah Terima) adalah dokumen bukti serah terima aset. Nomornya dicatat di setiap record assignment.'],
            ['cara serah terima aset', 'Buka detail aset > klik "Assign" atau dari menu Serah Terima > Tambah. Pilih user, isi kondisi & catatan, simpan.'],
            ['apa itu pengembalian aset', 'Pengembalian aset adalah proses user mengembalikan aset ke kantor/IT. Status aset kembali "Tersedia".'],

            // ===== Peminjaman (Loan) =====
            ['apa itu peminjaman', 'Peminjaman (loan) adalah penggunaan aset untuk jangka waktu tertentu (misal dinas luar, meeting), harus dikembalikan sesuai due date.'],
            ['apa itu loan', 'Loan (peminjaman) adalah aset dipinjam user untuk periode tertentu, dengan tanggal pinjam & jatuh tempo.'],
            ['apa itu due date', 'Due date adalah tanggal jatuh tempo pengembalian aset yang dipinjam.'],
            ['apa itu overdue', 'Overdue artinya peminjaman sudah melewati tanggal jatuh tempo (terlambat).'],
            ['cara pinjam aset', 'Buka menu Peminjaman > Tambah. Pilih aset & user, isi tanggal pinjam, due date, dan keperluan.'],
            ['cara kembalikan aset pinjam', 'Di halaman detail peminjaman, klik tombol "Kembalikan", isi kondisi saat kembali & catatan, simpan.'],
            ['apa itu status borrowed', 'Status "borrowed" artinya aset sedang dipinjam dan belum dikembalikan.'],
            ['apa itu status returned', 'Status "returned" artinya aset sudah dikembalikan.'],

            // ===== Perbaikan (Maintenance) =====
            ['apa itu perbaikan', 'Perbaikan (maintenance) adalah proses servis aset yang rusak, baik preventive (rutin) maupun corrective (karena kerusakan).'],
            ['apa itu maintenance', 'Maintenance (perbaikan) adalah perawatan/servis aset. Di SIAM ada 3 tipe: preventive, corrective, upgrade.'],
            ['jenis maintenance', 'Tipe maintenance: **preventive** (rutin/pencegahan), **corrective** (karena rusak), **upgrade** (peningkatan spesifikasi).'],
            ['apa itu preventive', 'Preventive maintenance adalah perawatan rutin untuk mencegah kerusakan — misal pembersihan, update BIOS.'],
            ['apa itu corrective', 'Corrective maintenance adalah perbaikan karena ada kerusakan — misal keyboard rusak, baterai drop.'],
            ['apa itu upgrade', 'Upgrade adalah peningkatan spesifikasi aset — misal tambah RAM, ganti SSD.'],
            ['cara catat perbaikan', 'Buka menu Perbaikan > Tambah. Pilih aset, isi jenis, masalah, teknisi, biaya, tanggal mulai.'],
            ['apa itu condition percent', 'Condition percent adalah nilai kondisi aset (0-100%) — 100% = mulus, 0% = rusak total.'],
            ['apa itu condition before after', 'Di record perbaikan ada kondisi **sebelum** (sebelum diperbaiki) dan **sesudah** (setelah diperbaiki).'],

            // ===== Movement / Log =====
            ['apa itu asset movement', 'Asset Movement adalah riwayat pergerakan aset — dari siapa ke siapa, dari lokasi mana ke mana.'],
            ['apa itu activity log', 'Activity Log adalah catatan aktivitas di sistem — siapa melakukan apa, kapan. Berguna untuk audit.'],
        ];
    }

    // ============================================================
    // 23. SIAM — KONSUMABLE & UMUM (~20)
    // ============================================================
    private function siamKonsumable(): array
    {
        return [
            // ===== Konsumable =====
            ['apa itu konsumable', 'Konsumable adalah barang habis pakai seperti mouse, keyboard, HDD eksternal. Stoknya dipantau; ada batas minimum untuk warning.'],
            ['apa itu consumable', 'Consumable (konsumable) adalah barang habis pakai yang stoknya berkurang saat dipakai user.'],
            ['apa itu stock available', 'Stock Available adalah jumlah stok konsumable yang tersedia untuk dipakai.'],
            ['apa itu stock minimum', 'Stock Minimum adalah batas minimum stok. Kalau stock available ≤ minimum, muncul warning "low stock".'],
            ['apa itu low stock', 'Low stock artinya stok konsumable sudah di bawah/sama dengan batas minimum — perlu segera restock.'],
            ['apa itu stock out', 'Stock out artinya stok konsumable habis (0 unit).'],
            ['cara catat transaksi konsumable', 'Buka menu Transaksi Konsumable > Tambah. Pilih konsumable, tipe (in/out/return), jumlah, penerima, dan simpan.'],
            ['jenis transaksi konsumable', 'Transaksi konsumable ada 3 tipe: **in** (masuk/restock), **out** (keluar/dipakai), **return** (dikembalikan).'],
            ['apa itu transaksi in', 'Transaksi "in" adalah konsumable masuk (restock/pembelian baru). Stok total & tersedia naik.'],
            ['apa itu transaksi out', 'Transaksi "out" adalah konsumable keluar (dipakai user). Stok tersedia turun.'],
            ['apa itu transaksi return', 'Transaksi "return" adalah konsumable dikembalikan user. Stok tersedia naik kembali.'],

            // ===== User =====
            ['cara tambah user', 'Buka menu User > Tambah User. Isi nama, email, password, departemen, lokasi, dan role.'],
            ['role user apa saja', 'Role user di SIAM: **admin** (full access), **support** (bisa tambah aplikasi), **user** (akses terbatas).'],
            ['apa itu departemen', 'Departemen adalah unit kerja user — misal IT, Finance, HRD, Marketing, Operations.'],
            ['apa itu lokasi', 'Lokasi adalah tempat user/aset berada — misal Gedung A - Lt. 1 - Ruang IT.'],

            // ===== Laporan =====
            ['laporan apa saja di siam', 'Di SIAM ada laporan: Daftar Aset, Daftar Serah Terima, Daftar Peminjaman, Daftar Perbaikan, Transaksi Konsumable, Activity Log.'],
            ['cara lihat laporan', 'Buka menu yang sesuai (Aset, Serah Terima, Peminjaman, Perbaikan), lalu klik Export Excel/PDF sesuai kebutuhan.'],

            // ===== Info Umum SIAM =====
            ['apa bedanya aset dan konsumable', '**Aset**: barang bernilai, punya SN, dilacak per unit (laptop, printer). **Konsumable**: barang habis pakai, dilacak stok total (mouse, keyboard).'],
            ['apa itu asset tag', 'Asset Tag adalah label fisik yang ditempel di aset berisi Asset Code untuk identifikasi cepat.'],
            ['apa itu current user', 'Current User adalah user yang sedang memegang aset saat ini.'],
            ['apa itu current location', 'Current Location adalah lokasi aset saat ini berada.'],
        ];
    }
}
