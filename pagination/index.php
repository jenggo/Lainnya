<?php

	// Panggil database sqlite, jika belum ada buat baru
	$sqlite = new sqlitedatabase('./sqlite.db');

	// Ambil tabel, bila belum ada buat baru
	$tabel = $sqlite->query("SELECT nomor, tanggal, judul FROM konten");
	if (!$tabel)
		header('location: install.php');

	// Periksa request POST dan GET (nilai dari 'halaman'), bila tidak ada dianggap halaman 1

	if (isset($_POST['halaman']) AND !empty($_POST['halaman'])) { // Dari request ajax
		$halaman = $_POST['halaman'];
	}
	elseif (isset($_GET['halaman']) AND !empty($_GET['halaman'])) { // Sedangkan ini tanpa ajax
		$halaman = $_GET['halaman'];
	}
	else { // Tidak ada request sama sekali, beri nilai 1
		$halaman = 1;
	}

	// Maksimal row database yang ditampilkan
	$limit = 15; // 15 row

	$start = $limit * ($halaman - 1);

	// Hitung jumlah row tabel
	$jumlah_konten = $tabel->numrows();

	// Hitung banyaknya halaman
	$total_halaman = ceil($jumlah_konten / $limit);

	// Buat list pagination
	$pagination = '';
	for ($i = 1; $i <= $total_halaman; $i++)
		$pagination .= "<li><a href='?halaman=$i' title='Halaman $i' rev='$i'>$i</a></li>";

	// Proses pagination (request ajax)
	if (isset($_POST['halaman'])) {

		sleep(2); // Jangan buru-buru ditampilkan.. (biar bisa lihat loader-nya ajax.. hehe..)

		// Ambil row dan sesuaikan jumlahnya
		$row_database = $sqlite->arrayQuery("SELECT nomor, tanggal, judul FROM konten LIMIT $start, $limit");

		foreach ($row_database as $data) {

			// Ubah timestamp ke format tanggal
			$tanggal = date('j M Y', $data['tanggal']);

			echo '
				<tr>
					<td>'.$data['nomor'].'</td>
					<td>'.$tanggal.'</td>
					<td>'.$data['judul'].'</td>
				</tr>
			';
		}
	}
	else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Pagination dengan PHP, SQLite, dan JQuery</title>
	<script type="text/javascript" src="jquery-1.3.2.min.js"></script>
	<style type="text/css">
		body {
			color: #fff
		}

		#pagination {
			list-style: none;
			margin: 50px 0;
			text-align: center
		}
		#pagination li {
			background-color: #4873EF;
			display: inline;
			margin: 0 7px;
			padding: 3px
		}
		#pagination li:hover {
			background-color: #2C4FB4
		}
		#pagination li a {
			text-decoration: none;
			font-weight: 700;
			color: #fff;
		}

		table {
			margin: 0 auto;
			width: 50%;
		}
		th {
			font-weight: 700;
			background-color: #7898F4;
			padding: 4px
		}
		td {
			background-color: #425A9F;
			padding: 2px;
			text-align: center
		}

		#loading {
			padding: 12px;
			background: url(loading_bar.gif) no-repeat
		}
	</style>
</head>

<body>
	<!-- Tabel -->
	<table>
		<thead>
			<tr>
				<th>Nomor</th>
				<th>Tanggal</th>
				<th>Judul</th>
			</tr>
		</thead>
		<tbody id="tbody">
			<?php

				// Ambil row dan sesuaikan jumlahnya
				$row_database = $sqlite->arrayQuery("SELECT nomor, tanggal, judul FROM konten LIMIT $start, $limit");

				foreach ($row_database as $data) {

					// Ubah timestamp ke format tanggal
					$tanggal = date('j M Y', $data['tanggal']);

					?>
						<tr>
							<td><?php echo $data['nomor']; ?></td>
							<td><?php echo $tanggal; ?></td>
							<td><?php echo $data['judul']; ?></td>
						</tr>
					<?php
				}
			?>
		</tbody>
	</table>

	<ul id="pagination">
		<?php echo $pagination; ?>
	</ul>

<script type="text/javascript">
	$(function(){

		// Lakukan sesuatu sebelum dan setelah ajax bekerja
		$('#tbody').ajaxStart(function(){
			$(this).empty().fadeOut('slow').append('<p id="loading">&nbsp;</p>'); // Sebelum : kosongkan isi tbody
		}).ajaxStop(function(){
			$(this).fadeIn('slow'); // Setelah : tampilkan tbody dengan isi yang baru
		});

		// Fungsi pagination
		$('#pagination li a').click(function(){ // Bila link yang ada di <li> dari <ul id="pagination"> diklik, maka lakukan :
			$.ajax({ // Gunakan fungsi ajax JQuery
				type: 'POST', // Pakai metode POST, salah satu alasannya agar pagination tetap bekerja apabila javascript tidak aktif
				data: 'halaman=' + $(this).attr('rev'), // Data yang diambil adalah dari attribut rev (<a href="" title="" rev="">)
				success: function(data){ // Bila ajax berhasil dilakukan, lanjutkan dengan :
					$('#tbody').empty().append(data); // Munculkan hasil request kedalam div
				}
			});
			return false;
		});
	});
</script>
</body>
</html>
<?php
	}
?>