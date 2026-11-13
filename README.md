# SIGAP Lingkungan

**Rancang Bangun Sistem Informasi Pelaporan dan Pemantauan Kebersihan Lingkungan RT/RW dengan Pelaporan Anonim Berbasis Web**

SIGAP Lingkungan adalah aplikasi web untuk melaporkan dan memantau masalah kebersihan lingkungan di tingkat RT/RW. Warga bisa melapor tanpa identitasnya terlihat oleh pengurus, sehingga tidak perlu takut dicap "tukang ngadu" atau diperlakukan beda oleh tetangga maupun ketua RT/RW.

Proyek ini disusun sebagai tugas Program Studi Teknik Informatika, Fakultas Teknik dan Ilmu Komputer, Universitas Indraprasta PGRI Jakarta (2026).

---

## Tim Pengembang (Kelompok 6)

| No  | Nama                  | NPM          |
| --- | --------------------- | ------------ |
| 1   | Ryan Ikramullah       | 202343501549 |
| 2   | Yudi Ahmad Taqiyyudin | 202343501555 |
| 3   | Kumalasari            | 202343501459 |

---

## Ringkasan Proyek

| Aspek            | Keterangan                                                                                  |
| ---------------- | ------------------------------------------------------------------------------------------- |
| Nama proyek      | SIGAP Lingkungan                                                                            |
| Jenis penelitian | Pengembangan perangkat lunak (rancang bangun sistem informasi)                              |
| Platform         | Aplikasi web responsif yang dapat dipasang sebagai PWA                                      |
| Pengguna         | Warga (pelapor), pengurus RT/RW, dan admin sistem                                           |
| Keunggulan utama | Pelaporan anonim terhadap pengurus RT/RW, identitas pelapor tetap tersimpan untuk anti-spam |

## Latar Belakang

Selama ini warga melaporkan masalah lingkungan, seperti tumpukan sampah liar atau saluran air tersumbat, langsung ke ketua RT lewat lisan atau grup WhatsApp. Laporan gampang tenggelam di antara obrolan lain, tidak ada bukti tindak lanjut, dan pengurus sulit memantau area mana yang paling sering bermasalah.

Di sisi lain, banyak warga enggan melapor secara terbuka karena khawatir dijauhi tetangga atau diperlakukan berbeda oleh pengurus. Akibatnya, banyak masalah dibiarkan tanpa pernah dilaporkan.

## Permasalahan

- Warga belum punya kanal terstruktur untuk melaporkan masalah kebersihan/lingkungan.
- Laporan lewat WhatsApp mudah hilang atau terlewat.
- Warga takut dikucilkan atau didiskriminasi jika melapor secara terbuka.
- Pengurus RT/RW tidak punya data terpusat untuk memantau status penanganan laporan.
- Belum ada riwayat atau rekap untuk menganalisis area rawan masalah.

## Tujuan Sistem

- Menyediakan kanal pelaporan yang anonim terhadap pengurus RT/RW.
- Menjaga akuntabilitas dengan menyimpan data pelapor di sistem (tidak ditampilkan ke pengurus) untuk mencegah laporan spam atau palsu.
- Memudahkan pengurus memantau, mengelola status, dan menindaklanjuti laporan.
- Menyediakan rekap dan riwayat laporan sebagai bahan evaluasi dan pengambilan keputusan.

## Fitur / Ruang Lingkup

1. **Modul pelaporan warga**: input laporan, foto, dan lokasi/titik area, dengan identitas pelapor disamarkan dari tampilan pengurus.
2. **Autentikasi ringan**: memakai nomor HP/akun, hanya untuk verifikasi dan anti-spam, tidak ditampilkan ke pengurus.
3. **Manajemen laporan untuk pengurus RT/RW**: laporan tampil sebagai "Warga #xx" dan statusnya bisa diubah (baru, diproses, selesai).
4. **Riwayat dan rekap laporan**: filter berdasarkan status, tanggal, dan area.
5. **Notifikasi status laporan**: pelapor bisa memantau perkembangan lewat kode tiket unik.

Sistem berskala satu RT/RW dan tidak terintegrasi dengan instansi pemerintah.

## Alur Singkat

```
Warga kirim laporan (anonim bagi pengurus)
        ↓
Pengurus RT/RW melihat laporan sebagai "Warga #xx"
        ↓
Status diperbarui: Baru → Diproses → Selesai
        ↓
Pelapor memantau lewat kode tiket
```

## Status Proyek

Tahap saat ini: perancangan sistem (judul, latar belakang, permasalahan, tujuan, dan ruang lingkup). Implementasi akan ditambahkan pada tahap berikutnya.

## Daftar Pustaka

- IEEE. (1998). _IEEE Std 830-1998: IEEE Recommended Practice for Software Requirements Specifications_. New York: The Institute of Electrical and Electronics Engineers.
- Pressman, R. S., & Maxim, B. R. (2020). _Software Engineering: A Practitioner's Approach_ (9th ed.). New York: McGraw-Hill Education.
- Sommerville, I. (2016). _Software Engineering_ (10th ed.). Harlow: Pearson Education.

---

Program Studi Teknik Informatika · Fakultas Teknik dan Ilmu Komputer · Universitas Indraprasta PGRI · Jakarta · 2026
