<?php
	# Panggil database, buat baru apabila belum ada
	$sqlite = new sqlitedatabase('./sqlite.db');

	# Cek tabel, buat baru bila belum ada
	$cek_tabel = @$sqlite->query("SELECT nomor FROM shout");
	if (!$cek_tabel) {
		$sqlite->query("CREATE TABLE shout(
			nomor integer primary key unique,
			ip varchar,
			tanggal timestamp,
			shout varchar)");
	}

	# Jika user mengirim shout atau jQuery meminta shout
	if ((isset($_GET['kirim']) AND !empty($_GET['kirim'])) OR isset($_GET['minta'])) {

		# Jeda 1 detik (sangat berguna bila di localhost)
		sleep(1);

		# Jika user mengirim shout, masukkan kedalam database
		if (isset($_GET['kirim'])) {
			$shout = substr($_GET['kirim'], 0, 160);
			$ip = $_SERVER['REMOTE_ADDR'];
			$sqlite->query("INSERT INTO shout (ip, tanggal, shout) VALUES ('$ip', DATETIME('NOW'), '$shout')");
		}

		# Tampilkan shout
		$tabel = $sqlite->arrayquery("SELECT ip, tanggal, shout FROM shout ORDER BY tanggal DESC");
		foreach ($tabel as $li) {
			# Format tanggal agar mudah dimengerti
			$tanggal = date('Y-m-d', strtotime($li['tanggal']));
			echo '<li>'.$li['ip'].' : '.$li['shout'].' <span>['.$tanggal.']</span></li>';
		}
	}

	# Apabila tidak ada request ajax, tampilkan halaman normal
	else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Shoutbox dengan jQuery</title>
	<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
	<script type="text/javascript" src="jquery-1.3.2.min.js"></script>
</head>

<body>
	<h1>ShoutBox</h1>

	<ul id="shout_text"></ul>

	<form method="post" action="" id="form_shout">
		<label for="shout">Shout :</label>
		<input type="text" name="shout" id="shout" maxlength="160" />
		<input type="submit" name="shouting" value="Post!" />
	</form>

	<script type="text/javascript">
		$(function(){

			/* Fungsi yang bekerja apabila $.ajax dipanggil */
			$('#shout_text').ajaxStart(function(){

				/* Tampilkan loader sebelum $.ajax diproses */
				$(this).removeClass('error').addClass('loader');
			}).ajaxStop(function(){

				/* Hilangkan loader setelah $.ajax diproses */
				$(this).removeClass('loader');
			});

			/* Panggil fungsi update() saat pertama kali dibuka */
			update();

			/* Panggil fungsi update() setiap 10 detik */
			setInterval('update()', 10000);

			/* Jika user mengirim shout maka : */
			$('#form_shout').submit(function(){

				/* Buat variabel yang berisi shout */
				var shout = $('#shout').val();

				/* Cek apakah shout kurang dari dua karakter, lanjutkan jika lebih dari dua karakter */
				if (shout.length < 2) {
					$('#shout').addClass('error');
					return false;
				}
				else {
					$.ajax({
						type: 'get',
						data: 'kirim=' +shout,
						success: function(data) {
							$('#shout_text').empty().append(data);
						}
					});
					$(this)[0].reset();
				}

				return false;
			});
		});

		/* Fungsi yang dipanggil untuk memperbarui shout */
		function update() {
			$.ajax({
				type: 'get',
				data: 'minta',
				success: function(data) {
					$('#shout_text').empty().append(data);
				}
			});
			return false;
		}
	</script>
</body>

</html>
<?php
	} ?>