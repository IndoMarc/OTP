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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

$url_balance = "https://api.jasaotp.id/v1/balance.php?api_key=" . $api_key;
$res_balance = fetch_api($url_balance);
$data_balance = json_decode($res_balance, true);
$saldo = $data_balance['data']['saldo'] ?? 0;

$notif = "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SHORTCUT OTP</title>
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --dark: #212529;
            --light: #f8f9fa;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            background-color: #f0f2f5; 
            margin: 0; 
            padding: 10px; 
            color: #333; 
        }

        .container { 
            max-width: 600px; 
            margin: auto; 
            background: white; 
            padding: 15px; 
            border-radius: 16px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }

        .header { 
            text-align: center;
            border-bottom: 1px solid #eee; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }

        h2 { font-size: 1.4em; margin: 10px 0; color: var(--dark); }

        .saldo-box { 
            background: linear-gradient(135deg, #007bff, #0056b3);
            padding: 15px; 
            border-radius: 12px; 
            color: white; 
            font-weight: bold; 
            font-size: 1.2em;
            text-align: center;
        }

        #notification-area {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: 90%;
            max-width: 400px;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 0.95em;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-danger { background: #ff4d4d; color: white; }
        .alert-success { background: var(--success); color: white; }

        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn { 
            display: inline-block;
            width: 100%;
            padding: 14px; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 0.9em;
            text-align: center;
            transition: 0.2s;
            box-sizing: border-box;
        }

        .btn-beli { background-color: var(--success); color: white; }
        .btn-clear { background-color: #6e7881; color: white; }
        .btn-cek { background-color: var(--primary); color: white; }
        .btn-cancel { background-color: #eee; color: #555; }
        .btn:active { opacity: 0.8; transform: scale(0.98); }
        .btn:disabled { background-color: #ddd; color: #999; cursor: not-allowed; }

        .order-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .number-display {
            font-size: 1.5em;
            font-weight: 800;
            color: var(--primary);
            display: block;
            margin: 10px 0;
            text-align: center;
            background: #f0f7ff;
            padding: 10px;
            border-radius: 8px;
            border: 1px dashed var(--primary);
        }

        .grid-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .json-res { 
            background: #1e1e1e; 
            color: #00ff00; 
            padding: 12px; 
            border-radius: 8px; 
            font-family: 'Courier New', monospace; 
            font-size: 0.9em; 
            margin: 15px 0;
            word-break: break-all;
        }

        .timer-text { 
            font-size: 0.75em; 
            color: var(--danger); 
            margin-top: 5px; 
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<div id="notification-area">
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
            echo "<div class='alert alert-success' id='auto-close'>Berhasil membeli nomor!</div>";
        } else {
            echo "<div class='alert alert-danger' id='auto-close'>Gagal: " . ($data_order['message'] ?? 'Error API') . "</div>";
        }
    }

    if (isset($_POST['cancel_nomor'])) {
        $target_id = $_POST['target_id'];
        $url_cancel = "https://api.jasaotp.id/v1/cancel.php?api_key=" . $api_key . "&id=" . $target_id;
        $res_cancel = fetch_api($url_cancel);
        $data_cancel = json_decode($res_cancel, true);

        if (isset($data_cancel['success']) && $data_cancel['success'] == true) {
            foreach ($_SESSION['orders'] as $key => $order) {
                if ($order['order_id'] == $target_id) {
                    unset($_SESSION['orders'][$key]);
                    $_SESSION['orders'] = array_values($_SESSION['orders']);
                    break;
                }
            }
            echo "<div class='alert alert-success' id='auto-close'>Nomor #$target_id dicancel & dihapus.</div>";
        } else {
            echo "<div class='alert alert-danger' id='auto-close'>Gagal Cancel: " . ($data_cancel['message'] ?? 'Error') . "</div>";
        }
    }
    ?>
</div>

<div class="container">
    <div class="header">
        <h2>~ NOMOR WA BY M.H.R ~</h2>
        <div class="saldo-box">
            <span style="font-size: 0.7em; display: block; font-weight: normal; opacity: 0.9;">Saldo Tersedia</span>
            Rp. <?php echo number_format($saldo, 0, ',', '.'); ?>
        </div>
    </div>

    <div class="action-buttons">
        <form method="post"><button type="submit" name="beli" class="btn btn-beli">Beli Nomor</button></form>
        <form method="post">
            <button type="submit" name="clear_history" class="btn btn-clear" <?php echo empty($_SESSION['orders']) ? 'disabled' : ''; ?>>Hapus Riwayat</button>
        </form>
    </div>

    <?php
    if (isset($_POST['cek_otp'])) {
        $url_sms = "https://api.jasaotp.id/v1/sms.php?api_key=" . $api_key . "&id=" . $_POST['target_id'];
        $res_sms = fetch_api($url_sms);
        echo "<strong>Hasil OTP #".$_POST['target_id'].":</strong><div class='json-res'>" . htmlspecialchars($res_sms) . "</div>";
    }

    if (!empty($_SESSION['orders'])): 
        foreach ($_SESSION['orders'] as $index => $order): 
            $remaining = 120 - (time() - $order['timestamp']);
            $is_disabled = ($remaining > 0) ? 'disabled' : '';
    ?>
        <div class="order-card">
            <div style="display:flex; justify-content: space-between; font-size: 0.8em; color: #888; margin-bottom: 10px;">
                <span>ID: #<?php echo $order['order_id']; ?></span>
                <span><?php echo date('H:i', $order['timestamp']); ?></span>
            </div>
            
            <div class="number-display" onclick="copyToClipboard('<?php echo $order['number']; ?>')">
                <?php echo $order['number']; ?>
            </div>

            <div class="grid-actions">
                <form method="post">
                    <input type="hidden" name="target_id" value="<?php echo $order['order_id']; ?>">
                    <button type="submit" name="cek_otp" class="btn btn-cek">Cek OTP</button>
                </form>

                <form method="post">
                    <input type="hidden" name="target_id" value="<?php echo $order['order_id']; ?>">
                    <button type="submit" name="cancel_nomor" id="btnCancel_<?php echo $index; ?>" class="btn btn-cancel" <?php echo $is_disabled; ?>>Cancel</button>
                    <div id="timer_<?php echo $index; ?>" class="timer-text"></div>
                </form>
            </div>
        </div>

        <script>
            (function() {
                var timeLeft = <?php echo max(0, $remaining); ?>;
                var timerElem = document.getElementById("timer_<?php echo $index; ?>");
                var btn = document.getElementById("btnCancel_<?php echo $index; ?>");
                
                function updateTimer() {
                    if (timeLeft <= 0) {
                        timerElem.innerHTML = "Siap Cancel";
                        timerElem.style.color = "var(--success)";
                        btn.disabled = false;
                    } else {
                        var min = Math.floor(timeLeft / 60);
                        var sec = timeLeft % 60;
                        timerElem.innerHTML = "Tunggu " + min + ":" + (sec < 10 ? "0" : "") + sec;
                        timeLeft--;
                        setTimeout(updateTimer, 1000);
                    }
                }
                updateTimer();
            })();
        </script>
    <?php 
        endforeach; 
    endif; 
    ?>
</div>

<script>
// Fungsi durasi notifikasi 2 detik
document.querySelectorAll('#auto-close').forEach(el => {
    setTimeout(() => {
        el.style.transition = "0.5s";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
    }, 2000);
});

function copyToClipboard(text) {
    var input = document.createElement('input');
    input.setAttribute('value', text);
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    
    // Toast notif copy 2 detik
    const toast = document.createElement('div');
    toast.className = "alert alert-success";
    toast.style = "position:fixed; bottom:20px; left:50%; transform:translateX(-50%); z-index:9999;";
    toast.innerText = "Nomor " + text + " disalin!";
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = "0.5s";
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 500);
    }, 2000);
}
</script>

</body>
</html>
