<?php
	include_once '../../vendor/setasign/fpdf/fpdf.php';
	include_once 'class/FPDF.class.php';
	include_once 'functions.php';

	$data = array();

	foreach ($_POST as $key => $value) :
		$data[] = $value;
	endforeach;

	$duplicidades = unserialize($data[0]);
	$data_arquivo = $data[1];

	foreach ($duplicidades as $key => $duplicidade) :
		/*
		* $key -> Índice (matrícula do cliente)
		*/
		$sigla = takeClient($key); //Sigla do cliente
		$path = pathName($sigla); //Pasta do cliente
		//PDF das duplicidades
		$pdf = new PDF("L", "mm", "A4");
		$pdf->SetTitle("Relatório de Duplicidades", true);
		$pdf->SetFont('Arial','',12);
		$pdf->AddPage();
		//HEADER
		$pdf->Cell(40, 7, "Pagamentos em duplicidade ocorridos em ".fmtDatePattern($data_arquivo, '4'));
		//BODY
		$pdf->SetFont('Arial', '', 9);
		$header = array('Pagador', 'Nosso Número', 'Valor do pgto. (R$)', 'Data pgto. original', 'Data créd. original');
		$pdf->CreateTableDuplicidade($header, $key, $duplicidade);
		if (!is_dir("C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\$path\\Duplicidades\\")) :
			if(!mkdir("C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\$path\\Duplicidades\\")):
				createAlert('danger', "[$sigla] A pasta \"Duplicidades\" não existe e o sistema não conseguiu criar a mesma");
			endif;
		endif;
		try {
			$pdf->Output('F', "C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\$path\\Duplicidades\\DUPLICIDADES_".fmtDatePattern($data_arquivo, '9')."_$sigla.pdf");
		} catch (Exception $e) {
			createAlert('danger', "[$sigla] Erro ao gerar o relatório, verifique se o arquivo já está em uso");
		}
		$pdf->Close();
	endforeach;