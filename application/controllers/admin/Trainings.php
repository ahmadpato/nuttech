<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once('vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

class Trainings extends CI_Controller
{
    public $id;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("training_model");
        $this->load->library('form_validation');
        $this->load->model("training_model");

		// if($this->training_model->isNotLogin()) redirect(site_url('admin/login'));
    }

    public function index()
    {
        $data["trainings"] = $this->training_model->getAll();
        $training = $this->training_model;
        $validation = $this->form_validation;
        $validation->set_rules($training->rules());

        $this->form_validation->set_rules('name', 'name', 'is_unique[trainings.name]');

        if ($validation->run()) {
            $training->save();
            $this->session->set_flashdata('success', 'Berhasil disimpan');
            redirect(site_url('admin/trainings'));
        } 

        $this->load->view("admin/training/list", $data);
    }

    public function add()
    {
        $training = $this->training_model;
        $validation = $this->form_validation;
        $validation->set_rules($training->rules());

        if ($validation->run()) {
            $training->save();
            $this->session->set_flashdata('success', 'Berhasil disimpan');
        }

        $this->load->view("admin/training/new_form");
    }

    public function edit($id = null)
    {
        if (!isset($id)) redirect('admin/trainings');
       
        $training = $this->training_model;
        $validation = $this->form_validation;
        $validation->set_rules($training->rules());

        if ($validation->run()) {
            $training->update();
            $this->session->set_flashdata('success', 'Berhasil disimpan');
        }

        $data["trainings"] = $training->getById($id);
        if (!$data["trainings"]) show_404();
        
        $this->load->view("admin/training/edit_form", $data);
    }

    public function delete($id=null)
    {
        if (!isset($id)) show_404();
        
        if ($this->training_model->delete($id)) {
            redirect(site_url('admin/trainings'));
        }
    }

    public function report(){

        $this->load->model('training_model');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Training');
        
        $product = $this->training_model->getAll();
        $no = 1;
        $x = 2;
        foreach($product as $row)
        {
            $sheet->setCellValue('A'.$x, $no++);
            $sheet->setCellValue('B'.$x, $row->name);
            $x++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan-training';
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
