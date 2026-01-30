<?php
session_start();

$api_key = "a39837fb4273c5c508fd49a0f10e990e";

if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

if (isset($_POST['clear_history'])) {
    $_SESSION['orders'] = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function fetch_api($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

$url_balance = "https://api.jasaotp.id/v1/balance.php?api_key=" . $api_key;
$res_balance = fetch_api($url_balance);
$data_balance = json_decode($res_balance, true);
$saldo = $data_balance['data']['saldo'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SHORTCUT OTP</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa; 
            margin: 0; 
            padding: 20px; 
            color: #333; 
            font-size: 1.1em; 
        }
        .container { 
            max-width: 900px; 
            margin: auto; 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        h2 { font-size: 1.6em; margin: 0; }
        .saldo-box { 
            background: #e7f3ff; 
            padding: 12px 24px; 
            border-radius: 8px; 
            color: #007bff; 
            font-weight: bold; 
            font-size: 1.2em; 
        }
        .btn { 
            padding: 12px 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            transition: 0.3s; 
            font-size: 0.95em;
        }
        .btn-beli { background-color: #28a745; color: white; }
        .btn-beli:hover { background-color: #218838; }
        .btn-clear { background-color: #dc3545; color: white; }
        .btn-clear:hover { background-color: #c82333; }
        .btn-cek { background-color: #007bff; color: white; }
        .btn-cek:hover { background-color: #0069d9; }
        .btn-cancel { background-color: #6c757d; color: white; }
        .btn-cancel:hover:not(:disabled) { background-color: #5a6268; }
        .btn:disabled { background-color: #ccc; cursor: not-allowed; }
        
        .json-res { 
            background: #272822; 
            color: #f8f8f2; 
            padding: 15px; 
            border-radius: 8px; 
            overflow-x: auto; 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 1.5em; 
            margin-bottom: 20px; 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            table-layout: auto;
        }
        th { 
            background-color: #f2f2f2; 
            color: #555; 
            text-align: center; 
            padding: 12px 8px; 
            font-size: 1em;
        }
        td { 
            padding: 12px 8px; 
            border-bottom: 1px solid #eee; 
            text-align: center;
        }
        
        /* Mempersempit kolom */
        th:nth-child(1), td:nth-child(1) { width: 15%; }
        th:nth-child(2), td:nth-child(2) { width: 30%; }
        th:nth-child(3), td:nth-child(3) { width: 25%; }
        th:nth-child(4), td:nth-child(4) { width: 30%; }

        .num-copy { 
            color: #007bff; 
            text-decoration: underline; 
            cursor: pointer; 
            font-family: monospace; 
            font-size: 1.2em; 
        }
        .timer-text { 
            font-size: 0.9em; 
            color: #d9534f; 
            margin-top: 6px; 
            font-weight: bold; 
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Shortcut OTP by M.H.R</h2>
        <div class="saldo-box">Saldo : RP <?php echo number_format($saldo, 0, ',', '.'); ?></div>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <form method="post"><button type="submit" name="beli" class="btn btn-beli">Beli Nomor</button></form>
        <?php if (!empty($_SESSION['orders'])): ?>
            <form method="post"><button type="submit" name="clear_history" class="btn btn-clear">Bersihkan Riwayat</button></form>
        <?php endif; ?>
    </div>

    <?php
    if (isset($_POST['beli'])) {
        $url_order = "https://api.jasaotp.id/v1/order.php?api_key=" . $api_key . "&negara=6&layanan=wa&operator=any";
        $res_order = fetch_api($url_order);
        $data_order = json_decode($res_order, true);

        if (isset($data_order['success']) && $data_order['success'] == true) {
            $raw_number = ltrim($data_order['data']['number'], '+');
            $formatted_number = (substr($raw_number, 0, 2) === '62') ? '0' . substr($raw_number, 2) : $raw_number;
            $new_order = ['order_id' => $data_order['data']['order_id'], 'number' => $formatted_number, 'timestamp' => time()];
            array_unshift($_SESSION['orders'], $new_order);
        } else {
            echo "<div style='color:red; margin-bottom:10px;'>Gagal: " . ($data_order['message'] ?? 'Koneksi error') . "</div>";
        }
    }

    if (isset($_POST['cek_otp'])) {
        $url_sms = "https://api.jasaotp.id/v1/sms.php?api_key=" . $api_key . "&id=" . $_POST['target_id'];
        $res_sms = fetch_api($url_sms);
        echo "<strong>Pesan OTP ( ID ".$_POST['target_id']." ) :</strong><div class='json-res'>" . $res_sms . "</div>";
    }

    if (isset($_POST['cancel_nomor'])) {
        $url_cancel = "https://api.jasaotp.id/v1/cancel.php?api_key=" . $api_key . "&id=" . $_POST['target_id'];
        $res_cancel = fetch_api($url_cancel);
        echo "<strong>Pesan Cancel ( ID ".$_POST['target_id']." ) :</strong><div class='json-res'>" . $res_cancel . "</div>";
    }

    if (!empty($_SESSION['orders'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Nomor</th>
                    <th>OTP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['orders'] as $index => $order): 
                    $remaining = 120 - (time() - $order['timestamp']);
                    $is_disabled = ($remaining > 0) ? 'disabled' : '';
                ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><span class="num-copy" onclick="copyToClipboard('<?php echo $order['number']; ?>')"><strong><?php echo $order['number']; ?></strong></span></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="target_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="cek_otp" class="btn btn-cek">Cek OTP</button>
                            </form>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="target_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" name="cancel_nomor" id="btnCancel_<?php echo $index; ?>" class="btn btn-cancel" <?php echo $is_disabled; ?>>Cancel</button>
                                <div id="timer_<?php echo $index; ?>" class="timer-text"></div>
                            </form>
                        </td>
                    </tr>
                    <script>
                        (function() {
                            var timeLeft = <?php echo max(0, $remaining); ?>;
                            var timerElem = document.getElementById("timer_<?php echo $index; ?>");
                            var btn = document.getElementById("btnCancel_<?php echo $index; ?>");
                            if (timeLeft > 0) {
                                var countdown = setInterval(function() {
                                    if (timeLeft <= 0) {
                                        clearInterval(countdown);
                                        timerElem.innerHTML = "Siap Cancel";
                                        btn.disabled = false;
                                    } else {
                                        var min = Math.floor(timeLeft / 60);
                                        var sec = timeLeft % 60;
                                        timerElem.innerHTML = "Tunggu " + min + ":" + (sec < 10 ? "0" : "") + sec;
                                        timeLeft--;
                                    }
                                }, 1000);
                            } else {
                                timerElem.innerHTML = "Siap Cancel";
                            }
                        })();
                    </script>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    var input = document.createElement('input');
    input.setAttribute('value', text);
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    alert('Nomor ' + text + ' berhasil disalin !');
}
</script>

</body>
</html>