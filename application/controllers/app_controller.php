<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class App_controller extends CI_Controller {

	public function index()
	{
		$this->load->view('Vlogin');
	}

	public function CProsesLogin()
	{
		$pengguna = $this->input->post('nm_pengguna');
		$pass = $this->input->post('pass');
		$role = 'kasir';
		$hasil = $this->app_model->MprosesLogin($pengguna, $pass, $role);
		if ($hasil == false) {
			$this->session->set_flashdata('error_login', true);
			redirect('app_controller');
		}
		$hasil2 = array(
			'username' => $hasil['username'],
			'nm_user' => $hasil['nm_user'],
			'id_user' => $hasil['id_user'],
			'id_outlet' => $hasil['id_outlet']
			);
		$this->session->set_userdata($hasil2);
		//$this->session->set_userdata('id_outlet', $hasil2['id_outlet']);

		$this->session->set_flashdata('status', 'Selamat Datang : ' .$hasil2['username']);
		redirect('app_controller/CTampilMember');
	}

	public function CTampilMember()
	{
		$id_user = $this->session->userdata('id_user');
		$data = $this->app_model->MTampilMember($id_user);
		$this->load->view('VHome', ['data' => $data]);
	}

	public function CTambahMember()
	{
		$this->load->view('VTambahMember');
	}
	public function CProsesTambahMember()
	{
		$id_user = $this->session->userdata('id_user');
		$nm_member = $this->input->post('nm_member');
		$tlp_member = $this->input->post('tlp_member');
		$alamat_member = $this->input->post('alamat_member');

		$hasil = $this->app_model->MProsesTambahMember($nm_member, $tlp_member, $alamat_member, $id_user);
		if ($hasil == true) {
			$this->session->set_flashdata('status', 'Berhasil Menambahkan Member');

		}else {
			$this->session->set_flashdata('status', 'Gagal Menambahkan Member');
		}
		redirect('app_controller/CTampilMember');
	}

	public function Clogout()
	{
		$this->session->unset_userdata('username');
		redirect('app_controller');
	}

	public function CEditMember($id)
	{
		$data = $this->app_model->MEditMember($id);
		$this->load->view('VEditMember', ['data' => $data]);
	}

	public function CProsesEditMember($id_member)
	{
		$id = $id_member;
		$nm_member = $this->input->post('nm_member');
		$tlp_member = $this->input->post('tlp_member');
		$alamat_member = $this->input->post('alamat_member');

		$hasil = $this->app_model->MProsesEditMember($nm_member, $tlp_member, $alamat_member, $id);
		if ($hasil == true) {
			$this->session->set_flashdata('status', 'Berhasil Merubah Member ');

		}else {
			$this->session->set_flashdata('status', 'Gagal Merubah Member');
		}
		redirect('app_controller/CTampilMember');
	}

	public function CHapusMember($id)
	{
		$this->app_model->MHapusMember($id);
		redirect ('app_controller/CTampilMember');
	}

	public function CTampilService()
	{
		$ambil_jenis = $this->app_model->MAmbilJenis();
		$id_outlet = $this->session->userdata('id_outlet');


		foreach ($ambil_jenis as $j) {
			if ($j['jenis_paket'] == 'paketan') {
				$paketan = $this->app_model->MTampilPaket('paketan', $id_outlet);
				$paketan2 = $this->load->view('VServicePaket', ['data' => $paketan], true);
			} elseif ($j['jenis_paket'] == 'standar' ) {
				$standar = $this->app_model->MTampilPaket('standar', $id_outlet);
				$standar2 = $this->load->view('VServiceStandar', ['data' => $standar], true);
			}
		}

		$this->load->view('VService', ['standar' => $standar2, 'paketan' => $paketan2]);

	}

	public function CMasukKeranjang($id)
	{
		$id_paket = $id;
		$id_user = $this->session->userdata('id_user');
		$qty = $this->input->post('kuantitas');


		$hasil = $this->app_model->MMasukKeranjang($qty, $id_paket, $id_user);
		if ($hasil == true) {
			$this->session->set_flashdata('status', 'Berhasil Masuk Keranjang ');

		}else {
			$this->session->set_flashdata('status', 'Gagal Masuk Keranjang');
		}
		redirect('app_controller/CTampilService');
	}

	public function CTampilKeranjang()
	{
		$data = $this->app_model->MTampilKeranjang($this->session->userdata('id_user'));
		$this->load->view('VKeranjang', ['data' => $data]);
	}
	
	public function CHapusKeranjang($id_detail_transaksi)
	{
		$this->app_model->MHapusKeranjang($id_detail_transaksi);
	 	redirect('app_controller/CTampilKeranjang');
	}

	public function CProsesKeranjang()
	{
		$total_harga = $this->input->post('total_bayar');
		$id_member = $this->input->post('id_member');
		$biaya_tambahan = $this->input->post('biaya_tambahan');
		$pajak = $this->input->post('pajak');
		$diskon = $this->input->post('diskon');
		$keterangan = $this->input->post('keterangan');
		$batas_waktu = $this->input->post('batas_waktu');

		$id_user = $this->session->userdata('id_user');
		$id_outlet = $this->session->userdata('id_outlet');
		
		$hasil = $this->app_model->MProsesKeranjang($id_member, $biaya_tambahan, $pajak, $diskon, $id_user, $id_outlet, $batas_waktu, $total_harga);
		$hasil2 = $this->app_model->MUpdateKeranjang($id_user, $keterangan, $id_member);
		
		$invoice = $this->app_model->MAmbilDataTransaksi($id_member);
		$invoice2 = array(
			'kode_invoice' => $invoice['kode_invoice']
			);

		$updateStatus = $this->app_model->MUpdateStatus($invoice2['kode_invoice']);

		//mengecek klo berhasil checkout atau tidak
		if ($hasil == true) {
			$this->session->set_userdata($invoice2);
			$this->session->set_flashdata('status', 'Berhasil Checkout, dengan Kode Invoice : '.$invoice2['kode_invoice']);

		}else {
			$this->session->set_flashdata('status', 'Gagal Checkout');
		}
		redirect('app_controller/CTampilKeranjang');

	}	

	public function CTampilPembayaran()
	{
		$id_user = $this->session->userdata('id_user');

		$data = $this->app_model->MTampilPembayaran($id_user);
		$this->load->view('VPembayaran', ['data' => $data]);
	}

	public function CProsesTampilPembayaran($id_transaksi)
	{
		$data['data'] = $this->app_model->MProsesTampilBayar($id_transaksi);
		$this->load->view('VProsesPembayaran', $data);
	}

	public function CHapusPembayaran($id_transaksi)
	{
		$data = $this->app_model->MHapusPembayaran($id_transaksi);
		redirect('app_controller/CTampilPembayaran');
	}


		// $sql = $this->App_model->MProsesTampilBayar($id_transaksi);

		// $ambil_total_harga = $sql['total_harga'];
		// $ambil_bayar_transaksi = $sql['bayar_transaksi'];
	public function CProsesBayar($id_transaksi)
	{

		$bayar = $this->input->post('bayar');
		$ambil_total_harga = $this->app_model->MAmbilTotal($id_transaksi);
		$total_harga = $ambil_total_harga['total_harga'];
		

		$hasil = $this->app_model->MProsesBayar($id_transaksi, $bayar, $total_harga);


		if ($hasil == true) {
			$this->session->set_flashdata('status', 'Pembayaran Berhasil ');

		}else {
			$this->session->set_flashdata('status', 'Gagal Melakukan Pembayaran');
		}

		redirect('app_controller/CProsesTampilPembayaran/'.$id_transaksi);
	}

	public function CTampilSelesai($id_transaksi)
	{
		$hasil = $this->app_model->MTampilSelesai($id_transaksi);

		if ($hasil == true) {
			$this->session->set_flashdata('status', 'Data Berhasil Diperbaharui ');

		}else {
			$this->session->set_flashdata('status', 'Gagal Melakukan Pembaharuan');
		}

		redirect('app_controller/CTampilPembayaran/');
	}

	public function CProsesTampilPengambilan($id_transaksi)
	{
		$data = $this->app_model->MProsesTampilBayar($id_transaksi);
		$this->load->view('VProsesPengambilan', ['data' => $data]);
	}

	public function CProsesPengambilan($id_transaksi)
	{
		$hasil = $this->app_model->MProsesPengambilan($id_transaksi);

		if ($hasil == true) {
			$this->session->set_flashdata('status', 'Data Berhasil Diperbaharui ');

		}else {
			$this->session->set_flashdata('status', 'Gagal Melakukan Pembaharuan');
		}

		redirect('app_controller/CTampilPembayaran/');
	}

	public function CTampilLaporan()
	{
		$id_user = $this->session->userdata('id_user');

		$data = $this->app_model->MTampilPembayaran($id_user);
		$this->load->view('VLaporan', ['data' => $data]);
	}

	public function CCariRange()
	{
		$tgl_awal = $this->input->post('tgl_awal');
		$tgl_akhir = $this->input->post('tgl_akhir');

		$id_user = $this->session->userdata('id_user');

		$data = $this->app_model->MCariRange($id_user, $tgl_awal, $tgl_akhir);
		$this->load->view('VLaporan', ['data' => $data]);
	}


	

	}
 ?>