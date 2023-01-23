<?php

include_once 'functions.php';
connectFB();

class PDF extends FPDF {

	function CreateTableDuplicidade($header, $matricula, $data) {
		$this->SetFillColor(217, 242, 250);
		$this->Ln();
		$this->Ln();
	    //Column widths
	    $widths = array(70, 50, 30, 30, 30);
	    //Header
	    for ( $i = 0; $i < count($header); $i++ ) :
	        $this->Cell($widths[$i],7,str_replace('_', ' ', utf8_decode($header[$i])),1,0,'C');
	    endfor;
	    //Data
	    $this->Ln();
	    $fill = false;
	    //Puting each data in the PDF
	    foreach ( $data as $duplicidade ) :
	    	$pagador = getPagadorByOurNumber(pathName(takeClient($matricula)), $duplicidade['nosso_numero'], 'NotEncoded');
	    	$valor = number_format(str_replace('_', '.', $duplicidade['valor']), 2, ',', '.');
	    	//Showing the data
	        $this->Cell($widths[0], 6, substr($pagador, 0, 35),                                    '1', 0, 'L', $fill);
	        $this->Cell($widths[1], 6, $duplicidade['nosso_numero'], 		  					   '1', 0, 'C', $fill);
	        $this->Cell($widths[2], 6, $valor, 								  					   '1', 0, 'R', $fill);
	        $this->Cell($widths[3], 6, fmtDatePattern($duplicidade['data_pgto_original'], '4'),    '1', 0, 'C', $fill);
	        $this->Cell($widths[4], 6, fmtDatePattern($duplicidade['data_credito_original'], '4'), '1', 0, 'C', $fill);
	        $this->Ln();
	    	$fill = !$fill;
	    endforeach;
	}

	function ImprovedTable($crud, $header, $data, $customer, $convs = null){
		$this->SetFillColor(217, 242, 250);
		$this->Ln();
		$this->Ln();
	    //Column widths
	    $w = array(20, 70, 40, 20, 30, 90);
	    //Header
	    for ( $i = 0, $len = count($header); $i < $len; $i++ ) :
	        $this->Cell($w[$i],7,str_replace('_', ' ', utf8_decode($header[$i])), 1, 0, 'C');
	    endfor;
	    //Data
	    $this->Ln();
	    $fill = false;
	    //Puting each data in the PDF
	    foreach ( $data as $titulo ) :
    		$nosso_numero = $titulo['nosso_numero'];
			$matricula = substr($nosso_numero, 7, 3);
			$cliente = $customer->GetSiglaByCodSac($matricula);
			$pagador = getPagadorByOurNumber($customer->GetPathNameBySigla($cliente), $nosso_numero);
			$valor = $titulo['valor_titulo'];
			$vencimento = $titulo['vencimento'];
			if (array_key_exists('motivo', $titulo)) :
				$motivo = array();
				for ($i = 0; $i < (strlen(trim(str_replace('_', '', $titulo['motivo'])))/2); $i++) :
					if ($i == 0) :
						$motivo[] = utf8_decode(getMotivoOcorrencia($crud, substr($titulo['motivo'], $i, 2), 'c047', 'A'));
					else :
						$motivo[] = utf8_decode(getMotivoOcorrencia($crud, substr($titulo['motivo'], $i+$i, 2), 'c047', 'A'));
					endif;
				endfor;
				//Showing the data
		        $this->Cell($w[0], 6 * count($motivo), $cliente, '1', 0, 'C', $fill);
		        $this->Cell($w[1], 6 * count($motivo), utf8_decode(substr($pagador, 0, 35)), '1', 0, 'L', $fill);
		        $this->Cell($w[2], 6 * count($motivo), $nosso_numero, '1', 0, 'C', $fill);
		        if ( !is_null($vencimento) ) $this->Cell($w[3], 6 * count($motivo), fmtDatePattern($vencimento, '4'), '1', 0, 'C', $fill);
		        $this->Cell($w[4], 6 * count($motivo), $valor, '1', 0, 'R', $fill);
		        $this->MultiCell($w[5], 6, implode(PHP_EOL, $motivo), '1', 'L', $fill);
		    else :
		    	//Showing the data
		        $this->Cell($w[0], 6, $cliente, '1', 0, 'C', $fill);
		        $this->Cell($w[1], 6, $pagador, '1', 0, 'L', $fill);
		        $this->Cell($w[2], 6, $nosso_numero, '1', 0, 'C', $fill);
				if ( !is_null($vencimento) ) {
					$this->Cell($w[3], 6, fmtDatePattern($vencimento, '4'), '1', 0, 'C', $fill);
				} else {
					$w[4] = $w[3];
				}
		        $this->Cell($w[4], 6, $valor, '1', 0, 'R', $fill);
	    		$this->Ln();
			endif;
	        $fill = !$fill;
	    endforeach;
	}
	  
}