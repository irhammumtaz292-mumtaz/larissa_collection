<?php
    $popup = $popup ?? false;
    $warnaPopup = $warnaPopup ?? 'secondary';
    $statusPopup = $statusPopup ?? '';
    $iconPopup = $iconPopup ?? 'info-circle';
    $laman = $laman ?? '';
    $popupEksekusi = $popupEksekusi ?? '';
    $fileLaman = $fileLaman ?? '';
?>

<!-- Popup -->
<section aria-label="Popup">
    <?php if ($popup == true) :?>
        <div class="modal fade show" style="display:block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-<?= $warnaPopup ?>">
                        <h5 class="modal-title"><?= $statusPopup ?></h5>
                    </div>
                    <div class="modal-body">
                        <i class="bi bi-<?= $iconPopup ?> me-2"></i><?= $laman ?> <?= $statusPopup ?> <?= $popupEksekusi ?>!
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-<?= $warnaPopup ?>" data-bs-dismiss="modal" onclick="location.href='<?= $fileLaman ?>'">OK</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif;?>
</section>
<!-- .Popup -->
