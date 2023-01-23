<?php
    error_reporting(0);
    include_once 'dao/AnalysisRecordDAO.class.php';
    include_once 'Mail.class.php';
    include_once 'Customer.class.php';
    include_once 'constants/codigos_movimentacao.php';

    /**
     * AnalysisRecord
     * 
     * Inicia o processo de inserção e análise de remessas/retornos processados
     * Envia emails para o cliente com títulos rejeitados
     * @author Bruno Pontes <brunodeveloper18@gmail.com>
     */
    class AnalysisRecord {

        public function __construct() {
            $this->dao = new AnalysisRecordDAO();
            $this->mail = new Mail();
            $this->customer = new Customer();
            $this->processed_by = $_SESSION['nome'];
            //Códigos de movimentação para retornos
            $this->codMovimentoRetorno = array(
                '02' => 'REGISTRADO', 
                '03' => 'REJEITADO', 
                '06' => 'LIQUIDAÇÃO',
                '09' => 'BAIXADO (Saiu do sistema bancário)', 
                '14' => 'ALTERAÇÃO DE VENCIMENTO',
                '17' => 'LIQUIDAÇÃO SEM REGISTRO'
            );
            //Códigos de movimentação para remessas
            $this->codMovimentoRemessa = array(
                '01' => 'SOLICITAÇÃO DE REGISTRO',
                '02' => 'SOLICITAÇÃO DE BAIXA DO TÍTULO',
                '06' => 'SOLICITAÇÃO DE ALTERAÇÃO DO VENCIMENTO' 
            );
            //Ocorrências não identificáveis
            $this->occurrencesNotFindables = array('', '00');
        }

        /**
		 * Inicia o processo de inserção/análise de títulos
         * 
         * @param      array      $data
         * @return     void
		 */
		public function init(array $data) {
            try {
                $rejected_records = $this->InsertRecordData($data);
                $occurrences = $this->getOccurrencesByOurNumber($rejected_records);
                
                if ( $occurrences ) {
                    //Verifica se existe confirmação de baixa do título
                    //Caso haja, o email mesmo não será enviado
                    $sendable_occurrences = $this->findSendableOccurrences($occurrences);
                    $has_sendable_occurrences = count($sendable_occurrences) > 0;
                    
                    if ( $has_sendable_occurrences ) {
                        $this->sendMails($sendable_occurrences);
                    }
                }
            } catch ( Exception $e ) {
                echo $e->getMessage();
            }
        }

        /**
         * Retorna array caso $needle seja encontrado
         *
         * @param    string   $needle
         * @param    array    $haystack
         * @return   array
         */
        public function array_search_multidimensional($needle, $haystack) {
            return array_filter($haystack, function ($data) use ($needle) {
                return in_array($needle, $data);
            });
        }

        /**
         * Retorna apenas as ocorrências que podem ser enviadas para o cliente
         * 
         * @param      array      $occurrences
         * @return     array
         */
        public function findSendableOccurrences(array $occurrences) {
            $sendable_occurrences = array();

            foreach ( $occurrences as $customer => $data ) {
                foreach ( $data as $nosso_numero => $occurrence ) {
                    $sendable = true;

                    /*
                    * Verifica se a matriz de ocorrências contém solicitação de registro
                    * se não houver, o nosso número não é apto a ser enviado
                    */

                    $search_register = $this->array_search_multidimensional("01", $occurrence);
                    $has_register = count($search_register) > 0;

                    /*
                    * Verifica se a matriz de ocorrências contém solicitação de baixa
                    * se houver, o nosso número não é apto a ser enviado
                    */

                    $search_down = $this->array_search_multidimensional("09", $occurrence);
                    $has_down = count($search_down) > 0;

                    if ( !$has_register || $has_down ) {
                        $sendable = false;
                    } 

                    if ( $sendable ) {
                        $sendable_occurrences[$customer][$nosso_numero] = $data[$nosso_numero];
                    }
                }
            }
            
            return $sendable_occurrences;
        }

        /**
         * Retorna as instruções que serão indexadas ao email de acordo com o motivo da rejeição
         * 
         * @param       array      $reasons
         * @return      string
         */
        public function getInstructionByReason(array $reasons) {
            $reason_description = array();

            foreach ( $reasons as $reason ) {
                $reason = !is_null($reason) ? $reason : "Ocorrência não encontrada";

                switch ( $reason ) {
                    case 'Movimento para Título Não Cadastrado':
                        $reason_description[] = "
                            <b>$reason</b>

                            * Caso <b>não</b> queira manter o histórico do título basta excluir, gerar um novo com a mesma competência, registrar e disponibilizar para o cliente;
                            * Caso queira manter o título no histórico do cliente siga os passos abaixo:
                            
                            01. Consultar o titulo em questão, alterar o valor para R$0,00 e salvar;
                            02. Após alterar, selecione o titulo, clique com o botão direito do mouse e selecione a opção Baixar manual;
                            03. Agora com a tela de baixa aberta, clique no botão Baixar e confirme a baixa no botão Sim;
                            04. Informe se deseja ou não imprimir o Recibo;
                            05. Gere um novo título com a mesma competência do título baixado;
                            06. Após gerado, consulte e selecione, clique com o direito e em Alterar;
                            07. Selecione o Lançamento, clique em Alterar. Se tiver desconto informe a data, caso constário informe zero Desconto e Salve;
                            08. Selecione e altere o Vencimento e clique em Salvar;
                            09. Selecione o titulo novamente, clique com o direito e selecione a opção Anotações;
                            10. Será exibidos campos de anotações para a Pessoa e para o Titulo. Vincule informações do titulo baixado, mantendo assim 
                            um histórico de movimentações;
                            11. Atualizar todos os dados na Web;
                            12. Registre gerando o arquivo de remessa através do SetRem.
                        ";
                    break;
                    case 'Data do Desconto Inválida':
                        $reason_description[] = "
                            <b>$reason</b>

                            01. Consultar o título através do nosso número no menu Cobrança/Arrecadação;
                            02. Na tela Controle de cobrança - FILTRO, informe o campo Nosso Número e clique no botão Filtrar;
                            03. Selecione qualquer campo do título, clique com o direito do mouse e clique em Alterar;
                            04. Com as informações exibidas, selecione o Lançamento a qual será dado o desconto conforme sua Descrição, clique com o direito do mouse e em seguida Alterar;
                            05. Será exibido a tela Alterar Lançamento. Informe a data correta ao lado do campo Desconto e clique em Salvar;
                            06. Altere o vencimento e em seguida digite Enter;
                            07. Agora Salve as alterações feitas no botão Salvar ao lado do Imprimir, confirme a alteração no Sim e logo em seguida em Ok;
                            08. Registre a alteração através do SetRem.
                        ";
                    break;
                    case 'Valor do Título Inválido':
                        $reason_description[] = "
                            <b>$reason</b>
                            
                            01. Consultar o título através do nosso número no menu Cobrança/Arrecadação;
                            02. Na tela Controle de cobrança - FILTRO, informe o campo Nosso Número e clique no botão Filtrar;
                            03. Selecione qualquer campo do título, clique com o direito do mouse e clique em Alterar;
                            04. Com as informações exibidas, selecione o Lançamento com o Valor R$:0,00, clique com o direito do mouse e em seguida Alterar;
                            05. Será exibido a tela Alterar Lançamento. Informe o valor a ser cobrado e os demais campos e clique em Salvar;
                            06. Altere o vencimento e em seguida digite Enter;
                            07. Agora Salve as alterações feitas no botão Salvar ao lado do Imprimir, confirme a alteração no Sim e logo em seguida em Ok;
                            08. Registre a alteração através do SetRem.
                        ";
                    break;
                    default:
                        $reason_description[] = "
                            <b>$reason</b>
                            
                            01. Analisar em que lado é gerada a o motivo da rejeição (SetyWare x Painel);
                            02. Analisar arquivo(s) gerado(s) pelo SetyWare conforme o nosso número;
                            03. Analisar arquivo(s) gerado(s) pelo Painel Administrativo Setydeias
                            no processamento das remessas;
                            04. Corrigir manualmente, registrar ou descartar dependendo da conclusão relacionada ao histórico.

                            Ex: 

                            1. Descartar \"Entrada para Título já Cadastrado\" quando o SetyWare gera
                            dois arquivos idênticos.

                            2. Corrigir manualmente e registrar quando \"Endereço do Pagador Não Informado\" é
                            informado, mas por conta de caracteres o banco rejeita.
                        ";
                    break;
                }
            }

            return implode("\n", $reason_description);
        }

        /**
         * Insere os regitros do processamento na tabela
         * 
         * @param      array           $data
         * @return     mixed           Retorna array caso haja títulos rejeitados
         */
		public function InsertRecordData(array $data) {
            try {
                $rejected_records = array();

                if ( count($data) > 0 ) {
                    for ( $i = 0; $i < count($data); $i++ ) {

                        $dataset = array();
                        
                        foreach ( $data[$i] as $key => $value ) {
                            if ( $key === 'SIGLA_CLIENTE' && $value === 'INDEFINIDO' ) {
                                continue 2;
                            }

                            if ( $key === 'COD_MOVIMENTO' && $value === REJEITADO ) {
                                $rejected_records[] = $data[$i]['NOSSO_NUMERO'];
                            }
                            $dataset[$key] = $value;
                        }

                        $this->dao->insertRecords($dataset);
                    }
                }
                
                return $rejected_records;
            } catch ( Exception $e ) {
                echo $e->getMessage();
            }
		}
        
        /**
         * Retorna todas as ocorrências do título
         */
        public function getOccurrencesByOurNumber(array $data) {
            if ( count($data) > 0 ) {
                return $this->dao->getRecordInfo($data);
            }

            return false;
        }

        /**
         * Envia os emails para os clientes
         */
        public function sendMails($occurrences) {
            $send_to_customer = $send_to_support = array();
            foreach ( $occurrences as $customer => $data ) {
                //Filtra os títulos que serão enviados ao cliente e ao suporte
                foreach ( $data as $key => $value ) {
                    $has_moviment_not_inserted = count($this->array_search_multidimensional('Movimento para Título Não Cadastrado', $value)) > 0;
                    $has_invalid_discount = count($this->array_search_multidimensional('Data do Desconto Inválida', $value)) > 0;
                    $has_invalid_value = count($this->array_search_multidimensional('Valor do Título Inválido', $value)) > 0;
                    
                    $has_moviment_not_inserted || $has_invalid_discount || $has_invalid_value 
                        ? $send_to_customer[$customer][$key] = $value
                        : $send_to_support[$customer][$key] = $value;
                }

                //Formata os títulos que serão enviados aos clientes
                $has_mail_to_customer = count($send_to_customer) > 0;
                if ( $has_mail_to_customer ) {
                    $nosso_numero_ref = "";
                    
                    foreach ( $send_to_customer as $customer => $nosso_numero ) {
                        $registro = "<!doctype><html>";
                        $registro .= "<head>";
                        $registro .= "<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet'>";
                        $registro .= "</head>";
                        $registro .= "<body style=\"font-family: 'Open Sans', Roboto, sans-serif;\">";
                        $registro .= "<img src='http://setydeias.com.br/comercial/images/mail-header.jpg' /><br/><br/>";
                        $registro .= "<h1 style='font-size: 150%;margin: 1.5em 0;'>Título(s) Rejeitado(s)</h1>";
                        $reject_reasons = array();

                        foreach ( $nosso_numero as $key => $occurrences ) {
                            //Formatação do valor do título
                            $valor_titulo = number_format($occurrences[count($occurrences) - 1]['VALOR_TITULO'], 2, ',', '.');
                            /*
                            * Acessa o banco de dados apenas a quantidade de vezes necessárias
                            * para buscar o nome do pagador, caso o mesmo possua mais de um título rejeitado
                            */
                            if ( $nosso_numero_ref != $key ) {
                                $nome_pagador = isset($this->customer->getCustomerByOurNumber($customer, $key)['customer'])
                                    ? $this->customer->getCustomerByOurNumber($customer, $key)['customer']
                                    : $this->customer->getCustomerByOurNumber($customer, $key);
                                $nosso_numero_ref = $key;
                            }
                            
                            $registro .= '<p style="width: 50px;border-top: 5px solid #2883A1;margin:30px 0;"></p>';
                            $registro .= "<div>";
                            $registro .= "<b>Nosso Número:</b> $key<br />";
                            $registro .= "<b>Pagador:</b> $nome_pagador<br />";
                            $registro .= "<b>Valor do título:</b> R$ $valor_titulo<br /><br />";
                            $registro .= "MOVIMENTAÇÕES: <br /><br />";
                            
                            foreach ( $occurrences as $occurrence_description ) {
                                $data_arquivo = Util::FmtDate($occurrence_description['DATA_ARQUIVO'], '20');
                                $cod_movimento = $occurrence_description['COD_MOVIMENTO'];
                                $tipo_registro = $occurrence_description['TIPO_REGISTRO'];
                                $motivo_rejeicao = $occurrence_description['MOTIVO_REJEICAO'];
                                $isRetorno = $tipo_registro === 'RETORNO';

                                $codMovimentoContext = $isRetorno ? $this->codMovimentoRetorno : $this->codMovimentoRemessa;
                                $legenda_movimentacao = $codMovimentoContext[$cod_movimento] ? $codMovimentoContext[$cod_movimento] : "OUTRAS MOVIMENTAÇÕES - Nº $cod_movimento";

                                if ( $cod_movimento === REJEITADO && !in_array($motivo_rejeicao, $reject_reasons)  ) {
                                    $reject_reasons[] = $motivo_rejeicao;
                                }

                                $motivo_rejeicao = $isRetorno && !in_array($motivo_rejeicao, $this->occurrencesNotFindables) ? " – $motivo_rejeicao" : "";
                                $cor_legenda = $this->getTextColor($cod_movimento);
                                $registro .= "<p style='font-family: open sans ms, courier new, verdana;'><b>$data_arquivo – <span style='color:$cor_legenda;'>".$legenda_movimentacao."</span>";
                                $registro .= $cod_movimento === REJEITADO ? $motivo_rejeicao : "";
                                $registro .= "</b></p>";
                            }
                            $registro .= "</div>";
                        }

                        //Observação sobre a baixa automática
                        $obs = "<p style='width:40%;border: 1px solid #069;border-radius: .2em;padding: 1em;margin: 0;background-color: #DFEDF2;color: #069;font-style: italic;'>Boletos vencidos há mais de 29 dias serão baixados do sistema bancário automaticamente.</p>";
                        //Lista dos procedimentos a serem realizados de acordo com cada rejeição
                        $instruction_data = $this->getInstructionByReason($reject_reasons);
                        $registro .= nl2br("
                            $obs
                            $instruction_data

                            Processado por {$this->processed_by}
                        ");

                        //Envia o email para o cliente
                        $customer_mail = $this->customer->getMail($customer);
                        $this->mail->send(array(
                            'mail' => $customer_mail !== '' ? $customer_mail : 'setydeias@setydeias.com.br',
                            'name' => $customer,
                            'subject' => $customer_mail !== '' ? "SETYDEIAS - $customer - Título(s) Rejeitado(s)" : "SETYDEIAS - $customer - EMAIL DO CLIENTE NÃO ENCONTRADO",
                            'body' => $registro
                        ));
                    }
                }

                //Formata os títulos que serão enviados ao suporte
                $has_mail_to_support = count($send_to_support) > 0;
                if ( $has_mail_to_support ) {
                    $nosso_numero_ref = "";

                    foreach ( $send_to_support as $customer => $nosso_numero ) {
                        $registro = "<!doctype><html>";
                        $registro .= "<head>";
                        $registro .= "<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet'>";
                        $registro .= "</head>";
                        $registro .= "<body style=\"font-family: 'Open Sans', Roboto, sans-serif;\">";
                        $registro .= "<img src='http://setydeias.com.br/comercial/images/mail-header.jpg' /><br/><br/>";
                        $registro .= "<h1 style='font-size: 150%;margin: 1.5em 0;'>Título(s) Rejeitado(s)</h1>";
                        $reject_reasons = array();

                        foreach ( $nosso_numero as $key => $occurrences ) {
                            //Formatação do valor do título
                            $valor_titulo = number_format($occurrences[count($occurrences) - 1]['VALOR_TITULO'], 2, ',', '.');
                            /*
                            * Acessa o banco de dados apenas a quantidade de vezes necessárias
                            * para buscar o nome do pagador, caso o mesmo possua mais de um título rejeitado
                            */
                            if ( $nosso_numero_ref != $key ) {
                                $nome_pagador = isset($this->customer->getCustomerByOurNumber($customer, $key)['customer'])
                                    ? $this->customer->getCustomerByOurNumber($customer, $key)['customer']
                                    : $this->customer->getCustomerByOurNumber($customer, $key);
                                $nosso_numero_ref = $key;
                            }
                            
                            $registro .= '<p style="width: 50px;border-top: 5px solid #2883A1;margin:30px 0;"></p>';
                            $registro .= "<div>";
                            $registro .= "<b>Nosso Número:</b> $key<br />";
                            $registro .= "<b>Pagador:</b> $nome_pagador<br />";
                            $registro .= "<b>Valor do título:</b> R$ $valor_titulo<br /><br />";
                            $registro .= "MOVIMENTAÇÕES: <br /><br />";
                            
                            foreach ( $occurrences as $occurrence_description ) {
                                $data_arquivo = Util::FmtDate($occurrence_description['DATA_ARQUIVO'], '20');
                                $cod_movimento = $occurrence_description['COD_MOVIMENTO'];
                                $tipo_registro = $occurrence_description['TIPO_REGISTRO'];
                                $motivo_rejeicao = $occurrence_description['MOTIVO_REJEICAO'];
                                $isRetorno = $tipo_registro === 'RETORNO';

                                $codMovimentoContext = $isRetorno ? $this->codMovimentoRetorno : $this->codMovimentoRemessa;
                                $legenda_movimentacao = $codMovimentoContext[$cod_movimento] ? $codMovimentoContext[$cod_movimento] : "OUTRAS MOVIMENTAÇÕES - Nº $cod_movimento";

                                if ( $cod_movimento === REJEITADO && !in_array($motivo_rejeicao, $reject_reasons)  ) {
                                    $reject_reasons[] = $motivo_rejeicao;
                                }

                                $motivo_rejeicao = $isRetorno && !in_array($motivo_rejeicao, $this->occurrencesNotFindables) ? " – $motivo_rejeicao" : "";
                                $cor_legenda = $this->getTextColor($cod_movimento);
                                $registro .= "<p style='font-family: open sans ms, courier new, verdana;'><b>$data_arquivo – <span style='color:$cor_legenda;'>".$legenda_movimentacao."</span>";
                                $registro .= $cod_movimento === REJEITADO ? $motivo_rejeicao : "";
                                $registro .= "</b></p>";
                            }
                            $registro .= "</div>";
                        }

                        //Observação sobre a baixa automática
                        $obs = "<p style='width:40%;border: 1px solid #069;border-radius: .2em;padding: 1em;margin: 0;background-color: #DFEDF2;color: #069;font-style: italic;'>Boletos vencidos há mais de 29 dias serão baixados do sistema bancário automaticamente.</p>";
                        //Lista dos procedimentos a serem realizados de acordo com cada rejeição
                        $instruction_data = $this->getInstructionByReason($reject_reasons);
                        $registro .= nl2br("
                            $obs
                            $instruction_data
                            
                            Processado por {$this->processed_by}
                        ");

                        //Envia o email para o cliente
                        $this->mail->send(array(
                            'mail' => 'setydeias@setydeias.com.br',
                            'name' => $customer,
                            'subject' => "SETYDEIAS - $customer - Título(s) Rejeitado(s)",
                            'body' => $registro
                        ));
                    }
                }
            }
        }

        /**
         * Retorna a lista de ocorrências
         */
        private function getOccurrencesList() {
            return $this->dao->getOccurrencesList();
        }

        /**
         * Retorna a cor da legenda de acordo com o Código de Movimentação
         */
        public function getTextColor($cod_movimento) {
            if ( $cod_movimento !== '03' ) {
                return "#1D7D14";
            }

            return "#EB2626";
        }
    }