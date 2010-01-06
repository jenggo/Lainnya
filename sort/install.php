<?php

	$sqlite = new sqlitedatabase('./sqlite.db');

	// Buat tabel baru
	$tabel = '
		CREATE TABLE konten (
			nomor integer primary key unique,
			tanggal timestamp,
			judul varchar
		)';
	$sqlite->query($tabel);

	// Masukkan 200 row
	for ($i = 1; $i <= 200; $i++) {
		$judul = acak_adut();
		$tanggal = time() / $i;
		$data = "INSERT INTO konten (tanggal, judul) VALUES ('$tanggal', '$judul')";
		$sqlite->query($data);
	}

	header('location: index.php');

	// Fungsi untuk membuat teks acak
    function acak_adut() {
		$huruf_mati = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z');
		$huruf_hidup = array('a', 'e', 'i', 'u', 'o');

		$kalimat = '';
		$maks = 5;

		for($i = 1; $i <= $maks; $i++) {
			$kalimat .= $huruf_mati[rand(0,19)];
			$kalimat .= $huruf_hidup[rand(0,4)];
		}

		return $kalimat;
	}
?>