<?php
	include_once '../../vendor/setasign/fpdf/fpdf.php';
	include_once 'class/FPDF.class.php';
	include_once 'class/FirebirdCRUD.class.php';
	include_once 'class/Customer.class.php';
	include_once 'functions.php';

	$crud = new FirebirdCRUD();
	$customer = new Customer();
	$params = unserialize(file_get_contents('php://input'));
	
	//Parâmetros
	$titulo = $params[0];
	$header = $params[1];
	$data = $params[2];
	$nome_arquivo = $params[3];
	if ( !file_exists(str_replace('_', ' ', 'C:/Setydeias/Setyware/ADM77777/Adm/Retornos/'.$nome_arquivo.'.pdf')) ) {
		$dia_util = $params[4];
		//PDF das duplicidades
		$pdf = new PDF("L", "mm", "A4");
		$pdf->SetTitle($titulo, true);
		$pdf->SetFont('Arial', '', 12);
		$pdf->AddPage();
		//HEADER
		$pdf->Cell(40, 7, utf8_decode(str_replace('_', ' ', $titulo))." PROCESSADOS NO DIA ".fmtDatePattern($dia_util, '15'));
		//BODY
		$pdf->SetFont('Arial', '', 9);
		$pdf->ImprovedTable($crud, $header, $data, $customer);
		$pdf->Output('F', str_replace('_', ' ', 'C:/Setydeias/Setyware/ADM77777/Adm/Retornos/'.$nome_arquivo.'.pdf'));
		$pdf->Close();
	}