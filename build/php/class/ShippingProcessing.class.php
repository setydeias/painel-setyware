<?php
    error_reporting(0);
    date_default_timezone_set('America/Sao_Paulo');
    session_start();
    include_once '../functions.php';
    include_once 'Customer.class.php';
    include_once 'DirManager.class.php';
    include_once 'Util.class.php';
    include_once 'MailParams.class.php';
    include_once 'strategy_class/StrategyDelivery.php';
    include_once '../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
    include_once '../../../vendor/autoload.php';
    use Dompdf\Dompdf;

    class ShippingProcessing {

        public function __construct($file) {
            try {
                // [INICIO] ----- Caminho para as remessas processadas -----//
                $dir = new DirManager();
                $MailParams = new MailParams();
                $this->email = $MailParams::_mail();
                $this->mail_port = $MailParams::_port();
                $this->mail_smtp_host = $MailParams::_smtp_host();
                $this->mail_name = $MailParams::_name();
                $this->mail_password = $MailParams::_password();
                $path = $dir->getDirs(array('d.REMESSA_PROCESSADA_GRAFICA'));
                $this->remProcessedDir = $path['REMESSA_PROCESSADA_GRAFICA'][0];
                // [FIM] -------- Caminho para as remessas processadas -----//
                $this->file = $file;
                $this->customer = new Customer();
                $this->taxes = $this->customer->getPrintDeliveryTaxes();
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        public function __destruct() {}

        /*
        * Inicia o fluxo de criação de remessas
        */
        public function generate() {
            $header = $this->getHeader();
            $details = $this->getDetailRecords($this->getPackages());
            $this->createShipp($header, $details); //Cria os arquivos de remessa
            $data = $this->getShippingInfo($header, $details); //Retorna as informações dos arquivos de remessa

            return $data;
        }

        /*
        * Insere remessa no banco de dados
        */
        public function registerShipping($rem_info) {
            //Verifica se a remessa já foi processada
            //Caso a remessa já tenha sido processada, retorna true
            $processed_shipping = $this->customer->getProcessedShipping($rem_info['SIGLA'], $rem_info['SEQUENCIAL']);
            
            return count($processed_shipping) > 0 ? true : !$this->customer->addProcessedShipping($rem_info);
        }

        /*
        * Escreve as informações da remessa no AdminPJ
        */
        public function writeAdminPJ($data) {
            if ( $data['write_adminpj'] ) {
                $adminpj = "C:\\Setydeias\\Financas\\AdminPJ.xls";
                if ( file_exists($adminpj) ) {
                    $qtde_titulos = array('INDIVIDUAL' => 0, 'UNICO' => 0);
                    $valor_total = 0;
                    $menor_vcto = Util::FmtDate($data['MENOR_VCTO'], '21');
                    $maior_vcto = Util::FmtDate($data['MAIOR_VCTO'], '21');
                    $dt_rec = explode(' ', $data['DATA_RECEBIMENTO']);
                    $data_recebimento = Util::FmtDate($dt_rec[0], '22');
                    $data_pagamento = Util::FmtDate($data['DATA_PAGAMENTO'], '22');
                    $hora_rec = explode(':', $dt_rec[2]);
                    $hora_recebimento = "$hora_rec[0]:$hora_rec[1]";
            
                    foreach ( $data['PACOTES'] as $package ) {
                        $desc = $package['DESC'] == 'Individual' ? 'INDIVIDUAL' : 'UNICO';
                        $qtde_titulos[$desc] += $package['QTDE_TITULOS'];
                        $valor_total += $package['VALOR_TOTAL'];
                    }

                    $custo_impressao = array_sum($qtde_titulos) * $this->taxes['IMPRESSAO'];
                    $custo_entrega = ($qtde_titulos['INDIVIDUAL'] * $this->taxes['ENTREGA_INDIVIDUAL']) + ($qtde_titulos['UNICO'] > 0 ? $this->taxes['ENTREGA_UNICA'] : 0);
                    $custo_final = $custo_impressao + $custo_entrega;

                    $sheet = PHPExcel_IOFactory::identify($adminpj);
                    $objReader = PHPExcel_IOFactory::createReader($sheet);
                    $objPHPExcel = $objReader->load($adminpj);
                    $objPHPExcel->setActiveSheetIndexByName('Setydeias');
                    //Loop for find info
                    foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) {
                        $highestRow = $worksheet->getHighestRow();
                        //Loop in all cells of the sheet
                        for ( $cc = 7; $cc < $highestRow; $cc++ ) {
                            if ( is_null($worksheet->getCellByColumnAndRow(0, $cc)->getValue()) ) {
                                $objWorksheet = $objPHPExcel->getActiveSheet();
                                $objWorksheet->freezePane('C7');
                                $objWorksheet->insertNewRowBefore($cc, 1);
                                $objWorksheet
                                    ->setCellValue("A$cc", $data['SIGLA'])
                                    ->setCellValue("B$cc", $data['SEQUENCIAL'])
                                    ->setCellValue("E$cc", $qtde_titulos['UNICO'] > 0 ? $qtde_titulos['UNICO'] : NULL)
                                    ->setCellValue("G$cc", $qtde_titulos['INDIVIDUAL'] > 0 ? $qtde_titulos['INDIVIDUAL'] : NULL)
                                    ->setCellValue("H$cc", $qtde_titulos['INDIVIDUAL'] + $qtde_titulos['UNICO'])
                                    ->setCellValue("I$cc", $valor_total)
                                    ->setCellValue("J$cc", '=IF(E'.$cc.'>0,2+INT(E'.$cc.'/19)+IF((E'.$cc.'/19-INT(E'.$cc.'/19))>0,1,0),0)+IF(G'.$cc.'>0,1,0)')
                                    ->setCellValue("K$cc", $menor_vcto)
                                    ->setCellValue("L$cc", $maior_vcto)
                                    ->setCellValue("M$cc", $data_recebimento)
                                    ->setCellValue("N$cc", $hora_recebimento)
                                    ->setCellValue("Q$cc", '=IF(OR(M'.$cc.'="",R'.$cc.'=""),0,R'.$cc.'-M'.$cc.')+IF(AND(M'.$cc.'<>"",R'.$cc.'=""),TODAY()-M'.$cc.',0)')
                                    ->setCellValue("T$cc", '=IF(D'.$cc.'=0,"",D'.$cc.')')
                                    ->setCellValue("W$cc", '=IF(OR($R'.$cc.'="",U'.$cc.'=""),0,U'.$cc.'-$R'.$cc.')+IF(AND($R'.$cc.'<>"",U'.$cc.'=""),TODAY()-$R'.$cc.',0)*IF(E'.$cc.'>0,1,0)')
                                    ->setCellValue("X$cc", '=IF(F'.$cc.'=0,"",F'.$cc.')')
                                    ->setCellValue("AA$cc", '=IF(OR($R'.$cc.'="",Y'.$cc.'=""),0,Y'.$cc.'-$R'.$cc.')+IF(AND($R'.$cc.'<>"",Y'.$cc.'=""),TODAY()-$R'.$cc.',0)*IF(G'.$cc.'>0,1,0)')
                                    ->setCellValue("AB$cc", '=MAX(W'.$cc.',AA'.$cc.')')
                                    ->setCellValue("AC$cc", '--------')
                                    ->setCellValue("AD$cc", '------')
                                    ->setCellValue("AE$cc", '=IF(OR(U'.$cc.'="",AC'.$cc.'="--------",AC'.$cc.'="??/??/??"),0,AC'.$cc.'-U'.$cc.')+IF(AND(U'.$cc.'<>"",AC'.$cc.'="??/??/??"),TODAY()-U'.$cc.',0)*IF(E'.$cc.'>0,1,0)')
                                    ->setCellValue("AF$cc", '=IF(G'.$cc.'>0,"??/??/??","------")')
                                    ->setCellValue("AG$cc", '=IF(G'.$cc.'>0,"??:??","------")')
                                    ->setCellValue("AH$cc", '=IF(OR(Y'.$cc.'="",AF'.$cc.'="------",AF'.$cc.'="??/??/??"),0,AF'.$cc.'-Y'.$cc.')+IF(AND(Y'.$cc.'<>"",AF'.$cc.'="??/??/??"),TODAY()-Y'.$cc.',0)*IF(G'.$cc.'>0,1,0)')
                                    ->setCellValue("AI$cc", '=IF(AE'.$cc.'>AH'.$cc.',AE'.$cc.',AH'.$cc.')')
                                    ->setCellValue("AJ$cc", '=K'.$cc.'-(M'.$cc.'+Q'.$cc.'+AB'.$cc.'+3)')
                                    ->setCellValue("AK$cc", '=IF(AJ'.$cc.'<3,"<<<<","")')
                                    ->setCellValue("AM$cc", '=IF(AND(AK'.$cc.'<>"",AL'.$cc.'=""),1,0)')
                                    ->setCellValue("AN$cc", $qtde_titulos['UNICO'] > 0 ? date('m') : NULL)
                                    ->setCellValue("AQ$cc", $qtde_titulos['UNICO'] > 0 ? $this->taxes['ENTREGA_UNICA'] : NULL)
                                    ->setCellValue("AR$cc", date('m'))
                                    ->setCellValue("AS$cc", '=D'.$cc)
                                    ->setCellValue("AT$cc", '=E'.$cc)
                                    ->setCellValue("AU$cc", '=F'.$cc)
                                    ->setCellValue("AV$cc", '=G'.$cc)
                                    ->setCellValue("AW$cc", '=AT'.$cc.'+AV'.$cc)
                                    ->setCellValue("AX$cc", '=AW'.$cc.'*'.$this->taxes['IMPRESSAO_GRAFICA'])
                                    ->setCellValue("AZ$cc", '=J'.$cc)
                                    ->setCellValue("BA$cc", '=AZ'.$cc.'*0.12752')
                                    ->setCellValue("BB$cc", '=AX'.$cc.'+BA'.$cc)
                                    ->setCellValue("BC$cc", date('m'))
                                    ->setCellValue("BF$cc", '=BD'.$cc.'+BE'.$cc)
                                    ->setCellValue("BG$cc", '=G'.$cc.'*'.$this->taxes['ENTREGA_INDIVIDUAL'])
                                    ->setCellValue("BH$cc", '=BF'.$cc.'+BG'.$cc)
                                    ->setCellValue("BI$cc", '=BH'.$cc.'*5%')
                                    ->setCellValue("BJ$cc", '=BH'.$cc.'-BI'.$cc)
                                    ->setCellValue("BK$cc", $data_pagamento)
                                    ->setCellValue("BL$cc", $custo_final)
                                    ->setCellValue("BM$cc", $data['REPASSE'] ? 'RE' : 'DC')
                                    ->setCellValue("BN$cc", '=IF(BM'.$cc.'="BO",5,0)+IF(BM'.$cc.'="DC",'.$this->taxes['DEBITO_CONTA'].',0)')
                                    ->setCellValue("BO$cc", '=IF(SUMIF(A$278:A$309,A'.$cc.',T$278:T$309)=0,"","X")')
                                    ->setCellValue("BP$cc", '=IF(BO'.$cc.'="X",BL'.$cc.'*6%,0)')
                                    ->setCellValue("BQ$cc", '=BL'.$cc.'-BB'.$cc.'-BJ'.$cc.'-BN'.$cc.'-BP'.$cc.'-AQ'.$cc)
                                    ->setCellValue("BR$cc", $data['REPASSE'] ? $data_pagamento : NULL)
                                    ->setCellValue("BU$cc", '=IF(AND(BS'.$cc.'<>"",BT'.$cc.'=""),1,0)')
                                    ->setCellValue("BV$cc", '=IF($AR'.$cc.'="01",$BQ'.$cc.',0)')
                                    ->setCellValue("BW$cc", '=IF($AR'.$cc.'="02",$BQ'.$cc.',0)')
                                    ->setCellValue("BX$cc", '=IF($AR'.$cc.'="03",$BQ'.$cc.',0)')
                                    ->setCellValue("BY$cc", '=IF($AR'.$cc.'="04",$BQ'.$cc.',0)')
                                    ->setCellValue("BZ$cc", '=IF($AR'.$cc.'="05",$BQ'.$cc.',0)')
                                    ->setCellValue("CA$cc", '=IF($AR'.$cc.'="06",$BQ'.$cc.',0)')
                                    ->setCellValue("CB$cc", '=IF($AR'.$cc.'="07",$BQ'.$cc.',0)')
                                    ->setCellValue("CC$cc", '=IF($AR'.$cc.'="08",$BQ'.$cc.',0)')
                                    ->setCellValue("CD$cc", '=IF($AR'.$cc.'="09",$BQ'.$cc.',0)')
                                    ->setCellValue("CE$cc", '=IF($AR'.$cc.'="10",$BQ'.$cc.',0)')
                                    ->setCellValue("CF$cc", '=IF($AR'.$cc.'="11",$BQ'.$cc.',0)')
                                    ->setCellValue("CG$cc", '=IF($AR'.$cc.'="12",$BQ'.$cc.',0)')
                                    ->setCellValue("CH$cc", '=IF(AND(MONTH($BK'.$cc.')=1,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CI$cc", '=IF(AND(MONTH($BK'.$cc.')=2,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CJ$cc", '=IF(AND(MONTH($BK'.$cc.')=3,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CK$cc", '=IF(AND(MONTH($BK'.$cc.')=4,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CL$cc", '=IF(AND(MONTH($BK'.$cc.')=5,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CM$cc", '=IF(AND(MONTH($BK'.$cc.')=6,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CN$cc", '=IF(AND(MONTH($BK'.$cc.')=7,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CO$cc", '=IF(AND(MONTH($BK'.$cc.')=8,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CP$cc", '=IF(AND(MONTH($BK'.$cc.')=9,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CQ$cc", '=IF(AND(MONTH($BK'.$cc.')=10,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CR$cc", '=IF(AND(MONTH($BK'.$cc.')=11,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CS$cc", '=IF(AND(MONTH($BK'.$cc.')=12,$BR'.$cc.'=""),$BL'.$cc.'-$BN'.$cc.',0)')
                                    ->setCellValue("CT$cc", '=IF(AND(MONTH($O'.$cc.')=1,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("CU$cc", '=IF(AND(MONTH($O'.$cc.')=2,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("CV$cc", '=IF(AND(MONTH($O'.$cc.')=3,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("CW$cc", '=IF(AND(MONTH($O'.$cc.')=4,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("CX$cc", '=IF(AND(MONTH($O'.$cc.')=5,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("CY$cc", '=IF(AND(MONTH($O'.$cc.')=6,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("CZ$cc", '=IF(AND(MONTH($O'.$cc.')=7,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("DA$cc", '=IF(AND(MONTH($O'.$cc.')=8,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("DB$cc", '=IF(AND(MONTH($O'.$cc.')=9,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("DC$cc", '=IF(AND(MONTH($O'.$cc.')=10,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("DD$cc", '=IF(AND(MONTH($O'.$cc.')=11,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("DE$cc", '=IF(AND(MONTH($O'.$cc.')=12,$BO'.$cc.'="x"),$BL'.$cc.',0)')
                                    ->setCellValue("DF$cc", '=IF(AND($AR'.$cc.'="01",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DG$cc", '=IF(AND($AR'.$cc.'="02",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DH$cc", '=IF(AND($AR'.$cc.'="03",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DI$cc", '=IF(AND($AR'.$cc.'="04",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DJ$cc", '=IF(AND($AR'.$cc.'="05",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DK$cc", '=IF(AND($AR'.$cc.'="06",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DL$cc", '=IF(AND($AR'.$cc.'="07",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DM$cc", '=IF(AND($AR'.$cc.'="08",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DN$cc", '=IF(AND($AR'.$cc.'="09",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DO$cc", '=IF(AND($AR'.$cc.'="10",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DP$cc", '=IF(AND($AR'.$cc.'="11",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)')
                                    ->setCellValue("DQ$cc", '=IF(AND($AR'.$cc.'="12",$BD'.$cc.'>0,$BE'.$cc.'>0),$BD'.$cc.'+$BE'.$cc.',0)');

                                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $sheet);
                                $objWriter->save($adminpj);
                                break;
                            }
                        }
                        break;
                    }
                    return array('status' => true);
                } else {
                    return array('status' => false, 'customer' => $shipping['SIGLA']);
                }
            }

            return false;
        }

        /*
        * Escreve o custo na Conta Transitória
        */
        public function writeCT($shipping) {
            if ( $shipping['write_ct'] ) {
                $qtde_titulos = array('INDIVIDUAL' => 0);

                foreach ( $shipping['PACOTES'] as $key => $package ) {
                    if ( $package['DESC'] == 'Individual' ) {
                        $qtde_titulos['INDIVIDUAL'] += $package['QTDE_TITULOS'];
                    } else {
                        $qtde_titulos['UNICO'][$key] = $package['QTDE_TITULOS'];
                    }    
                }
                
                $conta_transitoria = "C:\\contatransitoria\\".strtolower($shipping['SIGLA'])."\\index-".strtoupper($shipping['SIGLA']).".xls";
                $customerIsRepasse = $this->customer->IsRepasse($shipping['SIGLA']);
                $isentoDebitoAutomatico = $this->customer->isentoDebitoAutomatico($shipping['SIGLA']);
                $shouldPayDebitoAutomatico = !$customerIsRepasse && !$isentoDebitoAutomatico;

                if ( file_exists($conta_transitoria) ) {
                    $sheet = PHPExcel_IOFactory::identify($conta_transitoria);
                    $objReader = PHPExcel_IOFactory::createReader($sheet);
                    $objPHPExcel = $objReader->load($conta_transitoria);
                    //Loop for find info
                    foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) {
                        $highestRow = $worksheet->getHighestRow();
                        //Loop in all cells of the sheet
                        for ( $cc = 11; $cc < $highestRow; $cc++ ) {
                            if ( $worksheet->getCellByColumnAndRow(1, $cc)->getValue() == "" ) {
                                $objWorksheet = $objPHPExcel->getActiveSheet();
                                // Function should returns the formated date for excel
                                $dateProc = date('d/m/Y');
                                //Verifica se a data é útil
                                //Se não for, adiciona quantidade de dias para que se torne dia útil
                                $qtdeDaysToAdd = 0;
                                $weekDay = date('l', strtotime(Util::FmtDate($dateProc, '23')));
                                if ( $weekDay === 'Saturday' ) {
                                    $qtdeDaysToAdd = 2;
                                } else if ( $weekDay === 'Sunday' ) {
                                    $qtdeDaysToAdd = 1;
                                }
                                $dateProc = date('d/m/Y', strtotime(Util::FmtDate($dateProc, '23'). " + $qtdeDaysToAdd days"));
                                $getDt = getFmtedDate($dateProc);
                                // Declarating vars of functions @getFmtedDate
                                $t_date = $getDt[0];
                                $qtde_total = $qtde_titulos['INDIVIDUAL'] + (isset($qtde_titulos['UNICO']) ? array_sum($qtde_titulos['UNICO']) : 0);
                                getCellData($objWorksheet, $cc++, $t_date, null, $qtde_total, null, $this->taxes['IMPRESSAO'], 'remessa', 'Impressões/envelopamentos', null, null, null, null, null, array(), null);
                                if ( $qtde_titulos['INDIVIDUAL'] > 0 ) { 
                                    getCellData($objWorksheet, $cc++, $t_date, null, $qtde_titulos['INDIVIDUAL'], null, $this->taxes['ENTREGA_INDIVIDUAL'], 'remessa', 'Entrega Individual', null, null, null, null, null, array(), null);
                                }
                                if ( isset($qtde_titulos['UNICO']) && count($qtde_titulos['UNICO']) > 0 ) { 
                                    foreach ( $qtde_titulos['UNICO'] as $qtde ) {
                                        if ( $qtde > 0 ) {
                                            getCellData($objWorksheet, $cc++, $t_date, null, $qtde, 'unico', $this->taxes['ENTREGA_UNICA'], 'remessa', 'Entrega Única de Documentos', null, null, null, null, null, array(), null);
                                        } 
                                    }
                                }
                                //Se o cliente não for repasse, adiciona tarifa de débito em conta
                                if ( $shouldPayDebitoAutomatico ) {
                                    getCellData($objWorksheet, $cc++, $t_date, null, '1', 'unico', $this->taxes['DEBITO_CONTA'], 'remessa', 'Tarifa de Débito em Conta', null, null, null, null, null, array(), null);
                                }
                                //Creating the writer object
                                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $sheet);
                                $objWriter->setPreCalculateFormulas(false);
                                $objWriter->save($conta_transitoria);
                                break;
                            }
                        }
                    }
                    return array('status' => true);
                } else {
                    return array('status' => false, 'customer' => $shipping['SIGLA']);
                }
            }

            return false;
        }

        /*
        * Envia o email de processamento
        */
        public function send($data) {
            if ( $data['send_mail'] ) {
                $sigla = $data['SIGLA'];
                $isRepasse = $this->customer->IsRepasse($sigla);
                $isentoDebitoAutomatico = $this->customer->isentoDebitoAutomatico($sigla);
                $shouldPayDebitoAutomatico = !$isRepasse && !$isentoDebitoAutomatico;
                $customer_mail = $this->customer->getMail($sigla);
                if ( $customer_mail == "" ) return false;
                $customer_name = $this->customer->getSacadoNameBySigla($sigla);
                $customer_doc = $this->customer->GetDocSacBySigla($sigla);
                $tipodoc = $customer_doc['TPDOC'] == '1' ? 'CPF' : 'CNPJ';
                $mask = $customer_doc['TPDOC'] == '1' ? '%s%s%s.%s%s%s.%s%s%s-%s%s' : '%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s';
                $docsac = vsprintf($mask, str_split($customer_doc['DOC']));
                $pathname = $this->customer->GetPathNameBySigla($sigla);
                $meses = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
                $date = date('d').'-'.date('m').'-'.date('Y');
                $checkPath = "C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\".strtoupper($pathname)."\\Recibos";
                $filename = "$checkPath\\$sigla - Boletos - $date.pdf";
                
                //Custos
                $valor_total = $total_outros = $custo_impressao = $custo_entrega = $qtde_titulos = $qtde_individuais = $qtde_unico = 0;
                
                foreach ( $data["PACOTES"] as $pacote ) {
                    $valor_total += $pacote['VALOR_TOTAL'];
                    $qtde_titulos += $pacote['QTDE_TITULOS'];
                    $custo_impressao += $pacote['CUSTO']['IMPRESSAO'];
                    $custo_entrega += $pacote['CUSTO']['ENTREGA'];
                    if ( $pacote['DESC'] !== 'Individual' ) {
                        if ( count($pacote['BANCO']) > 0 ) {
                            $qtde_unico++;
                        }
                    } else {
                        $qtde_individuais = $pacote['QTDE_TITULOS'];
                    }
                }
                //Formatando valores
                $menor_vencimento = Util::FmtDate($data['MENOR_VCTO'], '20');
                $maior_vencimento = Util::FmtDate($data['MAIOR_VCTO'], '20');
                $valor_custo_total = $custo_impressao + $custo_entrega;
                $valor_custo_total += $shouldPayDebitoAutomatico ? $this->taxes['DEBITO_CONTA'] : 0;
                $valor_custo_total = number_format($valor_custo_total, 2, ',', '.');
                $custo_impressao = number_format($custo_impressao, 2, ',', '.');
                $custo_entrega = number_format($custo_entrega, 2, ',', '.');
                $valor_total += $shouldPayDebitoAutomatico ? $this->taxes['DEBITO_CONTA'] : 0;
                $valor_total = number_format($valor_total, 2, ',', '.');
                $tarifa_deb_automatico = $shouldPayDebitoAutomatico ? number_format($this->taxes['DEBITO_CONTA'], 2, ',', '.') : null;
                $total_outros += $shouldPayDebitoAutomatico ? $this->taxes['DEBITO_CONTA'] : 0;
                $custo_entrega_individual = $qtde_individuais > 0 ? number_format($qtde_individuais * $this->taxes['ENTREGA_INDIVIDUAL'], 2, ',', '.') : 0;
                $custo_entrega_unica = $qtde_unico > 0 ? number_format($qtde_unico * $this->taxes['ENTREGA_UNICA'], 2, ',', '.') : 0;
                $texto_entrega_individual = $qtde_individuais > 0 
                    ? "<span>Entregas individuais................ $qtde_individuais x R$ ".number_format($this->taxes['ENTREGA_INDIVIDUAL'], 2, ',', '.')." = R$ $custo_entrega_individual</span><br />" : "";
                $texto_entrega_unica = $qtde_unico > 0 
                    ? "<span>Entregas únicas..................... $qtde_unico x R$ ".number_format($this->taxes['ENTREGA_UNICA'], 2, ',', '.')." = R$ $custo_entrega_unica</span><br />" : "";
                $texto_tarifa_deb_automatico = is_null($tarifa_deb_automatico) ? '' : "<span>Tarifa de Débito Automático......... 1 x R$ ".number_format($this->taxes['DEBITO_CONTA'], 2, ',', '.')."</span><br />";
                /*
                * Gera o recibo
                */
                $dateProc = date('d/m/Y');
                $dateProcExplode = explode('/', $dateProc);
                $dateProc = $dateProcExplode[0]." de ".$meses[(int) $dateProcExplode[1] - 1]." de ".$dateProcExplode[2];
                $dompdf = new Dompdf($options);
                $html = "<center><h1>Recibo</h1></center>
                    <p align='justify'>
                        Recebemos de $customer_name, $tipodoc $docsac,
                        as importâncias abaixo discriminadas, referentes às despesas de <b>confecção e entrega de boletos bancários</b>,
                        e repassamos as mesmas para as empresas a seguir discriminadas.
                    </p>
                    <p>
                        <b>GRÁFICA: DIGILOC COMÉRCIO E LOCAÇÃO DE EQUIPAMENTOS DE INFORMÁTICA - ME (CNPJ: 22.609.826/0001-95)</b><br /><br />
                        <span>Impressões/envelopamentos........... $qtde_titulos x R$ ".number_format($this->taxes['IMPRESSAO'], 2, ',', '.')." = R$ $custo_impressao</span><br />
                    </p>
                    <p>
                        <b>CORREIOS: SINAI SERVIÇOS LTDA. (CNPJ: 02.730.052/0001-49)</b><br /><br />
                        $texto_entrega_individual
                        $texto_entrega_unica
                        <span>Total............................... R$ $custo_entrega</span><br />
                    </p>
                    <p>
                        <b>Outros</b><br /><br />
                        $texto_tarifa_deb_automatico
                        <span>Total............................... R$ ".number_format($total_outros, 2, ',', '.')."</span><br />
                    </p>
                    <p>
                        <b>Total de despesas................... R$ $valor_custo_total</b>
                    </p>
                    <p>
                        <br /><br /><br /><br /><br />
                        <center>
                            Fortaleza (CE), $dateProc<br /><br />                            
                            <i>SETYDEIAS SERVIÇOS LTDA<br />
                            CNPJ: 03.377.700/0001-98</i>
                        </center>
                    </p>";
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->set_option('defaultFont', 'Courier');
                $dompdf->render();
                $output = $dompdf->output();
                
                //Tenta criar o arquivo, caso haja algum erro, tenta criar o diretório e criar o arquivo novamente
                if ( !file_put_contents($filename, $output) ) {
                    mkdir($checkPath, 0777, true) ? file_put_contents($filename, $output) : $not_created[] = $filename;
                }

                $mail = new PHPMailer;
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP(); // Set mailer to use SMTP
                $mail->Host = $this->mail_smtp_host; // Specify main and backup SMTP servers
                $mail->SMTPAuth = true; // Enable SMTP authentication
                $mail->Username = $this->email; // SMTP username
                $mail->Password = $this->mail_password; // SMTP password
                $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
                $mail->Port = $this->mail_port; // TCP port to connect to
                $mail->SMTPDebug = false;
                $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

                //Recipients
                $mail->setFrom($this->email, $this->mail_name);
                //$mail->addAddress("brunodeveloper18@gmail.com", $customer_name); // Add a recipient
                $mail->addAddress($customer_mail, $customer_name); // Add a recipient
                $mail->addCC('setydeias@setydeias.com.br');   
                
                //Attachments
                if ( $data['attach'] ) {
                    $mail->addAttachment($filename, 'Recibo.pdf');
                }

                //Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = "Processamento de cobrança - $sigla - Remessa: {$data['SEQUENCIAL']}";

                //Corpo do email
                $body = "<html><head>";
                $body .= "<meta charset='UTF-8' />";
                $body .= "<style>";
                $body .= "* {font-family: 'Verdana', sans-serif;}";
                $body .= "body {background: #f4f4f4;}";
                $body .= "span {display: block;padding:10px 0;}";
                $body .= "h3 {margin: 20px 0;color:#069;}";
                $body .= "</style>";
                $body .= "</head><body>";
                $body .= "<a href='http://setydeias.com.vc' target='_blank'><img src='http://setydeias.com.br/comercial/images/mail-header.jpg' alt='Cabeçalho' /></a><br/><br/>";
                $body .= "<section style='width:700px'>";
                $body .= "<h3>Dados da remessa processada em ".date('d/m/Y')." às ".date('H')."h".date('i')."m</h3>";
                $body .= "<section style='display:block;width:100%;float:left;'>";
                $body .= "<section style='border:1px solid #ccc;border-radius:5px;width:200px;height:200px;text-align:center;float:left;margin:0 10px 10px 0;'><span><img src='http://setydeias.com.br/comercial/images/qtde.png' width='40' alt='Quantidade de títulos' /></span><span style='width:100%;color:#069;font-size:16px;'>Quantidade de títulos</span><span style='width:100%;color:#30BF5B;font-size:20px;'>$qtde_titulos</span></section>";
                $body .= "<section style='border:1px solid #ccc;border-radius:5px;width:200px;height:200px;text-align:center;float:left;margin:0 10px 10px 0;'><span><img src='http://setydeias.com.br/comercial/images/calendario.png' width='40' alt='Vencimentos' /></span><span style='width:100%;color:#069;font-size:16px;'>Vencimentos entre</span><span style='width:100%;color:#30BF5B;font-size:20px;'>$menor_vencimento e $maior_vencimento</span></section>";
                $body .= "<section style='border:1px solid #ccc;border-radius:5px;width:200px;height:200px;text-align:center;float:left;margin:0 10px 10px 0;'><span><img src='http://setydeias.com.br/comercial/images/money-bag.png' width='40' alt='Valor total' /></span><span style='width:100%;color:#069;font-size:16px;'>Valor total</span><span style='width:100%;color:#30BF5B;font-size:20px;'>R$ $valor_total</span></section>";
                $body .= "</section>";
                $body .= "<section style='display:block;width:100%;float:left;'>";
                $body .= "<section style='border:1px solid #ccc;border-radius:5px;width:200px;height:200px;text-align:center;float:left;margin:0 10px 10px 0;'><span><img src='http://setydeias.com.br/comercial/images/print.png' width='40' alt='Custo de impressão' /></span><span style='width:100%;color:#069;font-size:16px;'>Custo de impressão</span><span style='width:100%;color:#30BF5B;font-size:20px;'>R$ $custo_impressao</span></section>";
                $body .= "<section style='border:1px solid #ccc;border-radius:5px;width:200px;height:200px;text-align:center;float:left;margin:0 10px 10px 0;'><span><img src='http://setydeias.com.br/comercial/images/send.png' width='40' alt='Custo de entrega' /></span><span style='width:100%;color:#069;font-size:16px;'>Custo de entrega</span><span style='width:100%;color:#30BF5B;font-size:20px;'>R$ $custo_entrega</span></section>";
                $body .= !is_null($tarifa_deb_automatico) ? "<section style='border:1px solid #ccc;border-radius:5px;width:200px;height:200px;text-align:center;float:left;margin:0 10px 10px 0;'><span><img src='http://setydeias.com.br/comercial/images/send.png' width='40' alt='Tarifa de Débito em Conta' /></span><span style='width:100%;color:#069;font-size:16px;'>Tarifa de Débito em Conta</span><span style='width:100%;color:#30BF5B;font-size:20px;'>R$ $tarifa_deb_automatico</span></section>" : "";
                $body .= "</section>";
                $body .= "<section style='display:block;width:100%;margin: 10px 0;'><i>- Procure manter o envio dos seus pedidos de processamento com no mínimo <b>15 dias</b> antes do vencimento mais próximo;</i></section>";
                $body .= "<section style='display:block;width:100%;margin: 10px 0;'>";
                $body .= "<i>- Para analisar o detalhamento do custo desta remessa, clique no link abaixo:</i><br />";
                $body .= "<a style='text-decoration:none;color:#069;' href=http://setydeias.com.br/contatransitoria/".strtolower($data['SIGLA']).">http://setydeias.com.br/contatransitoria/".strtolower($data['SIGLA'])."</a></section>";
                $body .= "</section>";
                $body .= "</body></html>";

                $mail->Body = $body;
                
                return $mail->send();
            }

            return false;
        }

        /*
        * Cria o arquivo dividindo os pacotes e vinculando os detalhes para seus respectivos pacotes
        */

        public function createShipp($header, $details) {

            $index = 1;
            foreach ( $details as $detail ) {
                $package_details = explode(PHP_EOL, $detail)[0]; //Detalhes do pacote (cod 04)
                $package_kind = trim(substr($package_details, 6, 40)) == "Individual" ? 'D' : 'U'; //Tipo de entrega
                $delivery = $this->getDelivery($package_kind); //Dados do entregador (cod 02)
                $package_index = str_pad($index, 3, '0', STR_PAD_LEFT); //Código do pacote
                $count_packages = str_pad(count($details), 3, '0', STR_PAD_LEFT); //Total de pacotes
                //Complemento do header
                $header_complement = $header['SIGLA'].'.'.$header['SEQ_REMESSA'].'.'.$package_index.'/';
                $header_complement .= $count_packages.'-'.$package_kind.$header['ID_PAPEL'].$header['LAYOUT_STY'];
                $header_complement .= $header['DATA_RECEBIMENTO'].$header['HORA_RECEBIMENTO'].str_pad('', 104, ' ', STR_PAD_RIGHT);
                /*
                * Registro 99 (trailer)
                */
                $qtde_linhas = str_pad(count(explode(PHP_EOL, $detail)) + 1, 10, '0', STR_PAD_LEFT); 
                //Captudando a quantidade de títulos
                $qtde_titulos = 0;
                foreach ( explode(PHP_EOL, $detail) as $record ) {
                    $record_cod = substr($record, 0, 2);
                    if ( $record_cod == '12' ) $qtde_titulos++;
                }
                $qtde_titulos = str_pad($qtde_titulos, 7, '0', STR_PAD_LEFT); 

                $registro99 = "99$qtde_titulos".str_pad('', 5, ' ', STR_PAD_RIGHT)."$qtde_linhas";
                /*
                * Criando o arquivo
                */
                //Nome do arquivo
                $fileName = $this->remProcessedDir."STYPI_".$header['SIGLA'].$header['SEQ_REMESSA'].'.'.$package_index.".txt";
                //Se não houver registros passa para o próximo pacote
                if ( count(explode(PHP_EOL, $detail)) <= 2 ) continue;
                //Handle
                $fp = fopen($fileName, 'a');
                fwrite($fp, substr(implode('', $header), 0, 277).$header_complement.PHP_EOL);
                fwrite($fp, $delivery.PHP_EOL);
                fwrite($fp, $detail);
                fwrite($fp, $registro99);
                fclose($fp);
                //Incrementa o índice
                $index++;
            }
        }

        /*
        * Obtém o header do arquivo
        */
        public function getHeader() {
            
            $file_contents = file($this->file)[0];
            $header = array(
                'COD_REGISTRO' => substr($file_contents, 0, 2),
                'SIGLA' => substr($file_contents, 2, 3),
                'CODIGO_CEDENTE' => substr($file_contents, 5, 5),
                'TIPO_DOC_CEDENTE' => substr($file_contents, 10, 1),
                'DOC_CEDENTE' => substr($file_contents, 11, 14),
                'NOME_CEDENTE' => substr($file_contents, 25, 50),
                'ENDERECO_CEDENTE' => substr($file_contents, 75, 60),
                'DICA_ENDERECO' => substr($file_contents, 135, 50),
                'CIDADE_CEDENTE' => substr($file_contents, 185, 27),
                'UF_CEDENTE' => substr($file_contents, 212, 2),
                'CEP_CEDENTE' => substr($file_contents, 214, 8),
                'SEQ_REMESSA' => substr($file_contents, 222, 5),
                'SITE' => str_pad($this->customer->GetSite(substr($file_contents, 2, 3)), 50, ' ', STR_PAD_RIGHT),
                'ID_PAPEL' => substr($file_contents, 296, 2),
                'LAYOUT_STY' => substr($file_contents, 298, 6),
                'DATA_RECEBIMENTO' => substr($file_contents, 304, 6),
                'HORA_RECEBIMENTO' => substr($file_contents, 310, 6)
            );

            return $header;
        }

        /*
        * Obtém os pacotes do arquivo
        */
        public function getPackages() {
            $file_contents = file($this->file);
            $packages = array();
            
            foreach ( $file_contents as $record ) {
                $cod_record = substr($record, 0, 2); //Código do registro (01, 04, 06, 08, 10, 12)
                $package_id = substr($record, 2, 3);
                if ( $cod_record == '04' ) $packages[$package_id] = $record;
                if ( $cod_record > 4 ) break;
            }
            
            ksort($packages); //Ordena o array pelas chaves
            return $packages;
        }
 
        /*
        * Obtém o entregador de acordo com o tipo de pacote
        */
        public function getDelivery($package_kind) {
            $strategy = new StrategyDelivery($package_kind);
            $delivery = implode('', $strategy->getDelivery());
            return $delivery;
        }

        /*
        * Obtém os registros detalhe dos arquivos
        */

        public function getDetailRecords($packages) {
            $file_contents = file($this->file);
            $detail_records = array();
            $record_amount = "";
            //Separando os registros de acordo com o id do pacote
            foreach ( $file_contents as $record ) {
                $cod_record = substr($record, 0, 2); //Código do registro (01, 04, 06, 08, 10, 12)
                if ( $cod_record == '06' ) $record_amount .= $record;
                if ( $cod_record == '08' ) $record_amount .= $record;
                if ( $cod_record == '10' ) $record_amount .= $record;
                if ( $cod_record == '12' ) {
                    $record_amount .= $record;
                    //Captura o ID do pacote
                    $package_id = substr($record, 2, 3);
                    if ( array_key_exists($package_id, $packages) ) {
                        $packages[$package_id] .= $record_amount;
                    }
                    //Zera a string para voltar ao início do loop
                    $record_amount = "";
                }
            }
            
            return $packages;
        }

        public function getShippingInfo($header, $details) {
            $data_recebimento = Util::FmtDate($header['DATA_RECEBIMENTO'], '13');
            $hora_recebimento = substr($header['HORA_RECEBIMENTO'], 0, 2).':'.substr($header['HORA_RECEBIMENTO'], 2, 2).':'.substr($header['HORA_RECEBIMENTO'], 4, 2);
            $isRepasse = $this->customer->IsRepasse($header['SIGLA']);
            $isentoDebitoAutomatico = $this->customer->isentoDebitoAutomatico($header['SIGLA']);
            $dateProc = date('d/m/Y');
            //Verifica se a data é útil
            //Se não for, adiciona quantidade de dias para que se torne dia útil
            $qtdeDaysToAdd = 0;
            $weekDay = date('l', strtotime(Util::FmtDate($dateProc, '23')));
            if ( $weekDay === 'Saturday' ) {
                $qtdeDaysToAdd = 2;
            } else if ( $weekDay === 'Sunday' ) {
                $qtdeDaysToAdd = 1;
            }
            $dateProc = date('d/m/Y', strtotime(Util::FmtDate($dateProc, '23'). " + $qtdeDaysToAdd days"));

            $data = array(
                'SIGLA' => $header['SIGLA'],
                'SEQUENCIAL' => $header['SEQ_REMESSA'],
                'DATA_RECEBIMENTO' => "$data_recebimento às $hora_recebimento",
                'REPASSE' => $isRepasse,
                'ISENTO_TARIFA_DEBITO_AUTOMATICO' => $isentoDebitoAutomatico,
                'DATA_PAGAMENTO' => $dateProc,
                'PROCESSADO_POR' => $_SESSION['nome'],
                'PACOTES' => array(),
                'CUSTO_TOTAL_IMPRESSAO' => 0, 
                'BANCO' => array()
            );
            //Se o cliente não for repasse
            //Retornar o valor da tarifa de débito em conta
            if ( !$data['REPASSE'] ) {
                $data['TARIFA_DEBITO_CONTA'] = number_format($this->taxes['DEBITO_CONTA'], 2, '.', '');
            }
            //Para o processamento de datas de vencimento
            $maior_vencimento = 0;
            //Individualiza o pacote
            foreach ( $details as $detail ) {
                $package_id = null; //Id do pacote
                $valorTotal = 0; //Valor total do pacote
                $counter = 0; //Contador da quantidade de títulos
                $descricao = trim(substr($detail, 6, 40));
                $convenio = "";
                $banco = $bank_amount = array();
                $aux = true;
                //Loop nos pacotes encontrados
                $detail = explode(PHP_EOL, $detail);
                foreach ( $detail as $record ) {
                    $cod_record = substr($record, 0, 2);
                    //Dados do título
                    if ( $cod_record == '12' ) {
                        $package_id = substr($record, 2, 3);
                        //Valor
                        $valor = substr($record, 301, 13) / 100;
                        $valorTotal += $valor;
                        //Quantidade
                        $counter++;
                        //Vencimento
                        $vencimento = Util::FmtDate(substr($record, 256, 6), '10');
                        if ($aux) {
                            $maior_vencimento = $vencimento;
                            $menor_vencimento = $vencimento;
                            $aux = false;
                        } else {
                            $maior_vencimento = $vencimento > $maior_vencimento ? $vencimento : $maior_vencimento;
                            $menor_vencimento = $vencimento < $menor_vencimento ? $vencimento : $menor_vencimento;
                        }
                        //Bancos
                        $cod_banco = substr($record, 314, 3);
                        if ( $cod_banco == 001 ) $convenio = substr($record, 269, 7);
                        if ( $cod_banco == 104 ) $convenio = trim(substr($record, 323, 7));
                        
                        $banco[$cod_banco][] = $convenio;
                    }
                }
                $aux = true;
                //Dados individualizados
                $data['MENOR_VCTO'] = $menor_vencimento;
                $data['MAIOR_VCTO'] = $maior_vencimento;
                $data['PACOTES'][$package_id]['CUSTO']['IMPRESSAO'] = $counter * $this->taxes['IMPRESSAO'];
                if ( utf8_encode($descricao) == "Individual" ) {
                    $data['PACOTES'][$package_id]['CUSTO']['ENTREGA'] = $counter * $this->taxes['ENTREGA_INDIVIDUAL'];
                } else {
                    $data['PACOTES'][$package_id]['CUSTO']['ENTREGA'] = $counter > 0 ? $this->taxes['ENTREGA_UNICA'] * 1 : 0;
                }
                $data['PACOTES'][$package_id]['VALOR_TOTAL'] = $valorTotal;
                $data['PACOTES'][$package_id]['QTDE_TITULOS'] = $counter;
                $data['PACOTES'][$package_id]['DESC'] = utf8_encode($descricao);
                $data['PACOTES'][$package_id]['BANCO'] = $banco;
                //Contador de convênios
                foreach ( $data['PACOTES'][$package_id]['BANCO'] as $key => $banco ) $data['PACOTES'][$package_id]['BANCO'][$key] = array_count_values($banco);
            }
            
            //Individualizando os dados bancários
            foreach ( $data['PACOTES'] as $pacote ) {
                foreach ( $pacote['BANCO'] as $key => $banco ) {
                    foreach ( $banco as $convenio => $qtde ) {
                        !isset ( $bank_amount[$key][$convenio] ) ? $bank_amount[$key][$convenio] = $qtde : $bank_amount[$key][$convenio] += $qtde;
                    }
                }
            }
            
            $data['BANCO'] = $bank_amount;
            return $data;
        }

    } 