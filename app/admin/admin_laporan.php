<?php

    session_start();

    $laman = 'Laporan';
    $fileLaman = 'admin_laporan.php';

    include '../../assets/layout/admin/header.php';

    $query_laporan = "
        SELECT
            p.id_pesanan,
            p.id_customer,
            p.jumlah_beli,
            p.harga,
            p.harga_dp,
            p.total_harga,
            p.status_harga,
            p.status_pengerjaan,
            p.tanggal_pesan,
            p.tanggal_selesai,
            p.catatan_harga,
            p.id_produk,
            p.id_bahan,
            p.id_desain,
            p.id_desain_custom,
            p.ukuran,
            pr.nama_produk,
            c.nama AS customer_nama,
            c.no_hp AS customer_hp,
            c.alamat AS customer_alamat,
            b.jenis_bahan,
            w.nama_warna,
            d.nama_desain,
            d.gambar_desain,
            d.deskripsi AS desain_deskripsi,
            dc.files AS desain_custom_files,
            dc.catatan AS desain_custom_catatan,
            t.id_transaksi,
            t.metode_pembayaran,
            t.status_pembayaran,
            t.jumlah_bayar,
            t.bukti_pembayaran,
            t.tanggal_pembayaran
        FROM pesanan p
        JOIN produk pr ON p.id_produk = pr.id_produk
        JOIN customer c ON p.id_customer = c.id_customer
        JOIN bahan b ON p.id_bahan = b.id_bahan
        JOIN warna w ON b.id_warna = w.id_warna
        LEFT JOIN desain d ON p.id_desain = d.id_desain
        LEFT JOIN desain_custom dc ON p.id_desain_custom = dc.id_desain_custom
        LEFT JOIN transaksi t ON p.id_pesanan = t.id_pesanan
        WHERE p.status_pengerjaan = 'Selesai'
            AND t.status_pembayaran = 'Lunas'
        ORDER BY p.tanggal_selesai DESC, p.id_pesanan DESC
    ";

    $data_laporan = select($query_laporan);

?>

    <main class="overflow-auto" style="flex:1;">
        <section aria-label="Detail Pesanan Selesai">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Detail Pesanan Selesai</h3>
                        <small class="text-muted">Pesanan yang sudah divalidasi selesai di menu Pesanan akan tampil di sini.</small>
                    </div>
                </div>

                <div class="card-body overflow-auto" style="max-height: 500px;">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered border-dark table-hover align-middle">
                            <thead class="text-center">
                                <tr>
                                    <th>#</th>
                                    <th>No. Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Total Harga</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data_laporan)) : ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada pesanan selesai.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php $no = 1; foreach ($data_laporan as $laporan) : ?>
                                        <tr>
                                            <td class="text-center"><?= $no++; ?></td>
                                            <td class="fw-bold">#<?= str_pad($laporan['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($laporan['customer_nama']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($laporan['customer_hp']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($laporan['nama_produk']) ?></td>
                                            <td class="text-center"><?= intval($laporan['jumlah_beli']) ?> pcs</td>
                                            <td class="fw-bold">
                                                <?php if (!empty($laporan['total_harga'])) : ?>
                                                    Rp <?= number_format(intval($laporan['total_harga'])) ?>
                                                <?php else : ?>
                                                    <span class="text-muted">Menunggu Harga</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= format_tanggal_pesanan($laporan['tanggal_selesai'] ?? null) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetail"
                                                    onclick="setDetailModal(<?= htmlspecialchars(json_encode($laporan), ENT_QUOTES, 'UTF-8') ?>)">
                                                    <i class="bi bi-eye me-1"></i>Lihat
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Detail Pesanan - <span id="detailNoPesanan">#000000</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatStatusHarga(statusHarga, statusPembayaran) {
            if (statusHarga === 'Menunggu Harga') {
                return 'Pending';
            }

            if (statusHarga === 'Disetujui') {
                return ['DP', 'Lunas'].includes(statusPembayaran)
                    ? `Disetujui - ${statusPembayaran}`
                    : 'Disetujui - Belum Bayar';
            }

            return statusHarga || 'Menunggu Harga';
        }

        function formatTanggalPesanan(value) {
            if (!value) {
                return '-';
            }

            const parts = String(value).split(/[- :]/);
            if (parts.length < 3) {
                return '-';
            }

            const jam = parts[3] && parts[4] ? ` ${parts[3]}:${parts[4]}` : '';
            return `${parts[2]}/${parts[1]}/${parts[0]}${jam}`;
        }

        function renderDesignDetail(pesanan) {
            if (pesanan.desain_custom_files) {
                let files = {};
                try {
                    files = typeof pesanan.desain_custom_files === 'string'
                        ? JSON.parse(pesanan.desain_custom_files || '{}')
                        : (pesanan.desain_custom_files || {});
                } catch (error) {
                    files = {};
                }

                const images = [
                    ['depan', 'Tampak Depan'],
                    ['belakang', 'Tampak Belakang'],
                    ['kanan', 'Tampak Kanan'],
                    ['kiri', 'Tampak Kiri']
                ]
                    .filter(([key]) => files[key])
                    .map(([key, label]) => `
                        <div class="col-md-6">
                            <a href="../../assets/img/desain_custom/${escapeHtml(files[key])}" target="_blank" class="text-decoration-none text-dark">
                                <div class="border rounded-3 overflow-hidden bg-light h-100">
                                    <img src="../../assets/img/desain_custom/${escapeHtml(files[key])}" alt="${escapeHtml(label)}" class="w-100" style="height: 160px; object-fit: cover;">
                                    <div class="p-2 fw-semibold small">${escapeHtml(label)}</div>
                                </div>
                            </a>
                        </div>
                    `)
                    .join('');

                const logos = Array.isArray(files.logo) ? files.logo : [];
                const logoHtml = logos.length
                    ? `
                        <div class="mt-3">
                            <p class="fw-semibold mb-2">Logo</p>
                            <div class="d-flex flex-wrap gap-2">
                                ${logos.map(logo => `
                                    <a href="../../assets/img/desain_custom/${escapeHtml(logo)}" target="_blank">
                                        <img src="../../assets/img/desain_custom/${escapeHtml(logo)}" alt="Logo" class="border rounded-3 bg-light" style="width: 88px; height: 88px; object-fit: contain;">
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    `
                    : '';

                const catatan = pesanan.desain_custom_catatan
                    ? `<div class="alert alert-light border mt-3 mb-0">${escapeHtml(pesanan.desain_custom_catatan).replaceAll('\n', '<br>')}</div>`
                    : '';

                return `
                    <hr>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-images me-2"></i>Desain Custom Upload</h6>
                        ${images ? `<div class="row g-3">${images}</div>` : '<p class="text-muted">Tidak ada gambar desain custom.</p>'}
                        ${logoHtml}
                        ${catatan}
                    </div>
                `;
            }

            if (pesanan.gambar_desain) {
                return `
                    <hr>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-palette me-2"></i>Desain Dipilih</h6>
                        <div class="border rounded-3 overflow-hidden bg-light">
                            <a href="../../assets/img/desain/${escapeHtml(pesanan.gambar_desain)}" target="_blank">
                                <img src="../../assets/img/desain/${escapeHtml(pesanan.gambar_desain)}" alt="${escapeHtml(pesanan.nama_desain || 'Desain')}" class="w-100" style="max-height: 320px; object-fit: contain;">
                            </a>
                            <div class="p-3">
                                <h6 class="fw-bold mb-1">${escapeHtml(pesanan.nama_desain || 'Desain Katalog')}</h6>
                                ${pesanan.desain_deskripsi ? `<p class="text-muted small mb-0">${escapeHtml(pesanan.desain_deskripsi)}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            return `
                <hr>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>Data gambar desain tidak tersedia.
                </div>
            `;
        }

        function setDetailModal(pesanan) {
            document.getElementById('detailNoPesanan').textContent = '#' + String(pesanan.id_pesanan).padStart(6, '0');

            const alamatArray = String(pesanan.customer_alamat || '').split('\n').filter(a => a.trim());
            const totalHarga = parseInt(pesanan.total_harga || pesanan.harga || 0, 10);
            const alamat = alamatArray.join(' - ');

            const content = `
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Nama Pelanggan</p>
                        <p class="fw-bold">${escapeHtml(pesanan.customer_nama)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">No HP</p>
                        <p class="fw-bold">${escapeHtml(pesanan.customer_hp)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Tanggal Pesan</p>
                        <p class="fw-bold">${formatTanggalPesanan(pesanan.tanggal_pesan)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Tanggal Selesai</p>
                        <p class="fw-bold">${formatTanggalPesanan(pesanan.tanggal_selesai)}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <p class="text-muted small mb-1">Alamat</p>
                    <p class="fw-bold">${escapeHtml(alamat)}</p>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Produk</p>
                        <p class="fw-bold">${escapeHtml(pesanan.nama_produk)}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Jumlah</p>
                        <p class="fw-bold">${parseInt(pesanan.jumlah_beli || 0, 10)} pcs</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Bahan</p>
                        <p class="fw-bold">${escapeHtml(pesanan.jenis_bahan || '-')}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Warna</p>
                        <p class="fw-bold">${escapeHtml(pesanan.nama_warna || '-')}</p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-6">
                        <p class="text-muted small mb-1">Harga Admin</p>
                        <p class="fw-bold fs-5">${totalHarga > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga) : 'Menunggu Harga'}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Status Harga</p>
                        <p class="fw-bold">
                            <span class="badge ${pesanan.status_harga === 'Harga Diberikan' ? 'bg-info text-dark' : pesanan.status_harga === 'Disetujui' ? 'bg-success' : pesanan.status_harga === 'Ditolak' ? 'bg-danger' : 'bg-secondary'}">
                                ${escapeHtml(formatStatusHarga(pesanan.status_harga, pesanan.status_pembayaran))}
                            </span>
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-1">Status Pengerjaan</p>
                        <p class="fw-bold">${escapeHtml(pesanan.status_pengerjaan || 'Menunggu Pembayaran')}</p>
                    </div>
                </div>
                ${pesanan.catatan_harga ? `<div class="mt-3"><p class="text-muted small mb-1">Catatan Admin</p><p class="fw-bold">${escapeHtml(pesanan.catatan_harga)}</p></div>` : ''}
                ${renderDesignDetail(pesanan)}
            `;

            document.getElementById('detailContent').innerHTML = content;
        }
    </script>

<?php

    include '../../assets/layout/admin/footer.php';

?>
