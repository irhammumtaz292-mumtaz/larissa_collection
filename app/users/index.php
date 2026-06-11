<?php

    session_start();

    $title = 'Landing Page Konveksi';
    $halmut = './';
    $halpem = 'pesanan.php';

    include '../../assets/layout/users/header.php';

    $data_produk = select("SELECT * FROM produk ORDER BY nama_produk ASC");

?>

    <!-- MAIN CONTENT -->
    <main>

        <!-- HERO -->
        <header class="masthead">
            <div class="container">
                <div class="masthead-subheading">Welcome To Larisa Collection</div>
                <div class="masthead-heading text-uppercase text-white-outline-orange">Jasa Konveksi Berkualitas</div>
                <a class="btn btn-primary btn-xl text-uppercase" href="#katalog">Lihat Katalog</a>
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
        <section id="katalog" class="page-section bg-light py-5">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Katalog Produk</h2>
                    <h3 class="section-subheading text-muted">Temukan produk konveksi terbaik kami dengan bahan premium.</h3>
                </div>

                <?php if (count($data_produk) > 6) : ?>
                    <?php $catalogSlides = array_chunk($data_produk, 6); ?>
                    <div id="catalogCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-wrap="false">
                        <div class="carousel-inner">
                            <?php foreach ($catalogSlides as $index => $produkChunk) : ?>
                                <div class="carousel-item<?= $index === 0 ? ' active' : '' ?>">
                                    <div class="row g-4">
                                        <?php foreach ($produkChunk as $produk) : ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="card catalog-product-card h-100 shadow-sm">
                                                    <?php if (!empty($produk['gambar_produk'])) : ?>
                                                        <div class="product-img-wrapper">
                                                            <img src="../../assets/img/produk/<?= htmlspecialchars($produk['gambar_produk']) ?>" class="product-card-img" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
                                                        <p class="card-text"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                                                    </div>
                                                    <div class="card-footer bg-transparent border-0 pt-0">
                                                        <a href="pesanan.php?produk=<?= urlencode($produk['nama_produk']) ?>" class="btn btn-orange btn-sm w-100 rounded-pill">Pesan Sekarang</a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($catalogSlides) > 1) : ?>
                            <div class="carousel-arrow-group">
                                <button type="button" class="btn btn-orange carousel-arrow-button" data-bs-target="#catalogCarousel" data-bs-slide="prev" aria-label="Sebelumnya">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-orange carousel-arrow-button" data-bs-target="#catalogCarousel" data-bs-slide="next" aria-label="Berikutnya">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="row g-4">
                        <?php foreach ($data_produk as $produk) : ?>
                            <div class="col-md-4">
                                <div class="card catalog-product-card h-100 shadow-sm">
                                    <?php if (!empty($produk['gambar_produk'])) : ?>
                                        <div class="product-img-wrapper">
                                            <img src="../../assets/img/produk/<?= htmlspecialchars($produk['gambar_produk']) ?>" class="product-card-img" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <a href="<?= $halpem ?>?produk=<?= urlencode($produk['nama_produk']) ?>" class="btn btn-orange btn-sm w-100 rounded-pill">Pesan Sekarang</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                                <h4>2012</h4>
                                <h4 class="subheading">Awal Berdiri</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Memulai usaha konveksi dengan fokus kualitas dan kepuasan pelanggan.</p></div>
                        </div>
                    </li>
                    <li class="timeline-inverted">
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/2.jpg" alt="..." /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>2026</h4>
                                <h4 class="subheading">Pengembangan Layanan</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Memperluas layanan ke desain custom dan produksi massal.</p></div>
                        </div>
                    </li>
                    <li>
                        <div class="timeline-image"><img class="rounded-circle img-fluid" src="assets/img/about/3.jpg" alt="..." /></div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h4>2026</h4>
                                <h4 class="subheading">Solusi Lengkap</h4>
                            </div>
                            <div class="timeline-body"><p class="text-muted">Menjadi pilihan pelanggan untuk produksi seragam, kaos, dan merch.</p></div>
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

        <!-- KEUNGGULAN -->
        <section id="keunggulan" class="page-section py-5">
            <div class="container text-center">
                <h2 class="section-heading text-uppercase">Kenapa Pilih Kami?</h2>
                <h3 class="section-subheading text-muted">Layanan konveksi kami dibuat untuk memenuhi seluruh kebutuhan produksi Anda.</h3>
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="fw-bold">Bahan Premium</h5>
                        <p>Kualitas terjamin dan nyaman digunakan.</p>
                    </div>
                    <div class="col-md-4">
                        <h5 class="fw-bold">Harga Terjangkau</h5>
                        <p>Cocok untuk komunitas dan perusahaan.</p>
                    </div>
                    <div class="col-md-4">
                        <h5 class="fw-bold">Pengerjaan Cepat</h5>
                        <p>Estimasi produksi 3–7 hari kerja.</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

<?php

    include '../../assets/layout/users/footer.php';

?>
