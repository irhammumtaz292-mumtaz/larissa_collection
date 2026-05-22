<?php

    session_start();

    // // membatasi halaman sebelum login
    // if (!isset($_SESSION["login"])) {
    //     echo "<script>
    //             alert('AKSES DI TOLAK!');
    //             document.location.href = '../../.';
    //         </script>";
    //     exit;
    // }

    // // membatasi halaman sesuai user login
    // if ($_SESSION["role"] != 'Admin') {
    //     echo "<script>
    //         alert('AKSES DI TOLAK!');
    //         document.location.href = '../../.';
    //         </script>";
    //     exit;
    // }

    $laman = 'Dashboard';
    include '../../assets/layout/admin/header.php';

?>

    <!-- Dashboard Main -->
    <main class="overflow-auto" style="flex:1;">
        <!-- Dashboard Cards -->
        <section class="row g-3 mb-4" aria-label="Dashboard Cards">
            <article class="col-md-3">
            <div class="card text-bg-primary shadow-sm mb-3">
                <div class="card-body">
                <h6 class="card-subtitle mb-2 text-white">Users</h6>
                <h4 class="card-title text-white">1,234</h4>
                </div>
            </div>
            </article>
            <article class="col-md-3">
            <div class="card text-bg-success shadow-sm mb-3">
                <div class="card-body">
                <h6 class="card-subtitle mb-2 text-white">Sales</h6>
                <h4 class="card-title text-white">$12,345</h4>
                </div>
            </div>
            </article>
            <article class="col-md-3">
            <div class="card text-bg-warning shadow-sm mb-3">
                <div class="card-body">
                <h6 class="card-subtitle mb-2 text-dark">Orders</h6>
                <h4 class="card-title text-dark">567</h4>
                </div>
            </div>
            </article>
            <article class="col-md-3">
            <div class="card text-bg-danger shadow-sm mb-3">
                <div class="card-body">
                <h6 class="card-subtitle mb-2 text-white">Errors</h6>
                <h4 class="card-title text-white">0</h4>
                </div>
            </div>
            </article>
        </section>

        <!-- Actions -->
        <section class="mb-4" aria-label="Dashboard Actions">
            <h5>Actions</h5>
            <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add User</button>
            <button class="btn btn-secondary"><i class="bi bi-file-earmark-text me-1"></i>Generate Report</button>
            <button class="btn btn-success"><i class="bi bi-download me-1"></i>Export</button>
            <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete</button>
            </div>
        </section>

        <!-- Table Data Dummy -->
        <section aria-label="Recent Users Table">
            <h5>Recent Users</h5>
            <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>John Doe</td>
                    <td>john@example.com</td>
                    <td>Admin</td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td>
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Jane Smith</td>
                    <td>jane@example.com</td>
                    <td>User</td>
                    <td><span class="badge bg-secondary">Inactive</span></td>
                    <td>
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>
        </section>
    </main>

<?php

    include '../../assets/layout/admin/footer.php';

?>