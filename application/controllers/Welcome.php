<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header('Access-Control-Allow-Origin:*');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

class Welcome extends CI_Controller {
function __construct() {
	parent::__construct();
	header('Access-Control-Allow-Origin:*');
	header("Access-Control-Allow-Credentials: true");
	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
	header('Access-Control-Max-Age: 1000');
	header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
}
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}
	function returnResult($data)
	{
		$result = array(
			'code'=>200,
			'rowCount'=>$data->num_rows(),
			'row'=>$data->result()
		);
		return $result;
	}

	function returnResultErrorDB()
	{
		return array(
			'code'=>400,
			'msg'=>'Mohon Maaf Server Sedang Gangguan'
		);
	}
	function returnResultCustom($t,$msg)
	{
		return array(
			'code'=>$t,
			'msg'=>$msg
		);
	}
	function login()
	{
		try {
			$email = $this->input->post('username');
			$token = $this->input->post('password');
			if ($email != null AND $token != NULL) {
				$where = array(
					'username'=>$email,
					'token'=>$token,
				);
				$row = $this->db->get_where('User',$where);
				if ($row->num_rows() > 0) {
					$cekrow = $row->row();
					$cekpemohon = password_verify(''.$token.'', ''.$cekrow->password.'');
					if ($cekpemohon == true) {
						$q = $this->db->get_where('User',$where);
						$res = $this->returnResult($q);
					}else{
						$res = $this->returnResultCustom(400,'Password anda salah');
					}
				}else{
					$res = $this->returnResultCustom(400,'Tidak Ada Data');
				}
			}else{
				$res = $this->returnResultCustom(400,'Email dan Token Tidak Boleh Kosong');
			}
		} catch (Exception $e) {
			$res = $this->returnResultCustom(400,$e);
		}
		echo json_encode($res);
	}
	function Komentar()
	{
		$komentar = array(
			'permohonan_id'=>$this->input->post('idpermohonan'),
			'user_id'=>$this->input->post('iduser'),
			'komentar'=>$this->input->post('komentar'),
			'status'=>1
		);
		$p = $this->db->insert('Komentar', $komentar);
		if ($p) {
			$response = $this->returnResultCustom(200,'Berhasil Input data');
		}else{
			$response = $this->returnResultErrorDB();
		}
		echo json_encode($response);
	}
	function EditKomentar()
	{
		$where = array(
			'user_id'=>$this->input->post('iduser'),
			'permohonan_id'=>$this->input->post('idpermohonan'),
		);
		$komentar = array(
			'komentar'=>$this->input->post('komentar'),
		);
		$p = $this->db->update('Komentar', $komentar,$where);
		if ($p) {
			$response = $this->returnResultCustom(200,'Berhasil Input data');
		}else{
			$response = $this->returnResultErrorDB();
		}
		echo json_encode($response);
	}
	function HapusKomentar()
	{
		$where = array(
			'user_id'=>$this->input->post('iduser'),
			'permohonan_id'=>$this->input->post('idpermohonan'),
		);
		$komentar = array(
			'status'=>0,
		);
		$p = $this->db->update('Komentar', $komentar,$where);
		if ($p) {
			$response = $this->returnResultCustom(200,'Berhasil Input data');
		}else{
			$response = $this->returnResultErrorDB();
		}
		echo json_encode($response);
	}
	function getDataKomentar()
	{
		$q = $this->db->get_where('komentar',array('permohonan_id'=>$this->input->post('idpermohonan')));
		$response = $this->returnResult($q);
		echo json_encode($response);
	}
	function InputData()
	{
		$id = $this->input->post('iduser');
		$judul = $this->input->post('judul');
		$latitude = $this->input->post('lat');
		$longitude = $this->input->post('long');
		$alamat = $this->input->post('alamat');
		$kelurahan = $this->input->post('kelurahan');
		$kecamatan = $this->input->post('kecamatan');
		$file = $this->UploadFile('file');

		$permohonan = array(
			'judul'=>$judul,
			'uploadby'=>$id,
		);
		$p = $this->db->insert('Permohonan', $permohonan);
		$iddata = $this->db->insert_id();
		if ($p) {
			$berkas = array(
				'file'=>$file,
				'permohonan_id'=>$iddata,
				'koordinat_lat'=>$latitude,
				'koordinat_long'=>$longitude,
				'alamat'=>$alamat,
				'kecamatan'=>$kecamatan,
				'kelurahan'=>$kelurahan,
			);
			$b = $this->db->insert('Berkas', $berkas);
			if ($b) {
				$response = $this->returnResultCustom(200,'Berhasil Input data');
			}else{
				$response = $this->returnResultErrorDB();
			}
		}else{
			$response = $this->returnResultErrorDB();
		}
		echo json_encode($response);
	}
	function getDataPengajuan()
	{
		$this->db->select('p.permohonan_id,p.judul,p.tanggal,b.file,b.koordinat_lat,b.koordinat_long,b.kecamatan,b.kelurahan,u.name,d.nama_dinas');
		$this->db->from('Permohonan p');
		$this->db->join('Berkas b', 'Permohonan.permohonan_id = Berkas.permohonan_id', 'INNER');
		$this->db->join('User u', 'User.user_id = Permohonan.uploadby', 'INNER');
		$this->db->join('Dinas d', 'User.dinas_id = Dinas.dinas_id', 'INNER');
		$q = $this->db->get();
		$response = $this->returnResult($q);
		return var_dump($this->db->last_query());
		echo json_encode($response);
	}
	function getDataByid()
	{
		try {
			$q = $this->queryDatanya();
			if ($q->num_rows() != 0) {
				$response = $this->returnResult($q);
			}else{
				$response = $this->returnResultCustom(400,'Data Tidak Ada');
			}
		} catch (Exception $e) {
			throw new Exception("Error Processing Request", 1);	
		}
		echo json_encode($response);
	}
	function queryDatanya()
	{
		$this->db->select('p.judul,p.tanggal,b.file,b.koordinat_lat,b.koordinat_long,u.name,d.nama_dinas');
		$this->db->from('Permohonan p');
		$this->db->join('Berkas b', 'Permohonan.permohonan_id = Berkas.permohonan_id', 'INNER');
		$this->db->join('User u', 'User.user_id = Permohonan.uploadby', 'INNER');
		$this->db->join('Dinas d', 'User.dinas_id = Dinas.dinas_id', 'INNER');
		$this->db->where('Permohonan.permohonan_id', $this->input->post('id'));
		$q = $this->db->get();
		return $q;
	}
	function UploadFile($params)
	{
		$this->load->library('upload');
		$config['upload_path'] = './assets/berkas/';
		$config['allowed_types'] = 'doc|docx|pdf|';
		$config['encrypt_name']         = TRUE;
		$config['remove_spaces']        = TRUE;

		$ga = "";
		$this->upload->initialize($config);
		if ($this->upload->do_upload($params)) {
			$s = $this->upload->data();
			if (count($s) != 14) {
				for ($i=0; $i < count($s); $i++) {
					$abc = $s[$i]['file_name'].',';
					$ga .= $abc;
				}
				$newfiledalam = substr($ga, 0, -1);
			}else{
				$newfiledalam = $s['file_name'];
			}
		}else{
			$s = $this->input->post($params);
			for ($i=0; $i < count($s); $i++) {
				$abc = $s[$i].',';
				$ga .= $abc;
			}
			$newfiledalam = substr($s, 0, -1);
		}
		return $newfiledalam;
	}
}
