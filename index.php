<?php

$title = 'Landing Page Konveksi';
$halmut = './';
$hallog = 'login.php';

include 'assets/layout/katalog/header.php';

$data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");

$orderConfirmation = null;
$id_pesanan = 0;
if (isset($_GET['pesanan_success']) && $_GET['pesanan_success'] == 1 && !empty($_GET['id_pesanan'])) {
    $id_pesanan = intval($_GET['id_pesanan']);
    $orderConfirmation = select("SELECT t.*, p.jumlah_beli, p.ukuran, p.harga, p.harga_dp FROM transaksi t
            JOIN pesanan p ON t.id_pesanan = p.id_pesanan
            WHERE t.id_pesanan = $id_pesanan
            ORDER BY t.id_transaksi DESC LIMIT 1");
    $orderConfirmation = !empty($orderConfirmation) ? $orderConfirmation[0] : null;
}
?>

<!-- MAIN CONTENT -->
<main>

    <?php if (!empty($orderConfirmation)) : ?>
        <section class="page-section py-4 bg-success text-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-10">
                        <div class="card bg-dark border-0 shadow-lg rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                                    <div>
                                        <h2 class="h4 text-white mb-1">Pesanan Berhasil Dibuat</h2>
                                        <p class="text-white-50 mb-0">ID Pesanan: <strong><?= htmlspecialchars($id_pesanan) ?></strong></p>
                                    </div>
                                    <span class="badge bg-warning text-dark">Status: <?= htmlspecialchars($orderConfirmation['status_pembayaran']) ?></span>
                                </div>

                                <div class="row g-3 align-items-center mt-4">
                                    <div class="col-md-4">
                                        <div class="bg-white rounded-4 p-3 text-center">
                                            <p class="text-muted small mb-1">Metode Pembayaran</p>
                                            <h5 class="mb-0 text-uppercase"><?= htmlspecialchars(str_replace('_', ' ', $orderConfirmation['metode_pembayaran'])) ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-white rounded-4 p-3 text-center">
                                            <p class="text-muted small mb-1">Jumlah Bayar</p>
                                            <h5 class="mb-0">Rp <?= number_format($orderConfirmation['jumlah_bayar']) ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-white rounded-4 p-3 text-center">
                                            <p class="text-muted small mb-1">Total Item</p>
                                            <h5 class="mb-0"><?= htmlspecialchars($orderConfirmation['jumlah_beli']) ?> pcs</h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mt-4">
                                    <div class="col-lg-6">
                                        <div class="card border-0 rounded-4 shadow-sm p-4">
                                            <?php if ($orderConfirmation['metode_pembayaran'] === 'qris') : ?>
                                                <div class="text-center">
                                                    <div class="mx-auto rounded-3 border" style="width:220px; height:220px; background-image: repeating-linear-gradient(0deg, #000 0 6px, transparent 6px 12px), repeating-linear-gradient(90deg, #000 0 6px, transparent 6px 12px); background-size: 24px 24px;"></div>
                                                    <p class="text-muted small mt-3 mb-0">Scan QRIS di atas untuk menyelesaikan pembayaran.</p>
                                                </div>
                                            <?php else : ?>
                                                <div class="bg-light rounded-4 p-4 text-center">
                                                    <h5 class="fw-semibold mb-2">Virtual Account</h5>
                                                    <p class="text-muted mb-1">Bank</p>
                                                    <p class="fw-bold mb-2">BCA</p>
                                                    <p class="text-muted mb-1">Nomor VA</p>
                                                    <p class="fs-5 fw-semibold mb-0">1234 5678 9012</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
                                            <h5 class="fw-semibold">Petunjuk Pembayaran</h5>
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Pastikan pembayaran selesai dalam 24 jam.</li>
                                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Simpan screenshot atau bukti pembayaran.</li>
                                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Transaksi akan diproses setelah pembayaran terverifikasi.</li>
                                            </ul>
                                            <a href="index.php#contact" class="btn btn-warning btn-sm rounded-pill mt-3">Butuh Bantuan?</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- HERO -->
    <header class="masthead">
        <div class="container">
            <div class="masthead-subheading text-white-outline-orange">Welcome To Larisa Collection</div>
            <div class="masthead-heading text-uppercase text-white-outline-orange">Jasa Konveksi Berkualitas</div>
            <a class="btn btn-primary btn-xl text-uppercase" href="#portfolio">Lihat Katalog</a>
        </div>
    </header>

    <!-- SERVICES -->
    <section class="page-section" id="services">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Layanan Kami</h2>
                <h3 class="section-subheading text-muted">Desain siap pakai dan custom order untuk semua kebutuhan.</h3>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-tshirt fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">Pakaian Custom</h4>
                    <p class="text-muted">Kaos, hoodie, jaket, dan seragam sesuai kebutuhan Anda.</p>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-palette fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">Desain Kreatif</h4>
                    <p class="text-muted">Tim desain siap membantu dari konsep sampai produksi.</p>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-truck fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">Pengiriman Cepat</h4>
                    <p class="text-muted">Proses produksi cepat dan pengiriman dapat diandalkan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KATALOG -->
    <section class="page-section bg-light" id="katalog">

        <div class="container">

            <div class="text-center">
                <h2 class="section-heading text-uppercase">Katalog Produk</h2>
                <h3 class="section-subheading text-muted">Temukan produk konveksi terbaik kami dengan bahan premium.</h3>
            </div>

            <div class="row">
                <?php if (!empty($data_produk)) : ?>
                    <?php if (count($data_produk) > 6) : ?>
                        <?php $productSlides = array_chunk($data_produk, 6); ?>
                        <div id="productCarousel" class="carousel slide" data-bs-wrap="false" data-bs-interval="false" data-bs-touch="false" data-bs-keyboard="false">
                            <div class="carousel-inner">
                                <?php foreach ($productSlides as $slideIndex => $produkSlide) : ?>
                                    <div class="carousel-item<?= $slideIndex === 0 ? ' active' : '' ?>">
                                        <div class="row g-4">
                                            <?php foreach ($produkSlide as $produk) : ?>
                                                <div class="col-lg-4 col-sm-6">
                                                    <div class="card catalog-product-card h-100 shadow-sm">
                                                        <?php if (!empty($produk['gambar_produk'])) : ?>
                                                            <div class="product-img-wrapper">
                                                                <img src="assets/img/produk/<?= htmlspecialchars($produk['gambar_produk']) ?>" class="product-card-img" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
                                                            <p class="card-text text-muted"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                                                        </div>
                                                        <div class="card-footer bg-transparent border-0 pt-0">
                                                            <a href="<?= htmlspecialchars($hallog) ?>" class="btn btn-orange btn-sm w-100 rounded-pill">Pesan Sekarang</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($productSlides) > 1) : ?>
                                <div class="carousel-arrow-group">
                                    <button type="button" class="btn btn-orange carousel-arrow-button" data-bs-target="#productCarousel" data-bs-slide="prev" aria-label="Sebelumnya">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-orange carousel-arrow-button" data-bs-target="#productCarousel" data-bs-slide="next" aria-label="Berikutnya">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <?php foreach ($data_produk as $produk) : ?>
                            <div class="col-lg-4 col-sm-6 mb-4">
                                <div class="card catalog-product-card h-100 shadow-sm">
                                    <?php if (!empty($produk['gambar_produk'])) : ?>
                                        <div class="product-img-wrapper">
                                            <img src="assets/img/produk/<?= htmlspecialchars($produk['gambar_produk']) ?>" class="product-card-img" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
                                        <p class="card-text text-muted"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <a href="<?= htmlspecialchars($hallog) ?>" class="btn btn-orange btn-sm w-100 rounded-pill">Pesan Sekarang</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">Belum ada produk tersedia saat ini.</div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </section>

    <!-- ABOUT -->
    <section class="page-section" id="about">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Tentang Kami</h2>
                <h3 class="section-subheading text-muted">Melayani konveksi berkualitas dengan proses mudah dan hasil profesional.</h3>
            </div>
            <ul class="timeline">
                <li>
                    <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/1.jpg" alt="..." /></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4>2007</h4>
                            <h4 class="subheading">Awal Berdiri</h4>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">Memulai usaha konveksi dengan fokus kualitas dan kepuasan pelanggan.</p>
                        </div>
                    </div>
                </li>
                <li class="timeline-inverted">
                    <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/2.jpg" alt="..." /></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4>2012</h4>
                            <h4 class="subheading">Pengembangan Layanan</h4>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">Memperluas layanan ke desain custom dan produksi massal.</p>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/mitra/mitra.jpeg" alt="tim pembangun web bersama mitra" /></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4>2026</h4>
                            <h4 class="subheading">Solusi Lengkap</h4>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">Menjadi pilihan pelanggan untuk produksi seragam, kaos, dan merch.</p>
                        </div>
                    </div>
                </li>
                <li class="timeline-inverted">
                    <div class="timeline-image">
                        <h4>
                            Be Part
                            <br />
                            Of Our
                            <br />
                            Story!
                        </h4>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    <!-- CONTACT -->
    <section class="page-section" id="contact">
        <div class="container">

            <div class="text-center">
                <h2 class="section-heading text-uppercase">Hubungi Kami</h2>
                <h3 class="section-subheading text-muted">Kami akan membantu mengubah ide Anda menjadi produk nyata.</h3>
            </div>

            <div class="d-flex justify-content-center">
                <a href="https://wa.me/6289699031200?text=Halo%20Larisa%20Collection%2C%20saya%20ingin%20konsultasi%20order%20konveksi." target="_blank" rel="noopener noreferrer" class="btn whatsapp-contact-btn d-inline-flex align-items-center gap-2 rounded-pill">
                    <i class="bi bi-whatsapp fs-4"></i>
                    <span>Chat WhatsApp Kami</span>
                </a>
            </div>

        </div>
    </section>

</main>

<?php

include 'assets/layout/katalog/footer.php';

?>