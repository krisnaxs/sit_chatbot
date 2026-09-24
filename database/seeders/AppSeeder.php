<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            // ============================================================
            // SLIDE: APP SURALAYA INFORMATION
            // ============================================================
            ['nama' => 'FIREWALL', 'gambar' => 'apps/tzaup66dBN6mTmjnqMqk8EaEKimXDqEKXpyAdP3M.png', 'url' => 'http://192.168.101.252:1000/logout?', 'clicks' => 2, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'HELPDESK', 'gambar' => 'apps/8mWyJHjCUmAwdi0BVP8WWeeDpqC6GJb19yZVzazQ.png', 'url' => 'http://helpdesk.plnindonesiapower.co.id/', 'clicks' => 2, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'MAXIMO PROD', 'gambar' => 'apps/iCe8B0zS5Yv6spR6CDUkn8076HPJANnfBrM0CTsU.jpg', 'url' => 'http://10.8.10.36:9083/maximo/ui/login', 'clicks' => 1, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'ERP PROD', 'gambar' => 'apps/p3SJW7jQLjZs73UDqwu0iavXxSPyQQl8xZDeETKi.png', 'url' => 'http://erp.indonesiapower.co.id:8000/', 'clicks' => 6, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'PIVISION REOC', 'gambar' => 'apps/Yoa17fX6n1nJwZTAXtHJrYp8YtAy0tCa8QovSLu5.png', 'url' => 'http://pivision.plnindonesiapower.co.id/pivision', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'HXMS', 'gambar' => 'apps/nmpgRwOA7id6WanZvDUJoP35ehzwUThJncXui9qh.png', 'url' => 'https://hxms.pln.co.id/', 'clicks' => 1, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Email Office 365', 'gambar' => 'apps/MdAm3RJrwfdBtS7TbEXmGPma9JdQ1wMfA1pB2rTq.png', 'url' => 'http://office.com/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Email', 'gambar' => 'apps/woHXvhhyhWnVZf6onQdP1ew7kJAcNlKITovYdOti.png', 'url' => 'http://email.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Email PLN', 'gambar' => 'apps/NuJ1Mr19nAU2WgkGBFt7uHUEycLfslunOxSvSu6G.png', 'url' => 'https://webmail.pln.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'ERPVW', 'gambar' => 'apps/CyqHGlZCjEJ3rAOoqPq5QMUicNMJIjmYUdoCrtqj.png', 'url' => 'http://erpvw.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Maximo QA', 'gambar' => 'apps/Kj7bLTYzVjWPWdW451SSlm6i2SVXXNSaDbZXRS5u.jpg', 'url' => 'http://maximoqa.plnindonesiapower.co.id/maximo/', 'clicks' => 3, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'IRMA', 'gambar' => 'apps/0yNAFjkp0Pp663xUaXLFaS1027HbXnMAQjRAuOd1.png', 'url' => 'http://erm.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'PRONIA', 'gambar' => 'apps/2DTnIxvXMUrht05OIOcBp6ZPzAo3gUcOkZ2uq2VG.png', 'url' => 'http://pronia.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'AMS Korporat', 'gambar' => 'apps/5yobcHmD0otsCbMJnEtEFt7tJ6C4VDkeV2ERyrPb.png', 'url' => 'https://amskorporat.pln.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'IAM', 'gambar' => 'apps/VMmM3KmQgS4H8C5pT1ER0jGWtMC6CBsHpJJ8EvKl.png', 'url' => 'https://iam.pln.co.id/', 'clicks' => 1, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Compliance Online System (COS)', 'gambar' => 'apps/fBTpUPkzpeJde4a7jIDp70iNI1RgIfaHyqSKhBoU.png', 'url' => 'https://cos.pln.co.id/login', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'PRODIN 3.0', 'gambar' => 'apps/YTi38xt4RkBTBsiSA6Bd5PpnZx4SlgdCjS1X3vZs.png', 'url' => 'http://apps.plnindonesiapower.co.id:7001/prodin/f?p=HOME:LOGIN', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Knowledge Center (KC)', 'gambar' => 'apps/QGj7klK8g7oUCWVf4hvhiStGJWzYNd90rqZ2fKU0.png', 'url' => 'http://knowledgecenter.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'IPICOFR', 'gambar' => 'apps/Cl2JinAmRSWsYdTTEmRW8vfBDwn9kH0Cuu4imYyg.png', 'url' => 'http://apps.plnindonesiapower.co.id:7001/icofr/f?p=220', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Absensi', 'gambar' => 'apps/ga7Ke8GSMwwE71HTZY5ijf9Xz6fBW36Q3r0z5mvX.png', 'url' => 'http://absensi.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'IP-IMS', 'gambar' => 'apps/PIz7MFKG74O7vdQxkVf2wiWwPZI6hulqx2t3UeCz.png', 'url' => 'https://drive.plnindonesiapower.co.id/index.php/s/wxHTYLJXjqKQBJc', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Outage Management', 'gambar' => 'apps/TDO6jOFSYeSQ8XZMPEuQARjXloTwDJaVfKUuX6Cu.png', 'url' => 'http://192.168.10.183/outage-management', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Pro GCG', 'gambar' => 'apps/xfAlFwNC37n1D0onR4pBwL5Ofngn2jJUwjDElwDz.png', 'url' => 'http://gcg.plnindonesiapower.co.id:22023/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Assesment Maturity', 'gambar' => 'apps/INtJvwmqaA7IfQ07x9nn40JhPrmLyX7vRNPdWbX4.png', 'url' => 'http://aplikasi.plnindonesiapower.co.id/app/home/redirect/890a1c2b1fbccd173e8ac926a477c655', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'SIMBAKAR', 'gambar' => 'apps/j67ZNUtrkXe3XqgXIU9xq2Rtv5dFjK5U926GQIWp.png', 'url' => 'http://192.168.101.20/simbakar', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Tableau', 'gambar' => 'apps/1SvwtmY91cdh76yEb2VY6VAP5hP2u4SpS0ySe7bJ.png', 'url' => 'https://tableau.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'ICMS', 'gambar' => 'apps/x8ZkDuyGDR03j8fdboEHwIeTO3uvtFXaiiHwdWgf.png', 'url' => 'https://proeip.plnindonesiapower.co.id/ICMS/Icms.html', 'clicks' => 1, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],
            ['nama' => 'Dispatch REOC', 'gambar' => 'apps/PWEOdJ90Hctl4V2NK8AfgQU0EShUGwBrmLYafZKw.png', 'url' => 'http://dispatch.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'APP SURALAYA INFORMATION', 'urutan' => 1],

            // ============================================================
            // SLIDE: PLN APP
            // ============================================================
            ['nama' => 'HDKS', 'gambar' => null, 'url' => 'http://10.6.1.61/app4/login.php', 'clicks' => 1, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],
            ['nama' => 'PLN Web', 'gambar' => 'apps/7Ws0W9pb6h8iiG75EpRuDkXbiZkdxmKb5YaUh7qm.png', 'url' => 'http://www.pln.co.id/', 'clicks' => 1, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],
            ['nama' => 'NERGI', 'gambar' => null, 'url' => 'http://202.162.216.197/~neapp', 'clicks' => 0, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],
            ['nama' => 'BATUBARA', 'gambar' => null, 'url' => 'https://10.14.152.131/BatubaraOnline/', 'clicks' => 0, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],
            ['nama' => 'PORTAL', 'gambar' => null, 'url' => 'http://10.10.0.20/Portal/', 'clicks' => 1, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],
            ['nama' => 'SI-UJO', 'gambar' => null, 'url' => 'http://10.10.0.20/siujopbj/?nav=user', 'clicks' => 0, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],
            ['nama' => 'SNIPE', 'gambar' => null, 'url' => 'http://10.50.1.21/main.asp', 'clicks' => 0, 'is_active' => true, 'slide' => 'PLN APP', 'urutan' => 1],

            // ============================================================
            // SLIDE: SIS SURALAYA
            // ============================================================
            ['nama' => 'FORTINET ADMIN', 'gambar' => null, 'url' => 'https://192.168.101.252:5645/', 'clicks' => 3, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'ZABBIX', 'gambar' => null, 'url' => 'http://192.168.101.15/zabbix', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'KASPERSKY', 'gambar' => null, 'url' => 'https://192.168.101.38:8080/', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'NOC ICON+', 'gambar' => null, 'url' => 'https://mrtg.iconpln.co.id/login', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'RIVERBED', 'gambar' => null, 'url' => 'http://192.168.101.14/', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'Helpdesk', 'gambar' => null, 'url' => 'http://helpdesk.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'KASPERSKYHO', 'gambar' => null, 'url' => 'https://10.8.10.26:8080/login', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'FILE KORPORAT', 'gambar' => null, 'url' => 'https://linktr.ee/PLNIndonesiaPower', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'REPORT HELPDESK', 'gambar' => null, 'url' => 'http://reportwhd.plnindonesiapower.co.id/Reports/Pages/Folder.aspx', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'ECP', 'gambar' => null, 'url' => 'https://email.indonesiapower.co.id/ecp/', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'WLC', 'gambar' => null, 'url' => 'http://192.168.211.81/', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'PRTG', 'gambar' => null, 'url' => 'http://192.168.101.22/welcome.htm', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],
            ['nama' => 'UNIFI', 'gambar' => null, 'url' => 'https://192.168.112.10/', 'clicks' => 0, 'is_active' => true, 'slide' => 'SIS SURALAYA', 'urutan' => 1],

            // ============================================================
            // SLIDE: UBP Suralaya
            // ============================================================
            ['nama' => 'Simulasi Interaktif Turbin Control Oil', 'gambar' => 'apps/p4uStNREa1ZjB75vSMHNzCEClbtXHU6oSHmRX3IH.png', 'url' => 'https://steamturbine-control-oil.netlify.app/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'PPLS + PRODSLA', 'gambar' => 'apps/zLo1BCPO6lQlbBHKk9LH3CMf1VeuGfPZ3E6HBj21.png', 'url' => 'http://192.168.101.36/ppls//', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'LIQUID', 'gambar' => 'apps/B6AkcEfuJcHh3nUyA3oWktqK119hzif8oxpAKiSg.jpg', 'url' => 'https://liquid.plnindonesiapower.co.id/loginUser.html', 'clicks' => 1, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'ORAFIN', 'gambar' => 'apps/BNWOor8qgro105oeF47RGQSIloMBKz7VaxUb4fZm.png', 'url' => 'http://orafin.indonesiapower.co.id:8000/dev60cgi/f60cgi', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'FTP 1', 'gambar' => null, 'url' => 'http://192.168.101.9:5000/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'FTP 2', 'gambar' => null, 'url' => 'http://192.168.101.31:5000/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'Innovation', 'gambar' => 'apps/MQ4b9iX2w1ovv4yWmAjmEhcn5gLggiLK3Nr3NjMx.png', 'url' => 'http://innovation.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'CITRIX', 'gambar' => 'apps/alabe8i9NMsQah5FF7fXPbj4k4eA2LuMeH2LutQb.png', 'url' => 'https://newvip.plnindonesiapower.co.id/', 'clicks' => 1, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'Link AMS', 'gambar' => null, 'url' => 'https://linktr.ee/sekretariatplnip', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'SICOMO', 'gambar' => null, 'url' => 'http://192.168.101.26:8090/SICOMO/', 'clicks' => 1, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'Intranet SLA', 'gambar' => null, 'url' => 'http://intranet.indonesiapower.co.id/UBP_Suralaya/SitePages/Halaman.aspx', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'SIMBAKAR', 'gambar' => null, 'url' => 'http://192.168.101.10/simbakar/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'LOTO', 'gambar' => 'apps/gMozQLrzRPUYakHJI4FoHtdlD8KaNQ9YwRIfG1ed.png', 'url' => 'http://192.168.101.36/loto//', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'EA', 'gambar' => 'apps/05DCPIk8MezviNmgc1uCpSH2qOSPG9OrgxZmBmHd.png', 'url' => 'http://ea.plnindonesiapower.co.id/login.php', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'PROLAK IP', 'gambar' => null, 'url' => 'http://prolak.plnindonesiapower.co.id/Login', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'WEBSITE IP', 'gambar' => null, 'url' => 'https://www.plnindonesiapower.co.id/id/Default.aspx', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'DIRJAB', 'gambar' => 'apps/IknAuSbcWUj1q6iok17yOS2b8ayuK95IGx7ME7a0.jpg', 'url' => 'http://dirjab.plnindonesiapower.co.id/dirjab/login', 'clicks' => 1, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'IPKU IOS', 'gambar' => null, 'url' => 'https://webip.indonesiapower.co.id:6090/ipku/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'DigimonX IOS', 'gambar' => null, 'url' => 'https://apieam.plnindonesiapower.co.id:6090/digimonx/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'Diamond IOS', 'gambar' => null, 'url' => 'https://apieam.plnindonesiapower.co.id:6090/diamond/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'Pro Inventory IOS', 'gambar' => null, 'url' => 'https://webip.indonesiapower.co.id:6090/proinventory/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'IP Academy IOS', 'gambar' => 'apps/WpM3xgWWNugJlSP3oS9MQNCbknaOL4XeAUUiNrFE.jpg', 'url' => 'https://webip.indonesiapower.co.id:6090/ipacademy/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'DRIVE IP', 'gambar' => 'apps/Adydlf7mV6Do3IoJsmb55GHsaVzED1nVAJQroeb9.png', 'url' => 'http://drive.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'NEARMISS', 'gambar' => null, 'url' => 'https://nearmiss.indonesiapower.co.id/login', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'APPGAN', 'gambar' => 'apps/DG0iW25Jd5nlKHz2CFgb9XMC2Tq4hIaBFM3WWYnh.png', 'url' => 'http://appgan.plnindonesiapower.co.id/appgan/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
            ['nama' => 'EPPT', 'gambar' => 'apps/BGKc67w2kMMO40dpmokuPgCULo4cZdDWGHcLue3Q.png', 'url' => 'http://eppt.plnindonesiapower.co.id/', 'clicks' => 0, 'is_active' => true, 'slide' => 'UBP Suralaya', 'urutan' => 1],
        ];

        foreach ($apps as $data) {
            App::create($data);
        }
    }
}
