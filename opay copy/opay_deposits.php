<?php
require_once "../db/connect.php";

$query = "
SELECT 
    id, 
    payment_id, 
    account_id, 
    amount, 
    payload, 
    response, 
    callback, 
    query_status, 
    notify_prizma_req, 
    opay_res,
    status, 
    date_created 
FROM opay_deposit 
ORDER BY id DESC
";

$result = mysqli_query($con, $query);


// Metrics
$statsQuery = "
    SELECT 
        COUNT(*) AS total_rows,
        SUM(amount) AS total_amount
    FROM opay_deposit
";

$statsRes = mysqli_query($con, $statsQuery);

$total_rows = 0;
$total_amount = 0;

if ($statsRes && mysqli_num_rows($statsRes) > 0) {
    $stats = mysqli_fetch_assoc($statsRes);
    $total_rows = (int) $stats['total_rows'];
    $total_amount = (float) $stats['total_amount'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Opay Deposits</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables (Bootstrap 5) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>


    <style>
        @font-face {
            font-family: "koo-light";
            src: url(../fonts/Montserrat-Light.ttf);
        }

        *{
            font-family: "koo-light" !important;
            font-size: 14px !important;
            box-sizing: border-box;
        }

        h3, h5, h2{
            font-size: 30px !important
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        th{
            font-weight: bold !important;
        }

        td, th{
            padding: 10px !important
        }

        .dataTables_wrapper .dataTables_filter input {
            font-size: 13px;
            padding: 4px 6px;
        }

        .dataTables_wrapper .dataTables_length select {
            font-size: 13px;
        }

    </style>
</head>
<body class="bg-light">

<div class="container mt-4"><br /><br /><br /><br />
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow p-4">
                <small class="text-muted">Total Payment Records</small>
                <h2 class="bold"><?= number_format($total_rows) ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow p-4">
                <small class="text-muted">Total Payment Records</small>
                <h2 class="bold">&#8358;<?= number_format($total_amount,2) ?></h2>
            </div>
        </div>
    </div>

    <div class="card shadow mt-4 p-4">
        <div class="card-body">

            <h3 class="mb-4">Opay Deposit Records</h3>

            <!-- Column Toggle -->
            <div class="mb-3 d-flex flex-wrap gap-3 small">
                <label><input type="checkbox" class="toggle-col" data-col="payment_id" checked> Payment ID</label>
                <label><input type="checkbox" class="toggle-col" data-col="account_id" checked> Account ID</label>
                <label><input type="checkbox" class="toggle-col" data-col="amount" checked> Amount</label>
                <label><input type="checkbox" class="toggle-col" data-col="opay_status" checked> Opay Status</label>
                <label><input type="checkbox" class="toggle-col" data-col="status" checked> Status</label>
                <label><input type="checkbox" class="toggle-col" data-col="date_created" checked> Date</label>
            </div>

            <div class="table-responsive">
                <table id="opayTable" class="table table-bordered table-striped table-hover table-sm">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th>
                            <th class="col-payment_id">Payment ID</th>
                            <th class="col-account_id">Account ID</th>
                            <th class="col-amount">Amount</th>
                            <th class="col-opay_status">Opay Status</th>
                            <th class="col-status">Status</th>
                            <th class="col-date_created">Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td class="col-payment_id"><?= htmlspecialchars($row['payment_id']) ?></td>
                            <td class="col-account_id"><?= htmlspecialchars($row['account_id']) ?></td>
                            <td class="col-amount"><?= number_format($row['amount'], 2) ?></td>
                            <td class="col-opay_status"><?= htmlspecialchars($row['opay_res']) ?></td>
                            <td class="col-status"><?= htmlspecialchars($row['status']) ?></td>
                            <td class="col-date_created"><?= $row['date_created'] ?></td>
                            <td>
                                <button
                                    class="btn btn-link p-0 view-btn"
                                    data-row='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                >View</button>
                                |
                                <button
                                    class="btn btn-link text-danger p-0 delete-btn"
                                    data-id="<?= $row['id'] ?>"
                                >Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Full Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <div class="mb-3"><strong>Payload</strong><pre id="payload"></pre></div>
                <div class="mb-3"><strong>Response</strong><pre id="response"></pre></div>
                <div class="mb-3"><strong>Callback</strong><pre id="callback"></pre></div>
                <div class="mb-3"><strong>Query Status</strong><pre id="query_status"></pre></div>
                <div class="mb-3"><strong>Notify Prizma Req</strong><pre id="notify_prizma_req"></pre></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    /* DataTable init */
    const table = $('#opayTable').DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']], // ID desc
        responsive: true,
        stateSave: true
    });

    /* Column toggle (DataTable-safe) */
    $('.toggle-col').on('change', function () {
        let colName = $(this).data('col');
        let colIndex = $('.col-' + colName).first().index();
        table.column(colIndex).visible(this.checked);
    });

    /* View modal */
    const modal = new bootstrap.Modal(document.getElementById('viewModal'));

    $('.view-btn').on('click', function () {
        let data = $(this).data('row');

        function pretty(val) {
            try {
                return JSON.stringify(JSON.parse(val), null, 2);
            } catch {
                return val;
            }
        }

        $('#payload').text(pretty(data.payload));
        $('#response').text(pretty(data.response));
        $('#callback').text(pretty(data.callback));
        $('#query_status').text(pretty(data.query_status));
        $('#notify_prizma_req').text(pretty(data.notify_prizma_req));

        modal.show();
    });

    /* Delete */
    $('.delete-btn').on('click', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Enter Password',
            input: 'password',
            inputPlaceholder: 'Password',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value !== '239883920') {
                    Swal.fire('Error', 'Incorrect password', 'error');
                    return;
                }

                $.post('opay_delete.php', { id }, function (res) {
                    if (res === 'success') {
                        Swal.fire('Deleted', 'Record removed', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error', 'Delete failed', 'error');
                    }
                });
            }
        });
    });

});
</script>


</body>
</html>
