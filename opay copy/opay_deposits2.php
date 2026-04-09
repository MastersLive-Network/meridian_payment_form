<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Opay Deposits</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .modal-bg {
            background: rgba(0,0,0,.6);
        }
        pre {
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto bg-white rounded shadow p-4">

    <h1 class="text-2xl font-bold mb-4">Opay Deposit Records</h1>

    <!-- Column Toggle -->
    <div class="mb-4 flex flex-wrap gap-3 text-sm">
        <label><input type="checkbox" class="toggle-col" data-col="payment_id" checked> Payment ID</label>
        <label><input type="checkbox" class="toggle-col" data-col="account_id" checked> Account ID</label>
        <label><input type="checkbox" class="toggle-col" data-col="amount" checked> Amount</label>
        <label><input type="checkbox" class="toggle-col" data-col="opay_status" checked> Opay Status</label>
        <label><input type="checkbox" class="toggle-col" data-col="status" checked> Status</label>
        <label><input type="checkbox" class="toggle-col" data-col="date_created" checked> Date</label>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2 col-payment_id">Payment ID</th>
                    <th class="border p-2 col-account_id">Account ID</th>
                    <th class="border p-2 col-amount">Amount</th>
                    <th class="border p-2 col-opay_status">Opay Status</th>
                    <th class="border p-2 col-status">Status</th>
                    <th class="border p-2 col-date_created">Date</th>
                    <th class="border p-2">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr class="hover:bg-gray-50">
                    <td class="border p-2"><?= $row['id'] ?></td>
                    <td class="border p-2 col-payment_id"><?= htmlspecialchars($row['payment_id']) ?></td>
                    <td class="border p-2 col-account_id"><?= htmlspecialchars($row['account_id']) ?></td>
                    <td class="border p-2 col-amount"><?= number_format($row['amount'], 2) ?></td>
                    <td class="border p-2 col-opay_status"><?= htmlspecialchars($row['opay_res']) ?></td>
                    <td class="border p-2 col-status"><?= htmlspecialchars($row['status']) ?></td>
                    <td class="border p-2 col-date_created"><?= $row['date_created'] ?></td>
                    <td class="border p-2 space-x-2">
                        <button 
                            class="view-btn text-blue-600 underline"
                            data-row='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                        >View</button>

                        <button 
                            class="delete-btn text-red-600 underline"
                            data-id="<?= $row['id'] ?>"
                        >Delete</button>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="viewModal" class="hidden fixed inset-0 modal-bg flex items-center justify-center">
    <div class="bg-white w-11/12 md:w-2/3 p-4 rounded shadow-lg max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-3">Full Record</h2>

        <div class="space-y-3 text-sm">
            <div><strong>Payload</strong><pre id="payload"></pre></div>
            <div><strong>Response</strong><pre id="response"></pre></div>
            <div><strong>Callback</strong><pre id="callback"></pre></div>
            <div><strong>Query Status</strong><pre id="query_status"></pre></div>
            <div><strong>Notify Prizma Req</strong><pre id="notify_prizma_req"></pre></div>
        </div>

        <div class="text-right mt-4">
            <button id="closeModal" class="bg-gray-700 text-white px-4 py-2 rounded">Close</button>
        </div>
    </div>
</div>

<script>
/* Column toggle */
$('.toggle-col').on('change', function () {
    let col = $(this).data('col');
    $('.col-' + col).toggle(this.checked);
});

/* View modal */
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

    $('#viewModal').removeClass('hidden');
});

/* Close modal */
$('#closeModal').on('click', function () {
    $('#viewModal').addClass('hidden');
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
</script>

</body>
</html>
